<?php

namespace TS\FinancialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TS\FinancialBundle\Model\InvoicePdfModel;
use Symfony\Component\HttpFoundation\Response;


class InvoiceController extends Controller
{


    /**
     * Show invoice PDF
     */
    public function pdfAction($invoiceNr, Request $request)
    {
        $invoiceRepository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:Invoice');
        /* @var \TS\FinancialBundle\Entity\Invoice $invoice */
        $invoice = $invoiceRepository->findOneBy(array('invoiceNr' => $invoiceNr));
        if (!$invoice) {
            throw $this->createNotFoundException("Invoice ". $invoiceNr ." not found");
        }

        // check for view access
        if (false === $this->get('security.context')->isGranted("VIEW", $invoice)) {
            throw $this->createAccessDeniedException();
        }

        $invoicePdfModel = new InvoicePdfModel($this->container);
        $pdf = $invoicePdfModel->generatePdf($invoice);

        // Close and output PDF document
        $invoiceName = "Tournia-invoice-". $invoice->getInvoiceNr();
        $pdf->Output($invoiceName, 'I');
        return new Response();
    }

    /**
     * Show invoice PDF, based on token
     */
    public function pdfTokenAction($invoiceNr, $token, Request $request)
    {
        $invoiceRepository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:Invoice');
        /* @var \TS\FinancialBundle\Entity\Invoice $invoice */
        $invoice = $invoiceRepository->findOneBy(array('invoiceNr' => $invoiceNr));
        if (!$invoice) {
            throw $this->createNotFoundException("Invoice ". $invoiceNr ." not found");
        }

        // check token
        if ($invoice->getToken() != $token) {
            $this->get('session')->getFlashBag()->add('error', 'Incorrect token');
            throw $this->createAccessDeniedException("Incorrect token");
        }

        $invoicePdfModel = new InvoicePdfModel($this->container);
        $pdf = $invoicePdfModel->generatePdf($invoice);

        // Close and output PDF document
        $invoiceName = "Tournia-invoice-". $invoice->getInvoiceNr();
        $pdf->Output($invoiceName, 'I');
        return new Response();
    }

}
