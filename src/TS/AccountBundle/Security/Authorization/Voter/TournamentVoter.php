<?php
namespace TS\AccountBundle\Security\Authorization\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use TS\ApiBundle\Entity\Tournament;
use TS\ApiBundle\Entity\LoginAcount;
use TS\ApiBundle\Entity\Person;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\RegistrationGroup;
use TS\FinancialBundle\Entity\Invoice;

class TournamentVoter implements VoterInterface
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supportsAttribute($attribute)
    {
        // you won't check against a user attribute, so return true
        return true;
    }

    public function supportsClass($class)
    {
        // your voter supports all type of token classes, so return true
        return true;
    }

    function vote(TokenInterface $token, $object, array $attributes)
    {
        $res = VoterInterface::ACCESS_ABSTAIN;
        if (count($attributes) != 1) {
        	$this->log("Atributes not 1 string");
        	$res = VoterInterface::ACCESS_ABSTAIN;
        } else {
	        $attribute = strtoupper($attributes[0]);
	        if (is_object($object)) {
	        	$this->log("Requesting vote with object from ". get_class($object) ." and attribute ". $attribute);
	        	if ($object instanceof LoginAcount) {
	        		$res = $this->voteLoginAccount($object, $attribute);
	        	} else if ($object instanceof Person) {
	        		$res = $this->votePerson($object, $attribute);
	        	} else if ($object instanceof Tournament) {
	        		$res = $this->voteTournament($object, $attribute);
	        	} else if ($object instanceof Player) {
	        		$res = $this->votePlayer($object, $attribute);
	        	} else if ($object instanceof RegistrationGroup) {
	        		$res = $this->voteRegistrationGroup($object, $attribute);
	        	} else if ($object instanceof Invoice) {
	        		$res = $this->voteInvoice($object, $attribute);
	        	}
	        }
	    }
        
        if ($res == VoterInterface::ACCESS_GRANTED) {
        	$this->log("Access granted");
        } else if ($res == VoterInterface::ACCESS_DENIED) {
        	$this->log("Access denied");
        } else {
        	$this->log("Access abstain");
        }
        return $res;
    }
    
    /**
      * Do vote for LoginAcount object
      * VIEW = creator & admin
      * EDIT = creator & admin
      * CREATE = everyone
      * DELETE = creator & admin
      */
    private function voteLoginAccount($loginAccountObject, $attribute) {
    	$loggedInAccount = $this->container->get('security.context')->getToken()->getUser();
    	if ($attribute == "CREATE") {
    		$this->log("Create login account, access granted");
	        return VoterInterface::ACCESS_GRANTED;
	    } else {
	    	if (!is_object($loggedInAccount)) {
        		// user is not logged in
        		$this->log("User not logged in, access denied for login account object");
		        return VoterInterface::ACCESS_DENIED;
	    	} else if ($loggedInAccount->isAdmin() || $loggedInAccount->isEqualTo($loginAccountObject)) {
	    		$this->log("View, edit or delete login account, access granted");
	    		return VoterInterface::ACCESS_GRANTED;
	    	} else {
	    		$this->log("View, edit or delete login account, access denied");
	    		return VoterInterface::ACCESS_DENIED;
	    	}
	    }
    }

    /**
      * Do vote for Person object
      * VIEW = creator & admin
      * EDIT = creator & admin
      * CREATE = everyone
      * DELETE = creator & admin
      */
    private function votePerson($personObject, $attribute) {
    	$loggedInAccount = $this->container->get('security.context')->getToken()->getUser();
    	if ($attribute == "CREATE") {
    		$this->log("Create person, access granted");
	        return VoterInterface::ACCESS_GRANTED;
	    } else {
	    	if (!is_object($loggedInAccount)) {
        		// user is not logged in
        		$this->log("User not logged in, access denied for login account object");
		        return VoterInterface::ACCESS_DENIED;
	    	} else if ($loggedInAccount->isAdmin() || $loggedInAccount->getPerson()->isEqualTo($personObject)) {
	    		$this->log("View, edit or delete person, access granted");
	    		return VoterInterface::ACCESS_GRANTED;
	    	} else {
	    		$this->log("View, edit or delete person, access denied");
	    		return VoterInterface::ACCESS_DENIED;
	    	}
	    }
    }
    
    /**
      * Do vote for Tournament object
      * VIEW = everyone
      * EDIT = organizers & admin
      * CREATE = logged in
      * DELETE = organizers & admin
      */
    private function voteTournament($tournamentObject, $attribute) {
    	$loggedInAccount = $this->container->get('security.context')->getToken()->getUser();
    	if ($attribute == "VIEW") {
    		$this->log("View tournament, access granted");
	        return VoterInterface::ACCESS_GRANTED;
	    } else if (!is_object($loggedInAccount)) {
        	// user is not logged in
        	$this->log("User not logged in, access denied for tournament object");
		    return VoterInterface::ACCESS_DENIED;
		} else if ($attribute == "CREATE") {
			$this->log("Create tournament, access granted");
	        return VoterInterface::ACCESS_GRANTED;
	    } else {
	    	if ($loggedInAccount->isAdmin() || $tournamentObject->getOrganizerPersons()->contains($loggedInAccount->getPerson())) {
	    		$this->log("Edit or delete tournament, user in organizer list or admin, access granted");
	    		return VoterInterface::ACCESS_GRANTED;
	    	} else {
	    		$this->log("Edit or delete tournament, user not in organizer list nor admin, access denied");
	    		return VoterInterface::ACCESS_DENIED;
	    	}
	    }
    }
    
    /**
      * Do vote for Player object
      * VIEW = owner & contactPlayer & organizer & admin
      * EDIT = owner & contactPlayer & organizer & admin & for owner/contactPlayer:siteAuthorizations->changeRegistrationAllowed
      * CREATE = logged in
      * DELETE = owner & contactPlayer & organizer & admin & for owner/contactPlayer:siteAuthorizations->changeRegistrationAllowed
	  * @param \TS\ApiBundle\Entity\Player $playerObject
      */
    private function votePlayer($playerObject, $attribute) {
    	$loggedInAccount = $this->container->get('security.context')->getToken()->getUser();
    	if (!is_object($loggedInAccount)) {
        	// user is not logged in
        	$this->log("User not logged in, access denied for player object");
		    return VoterInterface::ACCESS_DENIED;
		} else if ($loggedInAccount->isAdmin()) {
			$this->log("User is admin, access granted for player object");
		    return VoterInterface::ACCESS_GRANTED;
		} else if ($attribute == "CREATE") {
			$this->log("Create player, access granted");
	        return VoterInterface::ACCESS_GRANTED;
	    } else {
	    	$isOwner = ($playerObject->getPerson() != null) && $playerObject->getPerson()->isEqualTo($loggedInAccount->getPerson());
	    	$isOrganizer = $playerObject->getTournament()->getOrganizerPersons()->contains($loggedInAccount->getPerson());

	    	$isContactPlayer = $this->container->get('doctrine.orm.entity_manager')
        		->getRepository('TSApiBundle:RegistrationGroup')
        		->isPersonRegistrationGroupContact($loggedInAccount->getPerson(), $playerObject->getRegistrationGroup());
	    	
	    	if ($attribute == "VIEW") {
	    		if ($isOwner || $isContactPlayer || $isOrganizer) {
		    		$this->log("View player, user is owner, contactPlayer or organizer, access granted");
		    		return VoterInterface::ACCESS_GRANTED;
		    	} else {
		    		$this->log("View player, user is NOT owner, contactPlayer or organizer, access denied");
		    		return VoterInterface::ACCESS_DENIED;
		    	}
		    } else if ($attribute == "EDIT") {
	    		$changeRegistrationAllowed = $playerObject->getTournament()->getAuthorization()->isChangeRegistrationAllowed();
				if (($isOwner || $isContactPlayer) && $changeRegistrationAllowed) {
		    		$this->log("Edit player, user is owner or contactPlayer and changeRegistrationAllowed, access granted");
		    		return VoterInterface::ACCESS_GRANTED;
		    	} else if ($isOrganizer) {
					$this->log("Edit player, user is organizer, access granted");
					return VoterInterface::ACCESS_GRANTED;
				} else {
		    		$this->log("Edit player, user is NOT owner, contactPlayer or organizer, access denied");
		    		return VoterInterface::ACCESS_DENIED;
		    	}
		    } else if ($attribute == "DELETE") {
				$changeRegistrationAllowed = $playerObject->getTournament()->getAuthorization()->isChangeRegistrationAllowed();
				if (($isOwner || $isContactPlayer) && $changeRegistrationAllowed) {
		    		$this->log("Delete player, user is owner, contactPlayer and changeRegistrationAllowed, access granted");
		    		return VoterInterface::ACCESS_GRANTED;
				} else if ($isOrganizer) {
					$this->log("Delete player, user is organizer, access granted");
					return VoterInterface::ACCESS_GRANTED;
				} else {
		    		$this->log("Delete player, user is NOT owner, contactPlayer or organizer, access denied");
		    		return VoterInterface::ACCESS_DENIED;
		    	}
		    }
	    }
    }
    
    /**
      * Do vote for RegistrationGroup object
      * VIEW = logged in
      * EDIT = contactPlayer & organizer & admin
      * CREATE = logged in
      * DELETE = organizer & admin
      */
    private function voteRegistrationGroup($registrationGroupObject, $attribute) {
    	$loggedInAccount = $this->container->get('security.context')->getToken()->getUser();
    	if (!is_object($loggedInAccount)) {
        	// user is not logged in
        	$this->log("User not logged in, access denied for team object");
		    return VoterInterface::ACCESS_DENIED;
		} else if ($loggedInAccount->isAdmin()) {
			$this->log("User is admin, access granted for team object");
		    return VoterInterface::ACCESS_GRANTED;
		} else if ($attribute == "VIEW") {
			$this->log("View team, access granted");
	        return VoterInterface::ACCESS_GRANTED;
	    } else if ($attribute == "CREATE") {
			$this->log("Create team, access granted");
	        return VoterInterface::ACCESS_GRANTED;
	    } else {
	    	$isOrganizer = $registrationGroupObject->getTournament()->getOrganizerPersons()->contains($loggedInAccount->getPerson());

	    	$isContactPlayer = $this->container->get('doctrine.orm.entity_manager')
        		->getRepository('TSApiBundle:RegistrationGroup')
        		->isPersonRegistrationGroupContact($loggedInAccount->getPerson(), $registrationGroupObject);
	    	
		    if ($attribute == "EDIT") {
	    		if ($isContactPlayer || $isOrganizer) {
		    		$this->log("Edit team, user is contactPlayer or organizer, access granted");
		    		return VoterInterface::ACCESS_GRANTED;
		    	} else {
		    		$this->log("Edit team, user is NOT contactPlayer or organizer, access denied");
		    		return VoterInterface::ACCESS_DENIED;
		    	}
		    } else if ($attribute == "DELETE") {
	    		if ($isOrganizer) {
		    		$this->log("Delete team, user is organizer, access granted");
		    		return VoterInterface::ACCESS_GRANTED;
		    	} else {
		    		$this->log("Delete team, user is NOT organizer, access denied");
		    		return VoterInterface::ACCESS_DENIED;
		    	}
		    }
	    }
    }

    /**
     * Vote for Invoice object
     * VIEW = execPerson of invoice/cart, organizer or admin
     * @param \TS\FinancialBundle\Entity\Invoice $invoiceObject
     */
    private function voteInvoice($invoiceObject, $attribute) {
        $loggedInAccount = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($loggedInAccount)) {
            // user is not logged in
            $this->log("User not logged in, access denied for invoice object");
            return VoterInterface::ACCESS_DENIED;
        } else if ($loggedInAccount->isAdmin()) {
            $this->log("User is admin, access granted for invoice object");
            return VoterInterface::ACCESS_GRANTED;
        } else if ($invoiceObject->getCartOrder()->getExecPerson() == $loggedInAccount->getPerson()) {
            $this->log("User is execPerson for invoice object, access granted");
            return VoterInterface::ACCESS_GRANTED;
        } else if (!is_null($invoiceObject->getPayOut()) && $invoiceObject->getPayOut()->getTournament()->getOrganizerPersons()->contains($loggedInAccount->getPerson())) {
            $this->log("User is organizer for tournament of invoice object, access granted");
            return VoterInterface::ACCESS_GRANTED;
        } else {
            $this->log("User is not execPerson or organizer of invoice object, access denied");
            return VoterInterface::ACCESS_DENIED;
        }
    }
    
    private function log($string) {
    	$logger = $this->container->get('logger');
    	$logger->info("TournamentVoter: ". $string);
    }
}