<?php
namespace TS\ControlBundle\PDF;

use \TCPDF;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomizedPdf extends TCPDF {

    /* @var \TS\ApiBundle\Entity\Tournament $tournament */
    private $tournament = "";
    private $container;
    private $isInvoice = false;
    private $invoiceNr = "";
    private $qrInFooter = false;
    private $qrUrl = null;

    /**
     * Set tournament, because name used in the template
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     */
    public function setTournament($tournament) {
        $this->tournament = $tournament;
    }

    /**
     * Set the container, to make translation possible
     * @param ContainerInterface $container
     */
    public function setContainer($container) {
        $this->container = $container;
    }

    /**
     * Set whether this PDF is an invoice, which changes the template
     * @param boolean $isInvoice
     */
    public function setIsInvoice($isInvoice) {
        $this->isInvoice = $isInvoice;
    }

    /**
     * Set invoice number, used in the template
     * @param #invoiceNr
     */
    public function setInvoiceNr($invoiceNr) {
        $this->invoiceNr = $invoiceNr;
    }

    /**
     * Set whether a QR code for Live should be rendered in footer
     * @param boolean $qrInFooter
     */
    public function setQrInFooter($qrInFooter) {
        $this->qrInFooter = $qrInFooter;
    }

    /**
     * Set the QR url
     * @param string $qrUrl
     */
    public function setQrUrl($qrUrl) {
        $this->qrUrl = $qrUrl;
    }

    //Page header
    /*public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'logo_example.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }*/


    // Page footer
    public function Footer() {
        /*
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        */
        // Position at 15 mm from bottom
        /*$this->SetY(550);

        // Logo
        $image_file = K_PATH_IMAGES.'logo_example.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, '<< TCPDF Example 013 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
*/
        if (!$this->isInvoice) {
            $this->createNormalFooter();
        } else {
            $this->createInvoiceFooter();
        }
    }


    private function createNormalFooter() {
        $this->SetTextColorArray($this->footer_text_color);
        //set style for cell border
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
        } else {
            $pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
        }
        $this->SetY($this->y-3);

        if ($this->qrInFooter) {
            // show Live QR code

            // set style for barcode
            $barcodeStyle = array(
                'border' => false,
                'vpadding' => 'auto',
                'hpadding' => 'auto',
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, //array(255,255,255)
                'module_width' => 1, // width of a single module in points
                'module_height' => 1 // height of a single module in points
            );

            $footerHtml = '
<table style="border-top:1px solid #ffffff">
    <tr style="border-top:1px solid #ffffff">
        <td align="left" width="10">&nbsp;</td>
        <td align="left" width="500">'. $this->trans('normalFooter.liveText') .':<br />'. $this->qrUrl .'</td>
        <td align="right" width="100">'. $pagenumtxt .'</td>
    </tr>
</table>
';
            $this->write2DBarcode($this->qrUrl, 'QRCODE,H', 10, 265, 30, 30, $barcodeStyle, 'M');
            $this->writeHTML($footerHtml, true, false, false, false, '');
        } else {
            $this->Cell(0, 8, $this->tournament->getName() ." - ". $this->trans('normalFooter.powered'), 'T', 0, 'L');

            //Print page number
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 8, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
        }
    }

    private function createInvoiceFooter() {
        $footerHtml = '
<p align="center" style="color:grey">
    <i>'. $this->trans('invoiceFooter.text1') .'</i><br />
    <i>'. $this->trans('invoiceFooter.text2') .'</i>
</p>
<p></p>
';
        $this->writeHTML($footerHtml, true, false, false, false, '');

        $this->SetTextColorArray($this->footer_text_color);
        //set style for cell border
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
        } else {
            $pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
        }
        $this->SetY($this->y-3);

        $this->Cell(0, 8, $this->trans('invoiceFooter.invoice') ." #". $this->invoiceNr, 'T', 0, 'L');

        //Print page number
        $this->SetX($this->original_lMargin);
        $this->Cell(0, 8, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
    }

    /**
     * Translate a string
     * @param String $str translatable string / variable
     * @param array $variables Will be passed to translatable string
     * @return String Translated string
     */
    private function trans($str, $variables = array()) {
        if (isset($this->container)) {
            return $this->container->get('translator')->trans($str, $variables, 'print');
        } else {
            return $str;
        }
    }
}
