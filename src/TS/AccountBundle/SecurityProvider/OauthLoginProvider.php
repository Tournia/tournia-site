<?php
namespace TS\AccountBundle\SecurityProvider;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use TS\ApiBundle\Entity\Person;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use FOS\UserBundle\Model\User;

class OauthLoginProvider extends BaseClass
{
    /**
     * @var session
     */
    protected $session;

    protected $container;

    public function __construct(UserManagerInterface $userManager, array $properties, $session, Container $container)
    {
        parent::__construct($userManager, $properties);
        $this->session = $session;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        //when the user is registrating
        if (null === $user) {
            // new user
            $service = $response->getResourceOwner()->getName();
            $user = $this->userManager->createUser();

            if ($service == "facebook") {
                $this->newFacebookUser($user, $response);
            } else if ($service == "google") {
                $this->newGoogleUser($user, $response);
            }

            $this->userManager->updateUser($user);
            $this->session->set('newSocialUser', true);
            return $user;
        }

        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);
        return $user;
    }

    private function newFacebookUser($loginAccount, UserResponseInterface $response, $person=null) {
        $loginAccount->setEmail($response->getUsername() .'@facebook');
        $loginAccount->setEmailCanonical($response->getUsername() .'@facebook');
        $loginAccount->setUsername($response->getUsername());

        $loginAccount->setFacebookId($response->getUsername());
        $loginAccount->addRole('ROLE_FACEBOOK');
        $loginAccount->setSocialUrl("http://www.facebook.com/". $response->getUsername());

        $loginAccount->setPassword(uniqid("ramdom", true));
        $loginAccount->setEnabled(true);
        $loginAccount->setMethod('facebook');

        $email = null;
        // getting email, but only set if email is not *@facebook.com
        if (($response->getEmail() != '') && (stripos($response->getEmail(), '@facebook') === false)) {
            $email = $response->getEmail();
        }

        if (is_null($person)) {
            // Create Person
            $person = $this->createPerson($email);
            $person->setName($response->getRealName());
        }

        $loginAccount->setPerson($person);
        $person->addLoginAccount($loginAccount);
    }

    private function newGoogleUser($loginAccount, UserResponseInterface $response, $person=null) {
        $loginAccount->setEmail($response->getUsername() .'@google');
        $loginAccount->setEmailCanonical($response->getUsername() .'@google');
        $loginAccount->setUsername($response->getUsername());

        $loginAccount->setGoogleId($response->getUsername());
        $loginAccount->addRole('ROLE_GOOGLE');
        $loginAccount->setSocialUrl("https://profiles.google.com/". $response->getUsername());

        $loginAccount->setPassword(uniqid("ramdom", true));
        $loginAccount->setEnabled(true);
        $loginAccount->setMethod('google');

        if (is_null($person)) {
            // Create Person
            $person = $this->createPerson($response->getEmail());
            $person->setName($response->getRealName());
        }

        $loginAccount->setPerson($person);
        $person->addLoginAccount($loginAccount);
    }


    /**
     * Link social login to user. Extended method because normal procedure would be to overwrite user, but instead a new LoginAccount should be created.
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }

        $property = $this->getProperty($response);

        if (!$this->accessor->isWritable($user, $property)) {
            throw new \RuntimeException(sprintf("Class '%s' must have defined setter method for property: '%s'.", get_class($user), $property));
        }

        $username = $response->getUsername();

        $loginAccount = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        if (!is_null($loginAccount)) {
            // existing loginAccount -> delete
            $em = $this->container->get('doctrine')->getManager();
            $em->remove($loginAccount);
            $em->flush();
        }

        // Lookup logged-in person
        $person = null;
        $loggedinUser = $this->container->get('security.context')->getToken()->getUser();
        if (is_object($loggedinUser) && !is_null($loggedinUser->getPerson())) {
            $person = $loggedinUser->getPerson();
        }

        // create new social LoginAccount, and connect LoginAccount to this user
        $service = $response->getResourceOwner()->getName();
        $loginAccount = $this->userManager->createUser();

        if ($service == "facebook") {
            $this->newFacebookUser($loginAccount, $response, $person);
        } else if ($service == "google") {
            $this->newGoogleUser($loginAccount, $response, $person);
        }
        $this->userManager->updateUser($loginAccount);

        return $loginAccount;
    }

    /**
     * Create a new Person
     * @param String $email The email address to lookup
     * @return \TS\ApiBundle\Entity\Person
     */
    private function createPerson($email) {
        $newPerson = new Person();
        $newPerson->setName($email);

        if (!is_null($email)) {
            // check if email is already in DB, if so, do not save email
            // (it would also be an option to lookup Person with $email, but that's a hacking risk)
            $person = $this->container->get('doctrine')
                ->getRepository('TSApiBundle:Person')
                ->findOneByEmail($email);
            if (!$person) {
                $newPerson->setEmail($email);
            }
        }

        return $newPerson;
    }
}
