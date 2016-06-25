<?php

namespace TS\AccountBundle\Model;

use TS\ApiBundle\Entity\AuthorizationInvite;
use TS\ApiBundle\Entity\LoginAccount;
use TS\ApiBundle\Entity\Person;

use TS\NotificationBundle\Event\PersonEvent;
use TS\NotificationBundle\NotificationEvents;

use Symfony\Component\DependencyInjection\ContainerInterface;


class AuthorizationModel
{
	
	private $container;
    private $em;
	
    
    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }


    /**
     * Create a new email LoginAccount for a Person
     * @param \TS\ApiBundle\Entity\Person $person The Person form which an Email LoginAccount is created
     * @return \TS\ApiBundle\Entity\LoginAccount
     */
    public function createEmailLoginAccountForPerson($person) {
        $email = $person->getEmail();
        if (empty($email)) {
            return null;
        }

        $loginAccount = $this->getOrCreateEmailLoginAccount($person);

        // inform Person of created account
        $event = new PersonEvent($person);
        $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PERSON_NEW, $event);
        $loginAccount->setPlainPassword(null);

        return $person;
    }

    /**
     * Authorize someone (based on an email address) for a Player.
     * If there is a Person with this email address, this Person is authorized. Otherwise a new Person is created.
     * @param \TS\ApiBundle\Entity\Player $player The Player for which $email will be authorized for
     * @param String $email
     * @return \TS\ApiBundle\Entity\Person
     */
    public function createAuthorizationPlayer($player, $email) {
        $person = $this->getOrCreatePerson($email);
        $person->addPlayer($player);
        $player->setPerson($person);
        $this->em->flush();

        // inform Person of changed rights
        $event = new PersonEvent($person);
        $event->setAddedAuthorization($player);
        $loginAccount = $person->getLoginAccounts()[0];
        if (!is_null($loginAccount->getPlainPassword())) {
            // new Person / LoginAccount
            // set name based on player
            $person->setName($player->getName(false));

            $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PERSON_NEW, $event);
            $loginAccount->setPlainPassword(null);
        } else {
            // authorized existing Person / LoginAccount
            $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PERSON_AUTHORIZED, $event);
        }

        return $person;
    }

    /**
     * Authorize someone (based on an email address) for a Tournament.
     * If there is a Person with this email address, this Person is authorized. Otherwise a new Person is created.
     * @param \TS\ApiBundle\Entity\Tournament $tournament The Tournament for which $email will be authorized for
     * @param String $email
     * @return \TS\ApiBundle\Entity\Person
     */
    public function createAuthorizationTournament($tournament, $email) {
        $person = $this->getOrCreatePerson($email);
        $person->addOrganizingTournament($tournament);
        $tournament->addOrganizerPerson($person);
        $this->em->flush();

        // inform Person of changed rights
        $event = new PersonEvent($person);
        $event->setAddedAuthorization($tournament);
        $loginAccount = $person->getLoginAccounts()[0];
        if (!is_null($loginAccount->getPlainPassword())) {
            // new Person / LoginAccount
            $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PERSON_NEW, $event);
            $loginAccount->setPlainPassword(null);
        } else {
            // authorized existing Person / LoginAccount
            $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PERSON_AUTHORIZED, $event);
        }

        return $person;
    }

    /**
     * Lookup or create a new Person, based on an email address
     * @param String $email The email address to lookup
     * @return \TS\ApiBundle\Entity\Person
     */
    private function getOrCreatePerson($email) {
        $person = $this->container->get('doctrine')
            ->getRepository('TSApiBundle:Person')
            ->findOneByEmail($email);
        if (!$person) {
            // Person not found -> create Person
            $person = new Person();
            $person->setName($email);
            $person->setEmail($email);

            // Create LoginAccount
            $loginAccount = $this->getOrCreateEmailLoginAccount($person);
        }

        return $person;
    }

    /**
     * Get or Create a new email LoginAccount
     * @param \TS\ApiBundle\Entity\Person $person Person should have email set
     * @return \TS\ApiBundle\Entity\LoginAccount
     */
    private function getOrCreateEmailLoginAccount($person) {
        /* @var \FOS\UserBundle\Doctrine\UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');
        // look for existing LoginAccount with email (unlikely, but possible...)
        $email = $person->getEmail();
        $loginAccount = $userManager->findUserByUsernameOrEmail($email);
        if (!$loginAccount) {
            // create new LoginAccount
            $userManager = $this->container->get('fos_user.user_manager');
            $loginAccount = $userManager->createUser();

            $generatedPassword = substr(str_shuffle("@#$%^&*()-_=+]}[{;?><,.123456789abcdefghklmnpqrstuvwxyzABCDEFGHKLMNPQRSTUVWXYZ@#$%^&*()-_=+]}[{;?><,."), 0, 8);
            $loginAccount->setPlainPassword($generatedPassword);
            $userManager->updatePassword($loginAccount);

            $loginAccount->setRegistrationName($email);
            $loginAccount->setMethod("email");
            $loginAccount->setUsername($email);
            $loginAccount->setEmail($email);
            $userManager->updateCanonicalFields($loginAccount);

            $this->em->persist($loginAccount);
        }
        $loginAccount->setPerson($person);
        $person->addLoginAccount($loginAccount);

        $loginAccount->setEnabled(true);
        $userManager->updateUser($loginAccount);

        if (isset($generatedPassword)) {
            $loginAccount->setPlainPassword($generatedPassword);
        }

        return $loginAccount;
    }
}