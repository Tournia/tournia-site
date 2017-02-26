<?php
namespace TS\SiteBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use TS\ApiBundle\Entity\DisciplinePlayer;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\RegistrationGroup;
use TS\ApiBundle\Entity\Payment;
use TS\ApiBundle\Entity\RegistrationFormValue;

use Symfony\Component\DependencyInjection\ContainerInterface;



class MailChanges
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container     = $container;
    }
    
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        $changeSet = $args->getEntityChangeSet();
        if ($this->container->get('kernel')->getEnvironment() == "test") {
            // workaround for unit testing. This will have problems accessing security.context container
            return;
        }
        if (!is_null($this->container->get('security.context')->getToken())) {
            $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
            if ($entity instanceof Player) {
            	// changes in Player entity
            	// save changes in session, so that RegistrationFormValue changes will be mailed as well in postUpdate()
            	$session = $this->container->get('session');
            	$session->set('playerChange.player', $entity->getId());
            	$changeSetSession = $session->get('playerChange.changeSet', array());
            	
            	foreach ($changeSet as $key=>$change) {
            		if ($key == "isContactPlayer") {
                        $this->hasUpdateContactPlayer($entity->getId(), $change[0], $change[1]);
                        continue;
                    }
                    $changeArray = array();
            		$changeArray['name'] = $key;
            		$changeArray['old'] = $change[0];
            		$changeArray['new'] = $change[1];
            		$changeSetSession[$key] = $changeArray;
            	}
            	$session->set('playerChange.changeSet', $changeSetSession);
            } else if ($entity instanceof RegistrationFormValue) {
            	// changes in RegistrationFormValue entity
            	// save changes in session, so that Player changes will be mailed as well in postUpdate()
            	$session = $this->container->get('session');
            	$session->set('playerChange.player', $entity->getPlayer()->getId());
            	$changeSetSession = $session->get('playerChange.changeSet', array());
            	
            	foreach ($changeSet as $key=>$change) {
            		if ($change[0] != $change[1]) {
            			// only mail actual changes in changeset
    	        		$changeArray = array();
    	        		$changeArray['name'] = $entity->getField()->getName();
    	        		if ($entity->getField()->getType() == 'checkbox') {
    	        			// replace booleans with text for email
    	        			$changeArray['old'] = $change[0] ? "Yes" : "No";
    	        			$changeArray['new'] = $change[1] ? "Yes" : "No";
    	        		} else {
    		        		$changeArray['old'] = $change[0];
    		        		$changeArray['new'] = $change[1];
    		        	}
    	        		$changeSetSession['formValue-'.$entity->getId()] = $changeArray;
    	        	}
            	}
            	$session->set('playerChange.changeSet', $changeSetSession);
            } else if ($entity instanceof DisciplinePlayer) {
                // changes in DisciplinePlayer entity
                // save changes in session, so that Player changes will be mailed as well in postUpdate()
                //$this->saveDisciplinePlayerChangeSet()
                // TODO: email changes of DisciplinePlayer
            } else if ($entity instanceof RegistrationGroup) {
            	// changes in RegistrationGroup entity
                // Moved to NotificationBundle
            }
        }
    }
    
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $uow = $entityManager->getUnitOfWork();
        if ($this->container->get('kernel')->getEnvironment() == "test") {
            // workaround for unit testing. This will have problems accessing security.context container
            return;
        }
        if (!is_null($this->container->get('security.context')->getToken())) {
            $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
            foreach ($uow->getScheduledEntityInsertions() AS $entity) {
    	    	if ($entity instanceof Player) {
    	        	// new Player entity
    	        	// Moved to NotificationBundle
    	    	}
    	    	
    	    	else if ($entity instanceof Payment) {
    	        	// new Payment entity
    	        	// Moved to NotificationBundle
    	    	}

                else if ($entity instanceof DisciplinePlayer) {
                    $this->saveDisciplinePlayerChangeSet(null, $entity);
                }
            }
            foreach ($uow->getScheduledEntityDeletions() AS $entity) {
                if ($entity instanceof Player) {
                    // deleted Player entity
                    // Moved to NotificationBundle
                }
            }
        }
    }
    
    private function mail($subject, $mailTo, $mailCC = null, $tournamentEntity = null, $templateName, $templateArray) {
    	if (!is_null($tournamentEntity)) {
    		$templateArray['tournament'] = $tournamentEntity;
    	}
    	
    	$message = \Swift_Message::newInstance()
    		->setSubject($subject)
    		->setContentType('text/plain')
    		->setBody($this->container->get('templating')->render($templateName, $templateArray));
    	
    	$message->setFrom($this->container->getParameter('email_from_email'));
    	if (!is_null($tournamentEntity)) {
    		$message->setReplyTo(array($tournamentEntity->getEmailFrom() => $tournamentEntity->getContactName()));
            if (($tournamentEntity->getOrganizationEmailOnChange() != null) && ($tournamentEntity->getOrganizationEmailOnChange() != '')) {
                $message->setBcc($tournamentEntity->getOrganizationEmailOnChange());
            }
    	}

        // prevent empty email names
        if (is_array($mailTo) && reset($mailTo) && empty(key($mailTo))) {
            $mailTo = null;
        }
        if (is_array($mailCC) && reset($mailCC) && empty(key($mailCC))) {
            $mailCC = null;
        }

        if (empty($mailTo) && empty($mailCC)) {
            // nothing to email
            return;
        }

        if (empty($mailTo)) {
            // move mailCC to mailTo
            $mailTo = $mailCC;
            $mailCC = null;
        }
    	
        $message->setTo($mailTo);
    	if (!empty($mailCC)) {
    		$message->setCc($mailCC);
    	}
        $this->container->get('mailer')->send($message);
	}

    /**
     * @param boolean $isNewRegistration Whether this is a new registration
     */
    public function emailAfterPlayerChanges($isNewRegistration) {
    	// mail changes made in registration form
    	// Moved to NotificationBundle
    }

    // save changes to contact player of RegistrationGroup, in order to be able to mail them all in once (with emailAfterContactPlayerChanges())
    private function hasUpdateContactPlayer($playerId, $oldValue, $newValue) {
        $sessionRow = array(
            'playerId' => $playerId,
            'oldValue' => $oldValue,
            'newValue' => $newValue
        );
        
        $session = $this->container->get('session');
        $sessionContactPlayers = $session->get('contactPlayerChange.rows', array());
        $sessionContactPlayers[$playerId] = $sessionRow;
        $session->set('contactPlayerChange.rows', $sessionContactPlayers);
    }

    // mail changes of contact players in RegistrationGroup
    public function emailAfterContactPlayerChanges() {
        $session = $this->container->get('session');
        if ($session->has('contactPlayerChange.rows')) { // prevent executing this code multiple times
            // mail old and new contact players in registration group of change in contact players
            $entity = $this->container->get('doctrine.orm.entity_manager');
            $changeSet = array();
            $tournamentEntity = null;

            foreach ($session->get('contactPlayerChange.rows') as $playerId=>$sessionRow) {
                $player = $entity
                    ->getRepository('TSApiBundle:Player')
                    ->find($playerId);
                $changeSet[] = array(
                    'name' => $player->getName(),
                    'old' => $sessionRow['oldValue'],
                    'new' => $sessionRow['newValue'],
                );
                
                $registrationGroup = $player->getRegistrationGroup();
                $tournamentEntity = $player->getTournament();
            }
            
            $contactPlayers = $entity
                        ->getRepository('TSApiBundle:RegistrationGroup')
                        ->getAllContactPlayers($registrationGroup);
            $mailTo = array();
            if (sizeof($contactPlayers) > 0) {
                foreach ($contactPlayers as $player) {
                    if (!is_null($player->getPerson())) {
                        $mailTo[$player->getPerson()->getEmail()] = $player->getPerson()->getName();
                    }
                }
            }

            $subject = "Changes in group for ". $tournamentEntity->getName();
            $templateName = "TSSiteBundle:Mails:contactPlayerChange.txt.twig";
            $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
            $changesMadeBy = (is_object($loggedInUser)) ? $loggedInUser->getPerson()->getName() .' ('. $loggedInUser->getPerson()->getEmail() .')' : 'Unknown';
            $templateArray = array(
                'registrationGroup' => $registrationGroup,
                'contactPlayers' => $contactPlayers,
                'changeSet' => $changeSet,
                'changesMadeBy' => $changesMadeBy,
            );
            
            $this->mail($subject, $mailTo, null, $tournamentEntity, $templateName, $templateArray);

            $session->remove('contactPlayerChange.rows');
        }

    }

    /**
     * Save DisciplinePlayer to changeSet so it can be emailed to player that this has changed
     * @param \TS\ApiBundle\Entity\DisciplinePlayer $oldDisciplinePlayer
     * @param \TS\ApiBundle\Entity\DisciplinePlayer $newDisciplinePlayer
     */
    private function saveDisciplinePlayerChangeSet($oldDisciplinePlayer, $newDisciplinePlayer) {
        $referenceDisciplinePlayer = null; /* @var \TS\ApiBundle\Entity\DisciplinePlayer $referenceDisciplinePlayer */
        $changeArray = array(
            "old" => null,
            "new" => null,
        );
        if (!is_null($oldDisciplinePlayer) && !is_null($oldDisciplinePlayer->getPlayer())) {
            $referenceDisciplinePlayer = $oldDisciplinePlayer;
            $changeArrayOld = array();
            $changeArrayOld['type'] = $oldDisciplinePlayer->getDiscipline()->getDisciplineType()->getName();
            $changeArrayOld['discipline'] = $oldDisciplinePlayer->getDiscipline()->getName();
            if ($oldDisciplinePlayer->getDiscipline()->getDisciplineType()->getPartnerRegistration()) {
                $changeArrayOld['partner'] = $oldDisciplinePlayer->getPartner();
            }
            $changeArray['old'] = $changeArrayOld;
        }
        if (!is_null($newDisciplinePlayer) && !is_null($newDisciplinePlayer->getPlayer())) {
            $referenceDisciplinePlayer = $newDisciplinePlayer;
            $changeArrayNew = array();
            $changeArrayNew['type'] = $newDisciplinePlayer->getDiscipline()->getDisciplineType()->getName();
            $changeArrayNew['discipline'] = $newDisciplinePlayer->getDiscipline()->getName();
            if ($newDisciplinePlayer->getDiscipline()->getDisciplineType()->getPartnerRegistration()) {
                $changeArrayNew['partner'] = $newDisciplinePlayer->getPartner();
            }
            $changeArray['new'] = $changeArrayNew;
        }

        // save to session, so it can be emailed later
        if (!is_null($referenceDisciplinePlayer)) {
            $session = $this->container->get('session');
            $session->set('playerChange.player', $referenceDisciplinePlayer->getPlayer()->getId());
            $changeSetSession = $session->get('playerChange.changeSet', array());
            if (!array_key_exists("disciplines", $changeSetSession)) {
                $changeSetSession['disciplines'] = array();
            }
            $disciplineTypeName = $referenceDisciplinePlayer->getDiscipline()->getDisciplineType()->getName();
            if (!array_key_exists($disciplineTypeName, $changeSetSession['disciplines'])) {
                $changeSetSession['disciplines'][$disciplineTypeName] = array();
            }
            $changeSetSession['disciplines'][$disciplineTypeName] = $changeArray;
            $session->set('playerChange.changeSet', $changeSetSession);
        }
    }
}