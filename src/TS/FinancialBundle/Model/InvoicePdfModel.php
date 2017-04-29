<?php

namespace TS\FinancialBundle\Model;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\ControlBundle\PDF\CustomizedPdf;

define ('INVOICE_HEADER_LOGO', 'bundles/tsfront/img/logo-bg-bw.png');
define ('INVOICE_HEADER_LOGO_WIDTH', 10);
define ('INVOICE_FONT_NAME_MAIN', 'helvetica');
define ('INVOICE_FONT_SIZE_MAIN', 10);
define ('INVOICE_FONT_NAME_DATA', 'helvetica');
define ('INVOICE_FONT_SIZE_DATA', 8);
define ('INVOICE_FONT_MONOSPACED', 'courier');
define ('INVOICE_MARGIN_LEFT', 15);
define ('INVOICE_MARGIN_TOP', 27);
define ('INVOICE_MARGIN_RIGHT', 15);
define ('INVOICE_MARGIN_BOTTOM', 25);
define ('INVOICE_MARGIN_HEADER', 5);
define ('INVOICE_MARGIN_FOOTER', 23);
/**
 * Ratio used to adjust the conversion of pixels to user units.
 */
define ('INVOICE_IMAGE_SCALE_RATIO', 1.25);

class InvoicePdfModel
{
    private $container;
    private $doctrine;

    /* @var boolean $isTournamentInvoice Whether this invoice is a tournament invoice */
    private $isTournamentInvoice;

    /* @var \TS\ApiBundle\Entity\Tournament $tournament */
    private $tournament;


    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');
    }

    /**
     * Generate invoice PDF
     * @requires Access checking has been done
     * @return \TS\ControlBundle\PDF\CustomizedPdf
     */
    public function generatePdf($invoice)
    {
        $this->isTournamentInvoice = !is_null($invoice->getPayOut());
        $this->tournament = ($this->isTournamentInvoice) ? $invoice->getPayOut()->getTournament() : $invoice->getCartOrder()->getItems()->get(0)->getProduct()->getTournament();

        if ($this->isTournamentInvoice) {
            // invoice is tournament invoice
            $debtorName = $invoice->getPayOut()->getTournament()->getName();
        } else {
            // normal invoice
            $debtorName = (!is_null($invoice->getCartOrder()->getExecPerson())) ? $invoice->getCartOrder()->getExecPerson()->getName() : $this->trans('invoice.anonymous');
        }

        $pdf = $this->setupPdf($debtorName);
        $pdf->setIsInvoice(true);
        $pdf->setInvoiceNr($invoice->getInvoiceNr());

        $this->generatePage1($pdf, $invoice, $debtorName);
        $pdf->AddPage();
        $this->generatePage2($pdf, $invoice);

        return $pdf;
    }

    /**
     * Setup PDF
     * @param String $debtorName Name of the debtor
     * @param int $fontSize The font size
     * @return CustomizedPdf
     */
    private function setupPdf($debtorName, $fontSize=10){
        /** @var CustomizedPdf $pdf */
        $pdf = $this->container->get("white_october.tcpdf")->create();

        $pdf->setContainer($this->container);
        $pdf->setPageOrientation("P");

        $pdf->SetCreator("Tournia.net");
        $pdf->SetAuthor("Tournia.net");
        $pdf->SetTitle($this->trans('invoice.invoice') ." - Tournia.net");
        $pdf->SetSubject($this->trans('invoice.invoice') ." - Tournia.net");
        $pdf->SetKeywords($this->trans('invoice.invoice') .", Tournia.net");

        // set default header data
        $pdf->SetHeaderData(INVOICE_HEADER_LOGO, INVOICE_HEADER_LOGO_WIDTH, $this->trans('invoice.invoice'), $debtorName, array(0,64,0), array(0,64,0));
        //$pdf->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(INVOICE_FONT_NAME_MAIN, '', INVOICE_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(INVOICE_FONT_NAME_DATA, '', INVOICE_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(INVOICE_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(INVOICE_MARGIN_LEFT, INVOICE_MARGIN_TOP, INVOICE_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(INVOICE_MARGIN_HEADER);
        $pdf->SetFooterMargin(INVOICE_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, INVOICE_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(INVOICE_IMAGE_SCALE_RATIO);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', $fontSize, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        //$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        return $pdf;
    }


    /**
     * Generate invoice PDF page 1
     * @param CustomizedPdf $pdf
     * @param \TS\FinancialBundle\Entity\Invoice $invoice
     * @param String $debtorName Name of the debtor
     */
    private function generatePage1(&$pdf, $invoice, $debtorName) {
        $tableHtml = '
<table style="font-size: 10px">
    <tr>
        <td>
            <table>
                <tr>
                    <td><b>'. $debtorName .'</b></td>
                </tr>';

        if (!$this->isTournamentInvoice) {
            // show email addres for normal invoice
            $email = (!is_null($invoice->getCartOrder()->getExecPerson())) ? $invoice->getCartOrder()->getExecPerson()->getEmail() : $this->trans('invoice.anonymous');
            $tableHtml .= '
                <tr>
                    <td><b>'. $email .'</b></td>
                </tr>';
        } else {
            $tableHtml .= '
                <tr>
                    <td><b>'. $this->tournament->getContactName() .'</b></td>
                </tr>
                <tr>
                    <td>'. $this->tournament->getEmailFrom() .'</td>
                </tr>
                    ';
            if (!is_null($this->tournament->getSite())) {
                // tournament invoice has site -> show address
                $tableHtml .= '
                <tr>
                    <td>'. $this->tournament->getSite()->getLocationAddress() .'</td>
                </tr>
                        ';
            }
        }

        $invoiceDate = $this->isTournamentInvoice ? $invoice->getPayOut()->getDateTime() : $invoice->getCartOrder()->getUpdatedAt();

        $tableHtml .= '
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td>'. $this->trans('invoice.invoiceNumber') .'</td>
                    <td>'. $invoice->getInvoiceNr() .'</td>
                </tr>
                <tr>
                    <td>'. $this->trans('invoice.invoiceDate') .'</td>
                    <td>'. date_format($invoiceDate, $this->trans('invoice.dateFormat')) .'</td>
                </tr>
            </table>
        </td>
        <td>
        </td>
    </tr>
</table>
<br />
<h1 align="center">'. $this->trans('invoice.invoice') .'</h1><br />
<table border="0" cellpadding="4" cellspacing="0" nobr="true">
    <tr style="background-color: lightgray">
        <td width="80">'. $this->trans('invoice.quantity') .'</td>
        <td width="420">'. $this->trans('invoice.product') .'</td>
        <td width="130" align="right">'. $this->trans('invoice.total') .'</td>
    </tr>';

        $adjustmentTotal = 0;
        foreach ($invoice->getCartOrder()->getAdjustments() as $adjustment) { /* @var \TS\FinancialBundle\Entity\PaymentAdjustment $adjustment */
            $tableHtml .= '
            <tr>
                <td>'. $adjustment->getQuantity() .'</td>
                <td>'. $adjustment->getLabel() .'</td>
                <td align="right">'. $this->tournament->getPaymentCurrency() .' '. number_format($adjustment->getAmount()/100, 2, ",", ".") .'</td>
            </tr>';
            $adjustmentTotal += $adjustment->getAmount();
        }

        $tableHtml .= '
    <tr>
        <td></td>
        <td><b>'. $this->trans('invoice.total') .'</b></td>
        <td align="right"><b>'. $this->tournament->getPaymentCurrency() .' '. number_format($adjustmentTotal/100, 2, ",", ".") .'</b></td>
    </tr>
    <tr>
        <td></td>
        <td>'. $this->trans('invoice.tournamentPaymentsNexPage') .'</td>
        <td align="right">'. $this->tournament->getPaymentCurrency() .' '. number_format(($invoice->getCartOrder()->getTotal()-$adjustmentTotal)/100, 2, ",", ".") .'</td>
    </tr>
    <tr>
        <td></td>
        <td><b>'. $this->trans('invoice.totalPayable') .'</b></td>
        <td align="right"><b>'. $this->tournament->getPaymentCurrency() .' '. number_format($invoice->getCartOrder()->getTotal()/100, 2, ",", ".") .'</b></td>
    </tr>
</table>
<br />
<p align="center">';
        if (!$this->isTournamentInvoice) {
            $tableHtml .= $this->trans('invoice.hasBeenPayed') .'</p>';
        } else {
            // tournament invoice
            if ($invoice->getCartOrder()->getTotal() > 0) {
                $amountTxt = $this->tournament->getPaymentCurrency() .' '. number_format($invoice->getCartOrder()->getTotal()/100, 2, ",", ".");
                $tableHtml .= '<b>'. $this->trans('invoice.pleaseTransfer', array('%amount%'=>$amountTxt)) .'</b></p>';
            } else {
                $amountTxt = $this->tournament->getPaymentCurrency() .' '. number_format(-$invoice->getCartOrder()->getTotal()/100, 2, ",", ".");
                $tableHtml .= $this->trans('invoice.hasBeenTransferred', array('%amount%'=>$amountTxt));

                if (!is_null($invoice->getPayOut()->getBankAccount())) {
                    $tableHtml .= $this->trans('invoice.hasBeenTransferred.toBankAccount', array('%account%'=>$invoice->getPayOut()->getBankAccount()));
                } else if (!is_null($invoice->getPayOut()->getPaypalAccount())) {
                    $tableHtml .= $this->trans('invoice.hasBeenTransferred.toPaypalAccount', array('%account%'=>$invoice->getPayOut()->getPaypalAccount()));
                } else {
                    $tableHtml .= $this->trans('invoice.hasBeenTransferred.toYou');
                }

                $tableHtml .= '</p>';
            }
        }

        $pdf->writeHTML($tableHtml, true, false, false, false, '');
    }

    /**
     * Generate invoice PDF page 2
     * @param CustomizedPdf $pdf
     * @param \TS\FinancialBundle\Entity\Invoice $invoice
     * @param String $debtorName Name of the debtor
     */
    private function generatePage2(&$pdf, $invoice) {
        $tableHtml = '
<h1>'. $this->trans('invoice.tournamentPayments') .'</h1>
<p>'. $this->trans('invoice.tournamentPayments.text') .' '. $invoice->getInvoiceNr() .'.</p>
<table border="0" cellpadding="4" cellspacing="0" nobr="true">
    <tr style="background-color: lightgray">
        <td width="80">'. $this->trans('invoice.quantity') .'</td>
        <td width="150">'. $this->trans('invoice.product') .'</td>
        <td width="150">'. $this->trans('invoice.player') .'</td>
        <td width="150">'. $this->trans('invoice.tournament') .'</td>
        <td width="100" align="right">'. $this->trans('invoice.total') .'</td>
    </tr>';

        $boughtProductTotal = 0;
        $boughtProductsArray = $this->isTournamentInvoice ? $invoice->getPayOut()->getBoughtProducts() : $invoice->getCartOrder()->getBoughtProducts();
        foreach ($boughtProductsArray as $boughtProduct) { /* @var \TS\FinancialBundle\Entity\BoughtProduct $boughtProduct */
            $tableHtml .= '
            <tr>
                <td>'. $boughtProduct->getQuantity() .'</td>
                <td>'. $boughtProduct->getName() .'</td>
                <td>'. $boughtProduct->getPlayer()->getName() .'</td>
                <td>'. $boughtProduct->getTournament()->getName() .'</td>
                <td align="right">'. $this->tournament->getPaymentCurrency() .' '. number_format($boughtProduct->getAmount()/100, 2, ",", ".") .'</td>
            </tr>';
            $boughtProductTotal += $boughtProduct->getAmount();
        }

        $tableHtml .= '
    <tr>
        <td></td>
        <td colspan="2"><b>'. $this->trans('invoice.totalTournamentPayments') .'</b></td>
        <td colspan="2" align="right"><b>'. $this->tournament->getPaymentCurrency() .' '. number_format($boughtProductTotal/100, 2, ",", ".") .'</b></td>
    </tr>
</table>';

        if (!$this->isTournamentInvoice) {
            $tableHtml .= '
            <br />
<p>'. $this->trans('invoice.disclaimer') .'<p>
';
        }

        $pdf->writeHTML($tableHtml, true, false, false, false, '');
    }

    /**
     * Translate a string
     * @param String $str translatable string / variable
     * @param array $variables Will be passed to translatable string
     * @return String Translated string
     */
    private function trans($str, $variables = array()) {
        return $this->container->get('translator')->trans($str, $variables, 'invoice');
    }
}
