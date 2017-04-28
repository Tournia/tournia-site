<?php

namespace TS\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TS\AdminBundle\Form\Type\DaterangeType;
use TS\AdminBundle\Form\Type\PayOutType;
use TS\FinancialBundle\Entity\PayOut;
use Sylius\Bundle\OrderBundle\Model\OrderInterface;
use TS\FinancialBundle\Entity\PaymentAdjustment;
use TS\FinancialBundle\Model\FinancialModel;
use TS\FinancialBundle\Entity\Invoice;
use TS\FinancialBundle\Model\InvoicePdfModel;
use Symfony\Component\HttpFoundation\Response;


class FinancialController extends MainController
{


    /**
     * Show outstanding amounts
     */
    public function outstandingAction(Request $request)
    {
        $this->checkAccess();
        $outstandingTournaments = $this->getAllOutstandingTournaments();
        return $this->render('TSAdminBundle:Financial:outstanding.html.twig', array(
            'outstandingTournaments' => $outstandingTournaments,
        ));
    }

    /**
     * Show pay-outs for administration
     */
    public function payoutsAction(Request $request)
    {
        $this->checkAccess();
        $payOuts = $this->getAllPayOuts();
        return $this->render('TSAdminBundle:Financial:payouts.html.twig', array(
            'payOuts' => $payOuts,
        ));
    }

    /**
     * Show incoming transactions for administration
     */
    public function incomingAction(Request $request)
    {
        $this->checkAccess();

        $form = $this->createForm(new DaterangeType());
        $form->handleRequest($request);
        $repository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:Cart');

        $startDateTime = $form->get('startDateTime')->getData()->format('Y-m-d');
        $endDateTime = $form->get('endDateTime')->getData()->format('Y-m-d');

        $query = $repository->createQueryBuilder('cart')
            ->andWhere('cart.updatedAt >= :startDateTime')
            ->andWhere('cart.updatedAt <= :endDateTime')
            ->andWhere('cart.state != :cartState')
            ->setParameter('cartState', OrderInterface::STATE_CART)
            ->leftJoin("cart.execPerson", "execPerson")
            ->leftJoin("cart.paymentInstruction", "paymentInstruction")
            ->orderBy("cart.updatedAt", "ASC")
            ->setParameter('startDateTime', $startDateTime)
            ->setParameter('endDateTime', $endDateTime . ' 23:59:59')
            ->getQuery();
        $incomingTransactions = $query->getResult();

        return $this->render('TSAdminBundle:Financial:incoming.html.twig', array(
            'form' => $form->createView(),
            'incomingTransactions' => $incomingTransactions,
        ));
    }

    /**
     * Show bought items for administration
     */
    public function boughtItemsAction(Request $request)
    {
        $this->checkAccess();

        $form = $this->createForm(new DaterangeType());
        $form->handleRequest($request);
        $repository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:Cart');

        $startDateTime = $form->get('startDateTime')->getData()->format('Y-m-d');
        $endDateTime = $form->get('endDateTime')->getData()->format('Y-m-d');

        $query = $repository->createQueryBuilder('cart')
            ->andWhere('cart.updatedAt >= :startDateTime')
            ->andWhere('cart.updatedAt <= :endDateTime')
            ->andWhere('cart.state != :cartState')
            ->setParameter('cartState', OrderInterface::STATE_CART)
            ->leftJoin("cart.execPerson", "execPerson")
            ->leftJoin("cart.paymentInstruction", "paymentInstruction")
            ->orderBy("cart.updatedAt", "ASC")
            ->setParameter('startDateTime', $startDateTime)
            ->setParameter('endDateTime', $endDateTime . ' 23:59:59')
            ->getQuery();

        $incomingTransactions = $query->getResult();

        return $this->render('TSAdminBundle:Financial:boughtItems.html.twig', array(
            'form' => $form->createView(),
            'incomingTransactions' => $incomingTransactions,
        ));
    }

    /**
     * Get all pay-outs
     * @return array
     */
    private function getAllPayOuts() {
        $repository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:PayOut');
        return $repository->findAll();
    }

    /**
     * Get all tournaments with outstanding amount
     * @return array
     */
    private function getAllOutstandingTournaments() {
        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament');
        $query = $repository->createQueryBuilder('t')
            ->select(array('SUM(boughtProducts.amount - boughtProducts.paidoutAmount) AS outstandingAmount', "t"))
            ->andWhere('boughtProducts.amount - boughtProducts.paidoutAmount <> 0')
            ->leftJoin("t.boughtProducts", "boughtProducts")
            ->groupBy("t.id")
            ->getQuery();
        return $query->getResult();
    }

    /**
     * Create a pay-out
     */
    public function createPayoutAction($tournamentId, Request $request)
    {
        $this->checkAccess();

        $tournamentRepository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament');
        $tournament = $tournamentRepository->findOneById($tournamentId); /* @var \TS\ApiBundle\Entity\Tournament $tournament */
        $outstandingBoughtProducts = $this->getAllOutstandingBoughtProducts($tournament);

        $payOut = new PayOut();
        $payOut->setTournament($tournament);
        $payOut->setDateTime(new \DateTime("now"));
        $payOut->setBankAccount($tournament->getFinancialPayoutBankAccount());
        $payOut->setPaypalAccount($tournament->getFinancialPayoutPaypalEmail());

        $financialModel = new FinancialModel($this->container);
        $form = $this->createForm(new PayOutType(), $payOut, array(
            "financialModel" => $financialModel,
        ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $payOutAmount = 0;

                $boughtProductRepository = $this->getDoctrine()
                    ->getRepository('TSFinancialBundle:BoughtProduct');

                // Create order
                $this->get('sylius.cart_provider')->abandonCart();
                $cart = $this->get('sylius.cart_provider')->getCart(); /* @var \TS\FinancialBundle\Entity\Cart $cart */
                $cart->setExecPerson($this->getUser()->getPerson());
                $currency = $tournament->getPaymentCurrency();
                $this->get('session')->set('currency', $currency);

                // Pay-out boughtProducts
                $boughtProducts = $this->get('request')->request->get('boughtProducts');
                foreach ($boughtProducts as $id) {
                    /* @var \TS\FinancialBundle\Entity\BoughtProduct $boughtProduct */
                    $boughtProduct = $boughtProductRepository->findOneById($id);
                    $payOutAmount += $boughtProduct->getOutstandingAmount();

                    $payOut->addBoughtProduct($boughtProduct);
                    $boughtProduct->setPayOut($payOut);
                    $boughtProduct->setPaidoutAmount($boughtProduct->getOutstandingAmount());
                }

                // Save adjustments payments
                foreach ($form->get('adjustments') as $adjustmentInfo) {
                    //var_dump($adjustmentInfo);
                    $adjustment = new PaymentAdjustment();
                    $adjustment->setOrder($cart);
                    $adjustment->setAmount($adjustmentInfo->get('amount')->getData());
                    $adjustment->setQuantity($adjustmentInfo->get('quantity')->getData());
                    $adjustment->setLabel($adjustmentInfo->get('label')->getData());
                    $cart->addAdjustment($adjustment);
                }

                $payOut->setPaidoutAmount($payOutAmount);

                $cart->calculateTotal();
                $cart->setTotal($cart->getTotal() - $payOutAmount);
                $cart->setState(OrderInterface::STATE_RETURNED);

                // Create invoice
                $invoice = new Invoice();
                $invoice->setCartOrder($cart);
                $cart->setInvoice($invoice);
                $invoice->setPayOut($payOut);
                $payOut->setInvoice($invoice);

                // saving the changes to the database
                $em = $this->getDoctrine()->getManager();
                $em->persist($payOut);
                $em->persist($invoice);
                $em->flush();

                $invoiceUrl = $this->generateUrl('financial_invoice', array("invoiceNr"=>$invoice->getInvoiceNr()));
                $flashMessage = 'Created PayOut invoice <a href="'. $invoiceUrl .'">#'. $invoice->getInvoiceNr() .'</a>. You can now transfer '. $currency .' '. number_format((-$cart->getTotal())/100, 2, ",", ".") .' to '. $tournament->getName();
                $this->get('session')->getFlashBag()->add('success', $flashMessage);
                return $this->redirect($this->generateUrl('admin_financial_outstanding'));
            }
        }

        return $this->render('TSAdminBundle:Financial:createPayout.html.twig', array(
            'form' => $form->createView(),
            'tournament' => $tournament,
            'outstandingBoughtProducts' => $outstandingBoughtProducts,
        ));
    }

    /**
     * Get all outstanding BoughtProducts for a tournament
     * @return array
     */
    private function getAllOutstandingBoughtProducts($tournament) {
        $repository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:BoughtProduct');
        $query = $repository->createQueryBuilder('bp')
            ->andWhere('bp.amount - bp.paidoutAmount <> 0')
            ->andWhere('bp.tournament = :tournament')
            ->setParameter("tournament", $tournament)
            ->getQuery();
        return $query->getResult();
    }

    /**
     * Show possible downloads for administration
     */
    public function downloadAction(Request $request)
    {
        $this->checkAccess();

        $form = $this->createForm(new DaterangeType());
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // download invoices
                set_time_limit(300);

                $repository = $this->getDoctrine()
                    ->getRepository('TSFinancialBundle:Invoice');

                $startDateTime = $form->get('startDateTime')->getData()->format('Y-m-d');
                $endDateTime = $form->get('endDateTime')->getData()->format('Y-m-d');

                // first look for player invoices
                $query = $repository->createQueryBuilder('invoice')
                    ->andWhere('cartOrder.updatedAt >= :startDateTime')
                    ->andWhere('cartOrder.updatedAt <= :endDateTime')
                    ->andWhere('invoice.payOut is NULL')
                    ->leftJoin("invoice.cartOrder", "cartOrder")
                    ->setParameter('startDateTime', $startDateTime)
                    ->setParameter('endDateTime', $endDateTime .' 23:59:59')
                    ->getQuery();
                $playerInvoices = $query->getResult();

                // lookup tournament invoices
                $query = $repository->createQueryBuilder('invoice')
                    ->andWhere('payOut.dateTime >= :startDateTime')
                    ->andWhere('payOut.dateTime <= :endDateTime')
                    ->andWhere('invoice.payOut is not NULL')
                    ->leftJoin("invoice.payOut", "payOut")
                    ->setParameter('startDateTime', $startDateTime)
                    ->setParameter('endDateTime', $endDateTime)
                    ->getQuery();
                $tournamentInvoices = $query->getResult();

                if ((sizeof($playerInvoices) == 0) && (sizeof($tournamentInvoices) == 0)) {
                    $this->get('session')->getFlashBag()->add('info', 'There are no invoices from '. $startDateTime .' to '. $endDateTime);
                } else {
                    $zipFileName =  "downloadInvoices-". $startDateTime ."-". $endDateTime .".zip";
                    return $this->generateInvoicesZip($zipFileName, $playerInvoices, $tournamentInvoices);
                }
            }
        }

        return $this->render('TSAdminBundle:Financial:download.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Generate zip with invoices
     * @param string $filename Filename of the generate zip
     * @param array $playerInvoices
     * @param array $tournamentInvoices
     * @return Response
     */
    private function generateInvoicesZip($filename, $playerInvoices, $tournamentInvoices) {
        $invoicePdfModel = new InvoicePdfModel($this->container);
        $tmpFile = tempnam(sys_get_temp_dir(), 'DownloadInvoices');
        $zip = new \ZipArchive();
        if ($zip->open($tmpFile, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE) === TRUE) {
            // add new invoice to zip
            foreach ($playerInvoices as $invoice) {
                // generate PDF
                $pdf = $invoicePdfModel->generatePdf($invoice);
                $invoiceFileName = "invoice-". $invoice->getInvoiceNr() .".pdf";
                $fileContents = $pdf->Output($invoiceFileName, 'S');

                // add PDF to zip
                $zip->addFromString($invoiceFileName, $fileContents);
            }
            foreach ($tournamentInvoices as $invoice) {
                // generate PDF
                $pdf = $invoicePdfModel->generatePdf($invoice);
                $invoiceFileName = "invoice-". $invoice->getInvoiceNr() .".pdf";
                $fileContents = $pdf->Output($invoiceFileName, 'S');

                // add PDF to zip
                $zip->addFromString($invoiceFileName, $fileContents);
            }
            if ($zip->close() === false) {
                echo "Bad zip close";
                var_dump($zip);
                echo $zip->getStatusString();
            }
        } else {
            echo "cannot open ". $tmpFile;
        }

        $response = new Response();

        //$response->setContent(readfile($tmpFile));
        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', filesize($tmpFile));
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Cache-Control', 'private');
        $response->setContent(file_get_contents($tmpFile));
        @unlink($tmpFile);
        return $response;
    }
}
