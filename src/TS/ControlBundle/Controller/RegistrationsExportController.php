<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use TS\ApiBundle\Entity\Tournament;

use TS\ApiBundle\Entity\Player;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;


class RegistrationsExportController extends MainController
{
    
    
    /**
     * Show an overview of possible export options
     */
    public function exportAction(Request $request) {
        $statusArray = $this->getStatusList($this->tournament);
        $form = $this->createFormBuilder()
	        ->add('status', 'choice', array(
	        	'choice_list' => new ChoiceList($statusArray, $statusArray),
	        	'multiple' => true,
	        	'expanded' => true,
	        	'mapped' => false,
	        	'data' => $statusArray,
	        ))
	        ->add('method', 'hidden')
	        ->getForm();
	        
	    if ($request->isMethod('POST')) {
	        $form->handleRequest($request);
	        if ($form->get('method')->getData() == "excel") {
	        	// download excel file
	        	return $this->downloadExcel($this->tournament, $form->get('status')->getData());
	        } 
	        else if ($form->get('method')->getData() == "isbt") {
	        	// download isbt organizer file
	        	return $this->downloadIsbtorganizer($this->tournament, $form->get('status')->getData());
	        }
	    }
        
        return $this->render('TSControlBundle:Registrations:export.html.twig', array(
        	'form' => $form->createView(),
        ));
    }
    
    private function getStatusList($tournament) {
    	$repository = $this->getDoctrine()->getRepository('TSApiBundle:Player');
		
		$query = $repository->createQueryBuilder('p')
		    ->andWhere('p.tournament = :tournament')
		    ->setParameter('tournament', $tournament)
		    ->groupBy('p.status')
		    ->getQuery();
		$statusRes = $query->getResult();
		
		$statusArray = array();
		foreach ($statusRes as $status) {
			$statusArray[] = $status->getStatus();
		}
		return $statusArray;
    }
    
    
    /**
     * Download an excel file of the tournament
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     */
    private function downloadExcel($tournament, $statusArray)
    {
        $players = $this->getDoctrine()
        	->getRepository('TSApiBundle:Player')
        	->findBy(array('tournament'=>$tournament, 'status'=>$statusArray));
		
		$playersArray = array();
		
		foreach ($players as $player) { /* @var \TS\ApiBundle\Entity\Player $player */
			$newPlayer = array();
			$newPlayer['firstName'] = $player->getFirstName();
			$newPlayer['lastName'] = $player->getLastName();
			$newPlayer['gender'] = $player->getGender() == "M" ? "Male" : "Female";
			foreach ($tournament->getDisciplineTypes()  as $disciplineType) {
				/* @var \TS\ApiBundle\Entity\DisciplineType $disciplineType */
				$newPlayer[$disciplineType->getName()] = '-';
				if ($disciplineType->getPartnerRegistration()) {
					$newPlayer['Partner '. $disciplineType->getName()] = '-';
				}
			}
			foreach($player->getDisciplinePlayers() as $disciplinePlayer) {
				/* @var \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayer */
				$discipline = $disciplinePlayer->getDiscipline();
				$disciplineType = $disciplinePlayer->getDiscipline()->getDisciplineType();
				$newPlayer[$disciplineType->getName()] = $discipline->getName();
				if ($disciplineType->getPartnerRegistration()) {
					$newPlayer['Partner '. $disciplineType->getName()] = $disciplinePlayer->getPartner();
				}
			}
			$newPlayer['registrationDate'] = $player->getRegistrationDate()->format("d-M-Y H:i:s");
			$newPlayer['status'] = $player->getStatus();
			if ($this->tournament->getRegistrationGroupEnabled()) {
				$newPlayer['group'] = (!is_null($player->getRegistrationGroup())) ? $player->getRegistrationGroup()->getName() : '';
				$newPlayer['isContactPerson'] = $player->getIsContactPlayer() ? "Yes" : "No";
			}
			$newPlayer['paymentBalance'] = $player->getPaymentBalance();
			if ($player->getPerson() != null) {
				$newPlayer['user_email'] = $player->getPerson()->getEmail();
				$newPlayer['user_name'] = $player->getPerson()->getName();
			} else {
				$newPlayer['user_email'] = '';
				$newPlayer['user_name'] = '-';
			}
			foreach ($player->getTournament()->getRegistrationFormFields() as $formField) {
				// set registrationFormFields that maybe weren't filled in by user (because the fields were added later)
				// necessary because first row should contain all possible column names
				$newPlayer[$formField->getName()] = '';
			}
			foreach ($player->getRegistrationFormValues() as $formValue) {
				$value = $formValue->getValue();
				if ($formValue->getField()->getType() == 'checkbox') {
					$value = $formValue->getValue() == '1' ? "Yes" : "No";
				}
				$newPlayer[$formValue->getField()->getName()] = $value;
			}
			
			$playersArray[] = $newPlayer;
		}
		
		$response = new Response($this->exportCSV($playersArray));
		$response->headers->set('Content-Type', 'application/csv');
		$response->headers->set('Content-Disposition', 'inline; filename="registrations-'. date("Y-m-d H:i:s") .'.csv');
        return $response;
    }
    
    /**
     * Download an isbt organizer file of the tournament
     */
    private function downloadIsbtorganizer($tournament, $statusArray)
    {
        $players = $this->getDoctrine()
        	->getRepository('TSApiBundle:Player')
        	->findBy(array('tournament'=>$tournament, 'status'=>$statusArray));
        
        // files should be organized by team -> put players in teams
        $teams = array();
        foreach ($players as $player) {
        	$groupId = (!is_null($player->getRegistrationGroup())) ? $player->getRegistrationGroup()->getId() : 0;
        	$teams[$groupId][] = $player;
        }
        
        if (count($teams) == 0) {
			return new Response('Error: no players found (with this status)');
		}

        $zip = new \ZipArchive();
        $filename = "downloadTeams-". date("Y-m-d H:i:s") .".zip";
        if ($zip->open($filename, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE) === TRUE) {
            // add new file for all teams
			foreach ($teams as $team) {
				$fileContents = $this->zipFileContents($team);
				$groupName = (!is_null($team[0]->getRegistrationGroup())) ? $team[0]->getRegistrationGroup()->getName() : 'Empty';
				$zip->addFromString($groupName .".dat", $fileContents);
			}
            if ($zip->close() === false) {
				echo "Bad zip close";
				var_dump($zip);
				echo $zip->getStatusString();
			}
        } else {
            echo "cannot open ". $filename;
        }

        $response = new Response();

        //$response->setContent(readfile($filename));
        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'application/zip'); 
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', filesize($filename));     
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Cache-Control', 'private');
        $response->setContent(file_get_contents($filename));
        @unlink($filename);
        return $response;
    }

	
	/**
	 * Export an array of players to the CSV format
	 * @param array $players The players with as the key the column name
	 */
	private function exportCSV($players) {
		$res = "";
		
		for ($i = 0; $i < count($players); $i++) {
			// set column names by using the first player
			if ($i == 0) {
				$titles = array_keys($players[$i]);
				$res .= $this->exportCSVArray($titles);
			}
			
			$res .= $this->exportCSVArray($players[$i]);
		}
		
		if ($i == 0) {
			$res .= "There are no registrations!";
		}
		
		return $res;
	}

	/**
	 * Export one array to a line in the CSV format
	 */
	private function exportCSVArray($array) {
		$res = "";
		
		if (is_array($array)) {
			foreach ($array as $value) {
				$value = str_replace("\r\n", " ", $value); // remove enters from comments
				$res .= '"' . html_entity_decode($value, ENT_QUOTES) .'",';
			}
			$res .= "\r\n";
		}
		
		return $res;
	}
	
	/**
	 * Generate for a certain team string for zip file
	 */
	private function zipFileContents($playersInTeam) {
		// default contactPlayer values, will be set later if there is a contactPlayer in this registrationGroup. 
		// For this file only one contactPlayer possible, so values will be overwritten with multiple contactPlayers to last contactPlayer values
		$contactPlayer = array();
		$contactPlayer['teamName'] = '';
		$contactPlayer['teamCountry'] = '';
		$contactPlayer['firstName'] = '';
		$contactPlayer['lastName'] = '';
		$contactPlayer['email'] = '';
		
		$res = "";
		$i = 0;
		foreach ($playersInTeam as $player) {
			if ($player->getIsContactPlayer()) {
				// set contactPlayer values
				$contactPlayer['teamName'] = (!is_null($player->getRegistrationGroup())) ? $player->getRegistrationGroup()->getName() : 'Empty';
				$contactPlayer['teamCountry'] = (!is_null($player->getRegistrationGroup())) ? $player->getRegistrationGroup()->getCountry() : 'Unknown';
				$contactPlayer['firstName'] = $player->getFirstName();
				$contactPlayer['lastName'] = $player->getLastName();
				if ($player->getPerson() != null) {
					$contactPlayer['email'] = $player->getPerson()->getEmail();
				}
			}
			$j = $i + 1;
			$playerNr = ($j < 10) ? "0". $j : $j;
			$class = "";
			$firstDisc = "";
			$secondDisc = "";
			foreach ($player->getDisciplinePlayers() as $disciplinePlayer) {
				/* @var \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayer */
				if ($disciplinePlayer->getDiscipline()->getDisciplineType()->getName() == "Singles") {
					// singles
					if ($firstDisc == "") {
						$firstDisc = "S";
					} else {
						$secondDisc = "S";
					}
				} else if ($disciplinePlayer->getDiscipline()->getDisciplineType()->getName() == "Mixed") {
					// mixed
					if ($firstDisc == "") {
						$firstDisc = "M";
					} else {
						$secondDisc = "M";
					}
				} else {
					// doubles
					if ($firstDisc == "") {
						$firstDisc = "D";
					} else {
						$secondDisc = "D";
					}
				}
				if ($class == "") {
					$class = substr($disciplinePlayer->getDiscipline()->getName(), -1);
				}
			}
			
			$res .= "[Player". $playerNr ."]
Lastname=". $player->getLastName() ."
Firstname=". $player->getFirstName() ."
Sex=". $player->getGender() ."
Class=". $class ."
Disc1=". $firstDisc ."
Disc2=". $secondDisc ."
Vegetarian=
Buffet=b
TShirt=

";
			$i++;
		}
		
		// Adding contactPlayer info
		$res = "[Team]
Name=". $contactPlayer['teamName'] ."
Country=". $contactPlayer['teamCountry'] ."

[Contact]
Name=". $contactPlayer['firstName'] ." ". $contactPlayer['lastName'] ."
Address=
Phone=
Fax=
Mail=". $contactPlayer['email'] ."

[Note]
Note=

". $res;
		return $res;
	}
}