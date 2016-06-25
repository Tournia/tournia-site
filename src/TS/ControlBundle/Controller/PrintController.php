<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TS\ControlBundle\PDF\CustomizedPdf;
use Symfony\Component\HttpFoundation\Response;

use TS\ApiBundle\Model\RankingModel;

define ('HEADER_LOGO', 'bundles/tsfront/img/logo-bg-bw.png');
define ('HEADER_LOGO_WIDTH', 10);
define ('FONT_NAME_MAIN', 'helvetica');
define ('FONT_SIZE_MAIN', 10);
define ('FONT_NAME_DATA', 'helvetica');
define ('FONT_SIZE_DATA', 8);
define ('FONT_MONOSPACED', 'courier');
define ('MARGIN_LEFT', 15);
define ('MARGIN_TOP', 27);
define ('MARGIN_RIGHT', 15);
define ('MARGIN_BOTTOM_NORMAL', 25);
define ('MARGIN_BOTTOM_QR', 33);
define ('MARGIN_HEADER', 5);
define ('MARGIN_FOOTER', 10);
/**
 * Ratio used to adjust the conversion of pixels to user units.
 */
define ('IMAGE_SCALE_RATIO', 1.25);


class PrintController extends MainController
{

	/**
	  * Show list of print options
	  */
	public function indexAction(Request $request) {
        // showing print index page
        return $this->render('TSControlBundle:Print:print.html.twig');
	}
	
	private function getPool($poolId) {
		$pool = $this->getDoctrine()
			->getRepository('TSApiBundle:Pool')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $poolId));
        if (!$pool) {
            throw new NotFoundHttpException('No pool found for id '. $poolId);
        }
		return $pool;
	}
	
	/**
	  * Print court notes
	  */
	public function courtNotesAction(Request $request)
	{
        $matches = $this->getMatchesFromRequest($request);
        $trimPlayerName = $request->query->get('trimPlayerName', '') == 'true';
        $pdf = $this->setupPdf($this->trans('courtNotes.title'), 10, true);

        foreach ($matches as $match) {
            $this->printFittingOnPage($pdf, "courtNotesPrintMatch", array(&$pdf, &$match, $trimPlayerName));
        }

        // Close and output PDF document
        $pdf->Output($this->trans('courtNotes.fileName'), 'I');
        return new Response();
	}

    /**
     * Print one match for court notes
     * @param CustomizedPdf $pdf
     * @param \TS\ApiBundle\Entity\Match $match
     * @param boolean $trimPlayerName Whether to trim the name of a player
     * @return void. $pdf is changed in this function
     */
    private function courtNotesPrintMatch(&$pdf, $match, $trimPlayerName) {
        // Print match number, court and pool/round
        $tableHtml = '
<table style="border: 1px solid black;font-weight: bold;" cellpadding="4" cellspacing="0" nobr="true">
    <tr>
        <td width="160">'. $this->trans('print.match') .': '. $match->getLocalId() .'</td>
        <td width="80">'. $this->trans('print.court') .': </td>
        <td width="400" align="right">'. $this->trans('print.pool') .': '. $match->getPool()->getName() .' - '. $match->getRound() .'</td>
    </tr>
</table>

<table border="1" cellpadding="4" cellspacing="0">
    <tr>
        <td width="90">&nbsp;</td>
        <td width="250">'. $this->formatTeamName($match->getTeam1(), $trimPlayerName) .'</td>
        <td width="50" align="center">'. $this->trans('print.vs') .'</td>
        <td width="250">'. $this->formatTeamName($match->getTeam2(), $trimPlayerName) .'</td>
    </tr>';
        for ($setNr = 1; $setNr <= $this->tournament->getNrSets(); $setNr++) {
            $tableHtml .= '
            <tr>
                <td>'. $this->trans('print.set') .' '. $setNr .'</td>
                <td>&nbsp;</td>
                <td align="center">-</td>
                <td>&nbsp;</td>
            </tr>
        ';
        }
        $tableHtml .= '
</table>
';

        $pdf->writeHTML($tableHtml, true, false, false, false, '');
    }


    /**
     * Print matches list
     */
    public function matchesListAction(Request $request)
    {
        $matches = $this->getMatchesFromRequest($request);
        $pdf = $this->setupPdf($this->trans('matchesList.title'));

        $this->matchesListPrintTop($pdf);
        foreach ($matches as $match) {
            $this->printFittingOnPage($pdf, "matchesListPrintRow", array(&$pdf, &$match), "matchesListPrintTop");
        }

        // Close and output PDF document
        $pdf->Output($this->trans('matchesList.fileName'), 'I');
        return new Response();
    }

    private function matchesListPrintTop($pdf) {
        $topHtml = '<h1>Matches list</h1><br />';
        $pdf->writeHTML($topHtml, false);
    }

    /**
     * Print one row for matches list
     * @param CustomizedPdf $pdf
     * @param \TS\ApiBundle\Entity\Match $match
     * @return void. $pdf is changed in this function
     */
    private function matchesListPrintRow(&$pdf, $match) {
        $tableHtml = '
<table border="1" cellpadding="4" cellspacing="0">
    <tr>
        <td width="50">'. $match->getLocalId() .'</td>
        <td width="140">'. $match->getPool()->getName() .'<br />'. $match->getRound() .'</td>
        <td width="200">'. $this->formatTeamName($match->getTeam1(), false) .'</td>
        <td width="50" align="center">'. $this->trans('print.vs') .'</td>
        <td width="200">'. $this->formatTeamName($match->getTeam2(), false) .'</td>
    </tr>
</table>
';
        $pdf->writeHTML($tableHtml, false, false, false, false, '');
    }


    /**
     * Print match results
     */
    public function matchResultsAction(Request $request)
    {
        $matches = $this->getMatchesFromRequest($request);
        $pdf = $this->setupPdf($this->trans('matchResults.title'));

        $this->matchResultsPrintTop($pdf);
        foreach ($matches as $match) {
            $this->printFittingOnPage($pdf, "matchResultsPrintRow", array(&$pdf, &$match), "matchResultsPrintTop");
        }

        // Close and output PDF document
        $pdf->Output($this->trans('matchResults.fileName'), 'I');
        return new Response();
    }

    private function matchResultsPrintTop($pdf) {
        $topHtml = '<h1>'. $this->trans('matchResults.title') .'</h1><br />';
        $pdf->writeHTML($topHtml, false);
    }

    /**
     * Print one row for match results
     * @param CustomizedPdf $pdf
     * @param \TS\ApiBundle\Entity\Match $match
     * @return void. $pdf is changed in this function
     */
    private function matchResultsPrintRow(&$pdf, $match) {
        $tableHtml = '
<table border="1" cellpadding="4" cellspacing="0">
    <tr>
        <td width="50">'. $match->getLocalId() .'</td>
        <td width="200">'. $this->formatTeamName($match->getTeam1(), false) .'</td>
        <td width="50" align="center">'. $this->trans('print.vs') .'</td>
        <td width="200">'. $this->formatTeamName($match->getTeam2(), false) .'</td>
        <td width="140">'. $match->getScoreTextual() .'</td>
    </tr>
</table>
';
        $pdf->writeHTML($tableHtml, false, false, false, false, '');
    }


    /**
     * Print ranking
     */
    public function rankingAction(Request $request) {
        $poolId = $request->query->get('poolId', '');

        $rankingModel = new RankingModel($this->getDoctrine(), $this->tournament);
        if ($poolId == '') {
            // all pools
            $poolArray = $this->tournament->getPools();
        } else {
            $poolArray = array($this->getPool($poolId));
        }

        $pdf = $this->setupPdf($this->trans('ranking.title'), 8);
        $rankingPools = array();
        foreach ($poolArray as $pool) {
            // new pool -> create new page
            $this->rankingPrintTop($pdf, $pool);

            $ranking = $rankingModel->getPoolRankingData($pool);
            foreach ($ranking as $teamId=>&$teamData) {
                $teamData['matchesRelative'] = sprintf("%1\$.3f", $teamData['matchesRelative']);
                $teamData['setsRelative'] = sprintf("%1\$.3f", $teamData['setsRelative']);
                $teamData['pointsRelative'] = sprintf("%1\$.3f", $teamData['pointsRelative']);

                $this->printFittingOnPage($pdf, "rankingPrintRow", array(&$pdf, &$teamData), "rankingPrintTop", array(&$pool));
            }
            $pdf->AddPage();
        }
        $pdf->deletePage($pdf->getNumPages()); // delete last page which is empty

        // Close and output PDF document
        $pdf->Output($this->trans('ranking.fileName'), 'I');
        return new Response();
    }

    private function rankingPrintTop($pdf, $pool) {
        $topHtml = '
<h4>Ranking '. $pool->getName() .'</h4>
<table border="1" cellpadding="2" cellspacing="0">
    <tr>
        <td rowspan="2" width="35">'. $this->trans('print.rank') .'</td>
        <td rowspan="2" width="165">'. $this->trans('print.players') .'</td>
        <td colspan="5" width="210" align="center">'. $this->trans('print.matches') .'</td>
        <td colspan="3" width="125" align="center">'. $this->trans('print.sets') .'</td>
        <td colspan="3" width="125" align="center">'. $this->trans('print.points') .'</td>
    </tr>
    <tr>
        <td width="45">'. $this->trans('print.played') .'</td>
        <td width="40">'. $this->trans('print.won') .'</td>
        <td width="40">'. $this->trans('print.draw') .'</td>
        <td width="40">'. $this->trans('print.lost') .'</td>
        <td width="45">'. $this->trans('print.relative') .'</td>
        <td width="40">'. $this->trans('print.won') .'</td>
        <td width="40">'. $this->trans('print.lost') .'</td>
        <td width="45">'. $this->trans('print.relative') .'</td>
        <td width="40">'. $this->trans('print.won') .'</td>
        <td width="40">'. $this->trans('print.lost') .'</td>
        <td width="45">'. $this->trans('print.relative') .'</td>
    </tr>
</table>';
        $pdf->writeHTML($topHtml, false);
    }

    /**
     * Print one row for ranking
     * @param CustomizedPdf $pdf
     * @param array $teamData
     * @return void. $pdf is changed in this function
     */
    private function rankingPrintRow(&$pdf, $teamData) {
        $tableHtml = '
<table border="1" cellpadding="4" cellspacing="0">
    <tr>
        <td width="35">'. $teamData['rank'] .'</td>
        <td width="165">';
        $nrPlayers = count($teamData['players']);
        $i = 0;
        foreach ($teamData['players'] as $playerName) {
            $tableHtml .= $playerName;
            $i++;
            if ($i < $nrPlayers) {
                $tableHtml .= "<br />";
            }
        }
        $tableHtml .='</td>
        <td width="45">'. $teamData['matchesPlayed'] .'</td>
        <td width="40">'. $teamData['matchesWon'] .'</td>
        <td width="40">'. $teamData['matchesDraw'] .'</td>
        <td width="40">'. $teamData['matchesLost'] .'</td>
        <td width="45">'. $teamData['matchesRelative'] .'</td>
        <td width="40">'. $teamData['setsWon'] .'</td>
        <td width="40">'. $teamData['setsLost'] .'</td>
        <td width="45">'. $teamData['setsRelative'] .'</td>
        <td width="40">'. $teamData['pointsWon'] .'</td>
        <td width="40">'. $teamData['pointsLost'] .'</td>
        <td width="45">'. $teamData['pointsRelative'] .'</td>
    </tr>
</table>
';
        $pdf->writeHTML($tableHtml, false);
    }


    /**
     * Print teams
     */
    public function teamsAction(Request $request) {
        $poolId = $request->query->get('poolId', '');

        if ($poolId == '') {
            // all pools
            $poolArray = $this->tournament->getPools();
        } else {
            $poolArray = array($this->getPool($poolId));
        }

        $pdf = $this->setupPdf($this->trans('teams.title'));
        foreach ($poolArray as $pool) {
            // new pool -> create new page
            $this->teamsPrintTop($pdf, $pool);
            foreach ($pool->getTeams() as $team) {
                $this->printFittingOnPage($pdf, "teamsPrintRow", array(&$pdf, &$team), "teamsPrintTop", array(&$pool));
            }
            $pdf->AddPage();
        }
        $pdf->deletePage($pdf->getNumPages()); // delete last page which is empty

        // Close and output PDF document
        $pdf->Output($this->trans('teams.fileName'), 'I');
        return new Response();
    }

    private function teamsPrintTop($pdf, $pool) {
        $topHtml = '
<h4>'. $this->trans('teams.title') .' - '. $pool->getName() .'</h4>
<table border="1" cellpadding="4" cellspacing="0">
    <tr>';
        for ($i = 1; $i <= $pool->getNrPlayersInTeam(); $i++) {
            $width = round(640 / $pool->getNrPlayersInTeam());
            $topHtml .= '<td width="'. $width .'">'. $this->trans('print.player') .' '. $i .'</td>';
        }
        $topHtml .= '
    </tr>
</table>';
        $pdf->writeHTML($topHtml, false);
    }

    /**
     * Print one row for teams
     * @param CustomizedPdf $pdf
     * @param \TS\ApiBundle\Entity\Team $team
     * @return void. $pdf is changed in this function
     */
    private function teamsPrintRow(&$pdf, $team) {
        $tableHtml = '
<table border="1" cellpadding="4" cellspacing="0">
    <tr>';
        for ($i = 0; $i < $team->getPool()->getNrPlayersInTeam(); $i++) {
            $width = round(640 / $team->getPool()->getNrPlayersInTeam());
            $tableHtml .= '<td width="'. $width .'">';
            if (!is_null($team->getPlayerForPosition($i, true))) {
                $tableHtml .= $team->getPlayerForPosition($i, true)->getName();
                if ($team->hasReplacementPlayerForPosition($i)) {
                    $tableHtml .= '<br />'. $this->trans('print.replacement') .': '. $team->getPlayerForPosition($i, false)->getName();
                }
            } else {
                $tableHtml .= '-';
            }
            $tableHtml .= '</td>';
        }
        $tableHtml .= '
    </tr>
</table>';
        $pdf->writeHTML($tableHtml, false);
    }


    /**
     * Print something on PDF if it fits on page. If it doesn't fit, the transaction is rolled back, and it's printed on a new page
     * @param CustomizedPdf $pdf
     * @param string $methodName The name of a method in this class, which is called for printing
     * @param array $methodArgs Arguments passed to the method
     * @param string $methodNameTop The name of a method in this class, which is called for printing the first top (e.g. header) of a page
     * @param array $methodArgsTop Arguments passed to the method for printing the first top (e.g. header) of a page. By default is $pdf already added
     */
    private function printFittingOnPage(&$pdf, $methodName, $methodArgs, $methodNameTop=null, $methodArgsTop=array()) {
        $numPages = $pdf->getNumPages();
        $pdf->startTransaction();
        call_user_func_array(array($this, $methodName), $methodArgs);

        if ($numPages == $pdf->getNumPages()) {
            $pdf->commitTransaction();
        } else {
            // part of match was printed on new page, undo adding the row.
            $pdf->rollbackTransaction(true);
            if ($numPages == $pdf->getNumPages()) {
                $pdf->AddPage();
            }
            if ($methodNameTop != null) {
                array_unshift($methodArgsTop, $pdf);
                call_user_func_array(array($this, $methodNameTop), $methodArgsTop);
            }
            call_user_func_array(array($this, $methodName), $methodArgs);
        }
    }

    /**
     * Return the team name, properly formatted
     */
    private function formatTeamName($team, $trimPlayerName) {
        $res = '';
        if (!is_null($team)) {
            $nrPlayers = count($team->getPlayersForAllPositions());
            $i = 0;
            foreach ($team->getPlayersForAllPositions() as $player) {
                if ($trimPlayerName) {
                    $res .= mb_strimwidth($player->getName(), 0, 30, "...", "UTF-8");
                } else {
                    $res .= $player->getName();
                }
                $i++;
                if ($i < $nrPlayers) {
                    $res .= "<br />";
                }
            }
        } else {
            $res .= "-";
        }
        return $res;
    }

    private function getMatchesFromRequest(Request $request) {
        $poolId = $request->query->get('poolId', '');
        $round = $request->query->get('round', '');
        $fromMatchNumber = $request->query->get('fromMatchNumber', '');
        $toMatchNumber = $request->query->get('toMatchNumber', '');
        $searchBy = $request->query->get('searchBy', '');

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Match');
        $query = $repository->createQueryBuilder('m')
            ->andWhere('m.tournament = :tournament')
            ->setParameter('tournament', $this->tournament);

        if ($searchBy == 'poolRound') {
            // search by poolId (and possibly round)
            if ($poolId != '') {
                $query = $query
                    ->leftJoin('m.pool', 'pool')
                    ->andWhere('pool.id = :poolId')
                    ->setParameter('poolId', $poolId);
            }
            if ($round != '') {
                $query = $query
                    ->andWhere('m.round = :round')
                    ->setParameter('round', $round);
            }
        } else {
            // search by match number
            if ($fromMatchNumber != '') {
                $query = $query
                    ->andWhere('m.localId >= :fromMatchNumber')
                    ->setParameter('fromMatchNumber', $fromMatchNumber);
            }
            if ($toMatchNumber != '') {
                $query = $query
                    ->andWhere('m.localId <= :toMatchNumber')
                    ->setParameter('toMatchNumber', $toMatchNumber);
            }
        }

        $query = $query
            ->orderBy('m.localId', 'ASC')
            ->getQuery();
        $matches = $query->getResult();

        return $matches;
    }

    /**
     * Setup PDF
     * @param String $title The title which will be displayed on the PDF
     * @param int $fontSize The font size
     * @param boolean $disableQrInFooter Whether to disable to QR-code in the footer
     * @return CustomizedPdf
     */
    private function setupPdf($title, $fontSize=10, $disableQrInFooter=false){
        /** @var CustomizedPdf $pdf */
        $pdf = $this->get("white_october.tcpdf")->create();

        $pdf->setTournament($this->tournament);
        $pdf->setContainer($this->container);

        // show QR-code in footer if Live is open
        $qrCodeInFooter = !$disableQrInFooter && $this->tournament->getAuthorization()->isApiAllowed();
        $pdf->setQrInFooter($qrCodeInFooter);
        if ($qrCodeInFooter) {
            $url = $this->get('router')->generate('live_index', array('tournamentUrl' => $this->tournament->getUrl()), true);
            $pdf->setQrUrl($url);
        }
        //$pdf->set

        $pdf->setPageOrientation("P");

        $pdf->SetCreator("Tournia.net");
        $pdf->SetAuthor("Tournia.net");
        $pdf->SetTitle($title ." - ". $this->tournament->getName());
        $pdf->SetSubject($title ." - ". $this->tournament->getName());
        $pdf->SetKeywords($title .", ". $this->tournament->getName());

        // set default header data
        $pdf->SetHeaderData(HEADER_LOGO, HEADER_LOGO_WIDTH, $title, $this->tournament->getName(), array(0,64,0), array(0,64,0));
        //$pdf->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(FONT_NAME_MAIN, '', FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(FONT_NAME_DATA, '', FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(MARGIN_LEFT, MARGIN_TOP, MARGIN_RIGHT);
        $pdf->SetHeaderMargin(MARGIN_HEADER);
        $pdf->SetFooterMargin(MARGIN_FOOTER);

        // set auto page breaks
        if ($qrCodeInFooter) {
            $pdf->SetAutoPageBreak(TRUE, MARGIN_BOTTOM_QR);
        } else {
            $pdf->SetAutoPageBreak(TRUE, MARGIN_BOTTOM_NORMAL);
        }

        // set image scale factor
        $pdf->setImageScale(IMAGE_SCALE_RATIO);

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
     * Translate a string
     * @param String $str translatable string / variable
     * @param array $variables Will be passed to translatable string
     * @return String Translated string
     */
    private function trans($str, $variables = array()) {
        return $this->container->get('translator')->trans($str, $variables, 'print');
    }
}