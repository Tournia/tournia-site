<?php
namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="TS\ApiBundle\Entity\PersonRepository")
 * @ORM\Table()
 * @UniqueEntity("email", message = "This email address is already in use.")
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\LoginAccount", mappedBy="person", cascade={"persist"})
     */
    private $loginAccounts;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Please enter your name")
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long."
     * )
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="\TS\ApiBundle\Entity\Tournament", mappedBy="organizerPersons", cascade={"persist"})
     */
    private $organizingTournaments;
    
    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\Player", mappedBy="person", cascade={"persist"})
     */
    private $players;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email(
     *     message = "The email {{ value }} is not a valid email address.",
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdmin;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Choice(choices = {"M", "F"}, message = "Choose a valid gender")
     */
    private $gender;

    /**
     * @ORM\OneToMany(targetEntity="\TS\FinancialBundle\Entity\Cart", mappedBy="execPerson")
     */
    private $carts;

    /**
     * @ORM\OneToMany(targetEntity="\TS\NotificationBundle\Entity\NotificationSubscription", mappedBy="person", cascade={"persist"})
     */
    private $notificationSubscriptions;

    
    public function __construct()
    {
        $this->loginAccounts = new ArrayCollection();
        $this->organizingTournaments = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->isAdmin = false;
        $this->notificationSubscriptions = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Person
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get informal name
     * Returns first name, or if this is empty, the name
     *
     * @return string
     */
    public function getInformalName()
    {
        return (!empty($this->firstName)) ? $this->firstName : $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Person
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return Person
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean 
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * Add loginAccounts
     *
     * @param \TS\ApiBundle\Entity\LoginAccount $loginAccounts
     * @return Person
     */
    public function addLoginAccount(\TS\ApiBundle\Entity\LoginAccount $loginAccounts)
    {
        $this->loginAccounts[] = $loginAccounts;

        return $this;
    }

    /**
     * Remove loginAccounts
     *
     * @param \TS\ApiBundle\Entity\LoginAccount $loginAccounts
     */
    public function removeLoginAccount(\TS\ApiBundle\Entity\LoginAccount $loginAccounts)
    {
        $this->loginAccounts->removeElement($loginAccounts);
    }

    /**
     * Get loginAccounts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLoginAccounts()
    {
        return $this->loginAccounts;
    }

    /**
     * Add organizingTournaments
     *
     * @param \TS\ApiBundle\Entity\Tournament $organizingTournaments
     * @return Person
     */
    public function addOrganizingTournament(\TS\ApiBundle\Entity\Tournament $organizingTournaments)
    {
        $this->organizingTournaments[] = $organizingTournaments;

        return $this;
    }

    /**
     * Remove organizingTournaments
     *
     * @param \TS\ApiBundle\Entity\Tournament $organizingTournaments
     */
    public function removeOrganizingTournament(\TS\ApiBundle\Entity\Tournament $organizingTournaments)
    {
        $this->organizingTournaments->removeElement($organizingTournaments);
    }

    /**
     * Get organizingTournaments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrganizingTournaments()
    {
        return $this->organizingTournaments;
    }

    /**
     * Add players
     *
     * @param \TS\ApiBundle\Entity\Player $players
     * @return Person
     */
    public function addPlayer(\TS\ApiBundle\Entity\Player $players)
    {
        $this->players[] = $players;

        return $this;
    }

    /**
     * Remove players
     *
     * @param \TS\ApiBundle\Entity\Player $players
     */
    public function removePlayer(\TS\ApiBundle\Entity\Player $players)
    {
        $this->players->removeElement($players);
    }

    /**
     * Get players
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlayers()
    {
        return $this->players;
    }


    /**
     * Set firstName
     *
     * @param string $firstName
     * @return Person
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Person
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return Person
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }

    public function isEqualTo(Person $person)
    {
        return ((!is_null($person)) && ($this->id == $person->getId()));
    }

    /**
     * Add carts
     *
     * @param \TS\FinancialBundle\Entity\Cart $carts
     * @return Person
     */
    public function addCart(\TS\FinancialBundle\Entity\Cart $carts)
    {
        $this->carts[] = $carts;

        return $this;
    }

    /**
     * Remove carts
     *
     * @param \TS\FinancialBundle\Entity\Cart $carts
     */
    public function removeCart(\TS\FinancialBundle\Entity\Cart $carts)
    {
        $this->carts->removeElement($carts);
    }

    /**
     * Get carts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * Get gravatarHash
     *
     * @return string
     */
    public function getGravatarHash()
    {
        return md5( strtolower( trim( $this->email ) ) );
    }

    /**
     * Add notificationSubscriptions
     *
     * @param \TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscriptions
     * @return Person
     */
    public function addNotificationSubscription(\TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscriptions)
    {
        $this->notificationSubscriptions[] = $notificationSubscriptions;

        return $this;
    }

    /**
     * Remove notificationSubscriptions
     *
     * @param \TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscriptions
     */
    public function removeNotificationSubscription(\TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscriptions)
    {
        $this->notificationSubscriptions->removeElement($notificationSubscriptions);
    }

    /**
     * Get notificationSubscriptions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotificationSubscriptions()
    {
        return $this->notificationSubscriptions;
    }
}
