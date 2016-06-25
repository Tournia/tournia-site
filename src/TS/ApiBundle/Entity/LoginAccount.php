<?php
namespace TS\ApiBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table()
 * @UniqueEntity("username", message = "This username is already in use.")
 */
class LoginAccount extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="loginAccounts", cascade={"persist"}, fetch="EAGER")
     */
    private $person;

    /**
     * Name. Only used for email registration. For the actual name, check person->getName()
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $registrationName;

    /**
     * Login method. Can be email, facebook, twitter or google
     * @var string
     *
     * @ORM\Column(type="string", length=16, nullable=false)
     */
    protected $method;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $socialUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true)
     */
    protected $facebookId;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
    */ 
    protected $googleId;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->method = "email";
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = parent::getRoles();

        if (!is_null($this->person) && $this->person->getIsAdmin()) {
            // add admin role
            $roles[] = static::ROLE_SUPER_ADMIN;
        }

        return array_unique($roles);
    }

    public function serialize()
    {
        return serialize(array($this->facebookId, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookId, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }

    
    public function isEqualTo(LoginAccount $loginAccount)
    {
        return ((!is_null($loginAccount)) && ($this->id == $loginAccount->getId()));
    }

    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return LoginAccount
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set socialUrl
     *
     * @param string $socialUrl
     * @return LoginAccount
     */
    public function setSocialUrl($socialUrl)
    {
        $this->socialUrl = $socialUrl;

        return $this;
    }

    /**
     * Get socialUrl
     *
     * @return string 
     */
    public function getSocialUrl()
    {
        return $this->socialUrl;
    }

    /**
     * @param string $facebookId
     * @return void
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        $this->setUsername($facebookId);
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     * @return LoginAccount
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string 
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * Set person
     *
     * @param \TS\ApiBundle\Entity\Person $person
     * @return LoginAccount
     */
    public function setPerson(\TS\ApiBundle\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return \TS\ApiBundle\Entity\Person 
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Whether this loginAccount has a Person that is admin
     * @return boolean
     */
    public function isAdmin() {
        return !is_null($this->getPerson()) && $this->getPerson()->getIsAdmin();
    }

    /**
     * Set registrationName
     *
     * @param string $registrationName
     * @return LoginAccount
     */
    public function setRegistrationName($registrationName)
    {
        $this->registrationName = $registrationName;

        return $this;
    }

    /**
     * Get registrationName
     *
     * @return string 
     */
    public function getRegistrationName()
    {
        return $this->registrationName;
    }
}
