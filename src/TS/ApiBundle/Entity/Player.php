<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Player
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\ApiBundle\Entity\PlayerRepository")
 */
class Player
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1)
     * @Assert\NotBlank(message = "Choose a gender")
     * @Assert\Choice(choices = {"M", "F"}, message = "Choose a valid gender")
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="DisciplinePlayer", mappedBy="player", cascade={"persist"})
     */
    private $disciplinePlayers;
   
    /**
     * @var string
     *
     * @ORM\Column(name="registrationDate", type="datetime")
     */
    private $registrationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Person", inversedBy="players")
     */
    private $person;
    
    /**
     * @ORM\ManyToOne(targetEntity="RegistrationGroup", inversedBy="players")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $registrationGroup;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isContactPlayer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="players")
     * @Assert\NotNull()
     */
    private $tournament;
    
    /**
     * @ORM\OneToMany(targetEntity="RegistrationFormValue", mappedBy="player", cascade={"persist"})
     */
    private $registrationFormValues;
    
    /**
     * @ORM\ManyToMany(targetEntity="\TS\ApiBundle\Entity\Team", mappedBy="players")
     *
     */
    private $teams;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $ready;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nonreadyReason;

    /**
     * @ORM\OneToMany(targetEntity="\TS\FinancialBundle\Entity\BoughtProduct", mappedBy="player")
     *
     */
    private $boughtProducts;
    

    public function __construct()
    {
        $this->setRegistrationDate(new \DateTime());
        $this->setStatus('');
        $this->isContactPlayer = false;
        $this->boughtProducts = new ArrayCollection();
        $this->registrationFormValues = new ArrayCollection();
        $this->disciplinePlayers = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->ready = true;
        $this->name = '-';
    }

    public function __clone()
    {
        if ($this->id) {
            // these values shouldn't be cloned
            $this->disciplinePlayers = new ArrayCollection();
            $this->person = null;
            $this->registrationGroup = null;
            $this->tournament = null;
            $this->registrationFormValues = new ArrayCollection();
            $this->teams = new ArrayCollection();
            $this->boughtProducts = new ArrayCollection();
        }
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Player
     */
    public function setStatus($status)
    {
        if (is_null($status)) {
        	$status = '';
        }
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    
    /**
     * Returns full name (firstName combined with lastName)
     * @param boolean $withNickname Whether to include nickname as well. This would result in firstName "nickName" lastName (if there is a form field with name "Nickname")
     * @return string
     * TODO: use setName() every time something changes, and make $this->name @Assert\NotBlank()
     */
    public function getName($withNickName = true) {
    	if ($withNickName) {
    		// look up nickname form field
    		$nickname = null;
    		foreach ($this->getRegistrationFormValues() as $formValue) {
    			if (strtolower($formValue->getField()->getName()) == 'nickname') {
    				// there is a field with name nickname
    				$nickname = $formValue->getValue();
    				break;
    			}
    		}
    		if (!is_null($nickname) && ($nickname != '')) {
    			return $this->firstName .' "'. $nickname .'" '. $this->lastName;
    		}
    	}
    	return $this->firstName .' '. $this->lastName;
    }

    /**
     * Get totat payment balance
     */
    public function getPaymentBalance() {
        $balance = 0.0;
        foreach ($this->boughtProducts as $product) {
            $balance += $product->getAmount();
        }
        return $balance;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Player
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set registrationDate
     *
     * @param \DateTime $registrationDate
     * @return Player
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    
        return $this;
    }

    /**
     * Get registrationDate
     *
     * @return \DateTime 
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Player
     */
    public function setTournament(\TS\ApiBundle\Entity\Tournament $tournament = null)
    {
        $this->tournament = $tournament;
    
        return $this;
    }

    /**
     * Get tournament
     *
     * @return \TS\ApiBundle\Entity\Tournament 
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * Add registrationFormValues
     *
     * @param \TS\ApiBundle\Entity\RegistrationFormValue $registrationFormValues
     * @return Player
     */
    public function addRegistrationFormValue(\TS\ApiBundle\Entity\RegistrationFormValue $registrationFormValues)
    {
        $this->registrationFormValues[] = $registrationFormValues;
    
        return $this;
    }

    /**
     * Remove registrationFormValues
     *
     * @param \TS\ApiBundle\Entity\RegistrationFormValue $registrationFormValues
     */
    public function removeRegistrationFormValue(\TS\ApiBundle\Entity\RegistrationFormValue $registrationFormValues)
    {
        $this->registrationFormValues->removeElement($registrationFormValues);
    }

    /**
     * Get registrationFormValues
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRegistrationFormValues()
    {
        return $this->registrationFormValues;
    }

    /**
     * Add teams
     *
     * @param \TS\ApiBundle\Entity\Team $teams
     * @return Player
     */
    public function addTeam(\TS\ApiBundle\Entity\Team $teams)
    {
        $this->teams[] = $teams;
    
        return $this;
    }

    /**
     * Remove teams
     *
     * @param \TS\ApiBundle\Entity\Team $teams
     */
    public function removeTeam(\TS\ApiBundle\Entity\Team $teams)
    {
        $this->teams->removeElement($teams);
    }

    /**
     * Get teams
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * Set ready
     *
     * @param boolean $ready
     * @return Player
     */
    public function setReady($ready)
    {
        $this->ready = $ready;
    
        return $this;
    }

    /**
     * Get ready
     *
     * @return boolean 
     */
    public function getReady()
    {
        return $this->ready;
    }

    /**
     * Set registrationGroup
     *
     * @param \TS\ApiBundle\Entity\RegistrationGroup $registrationGroup
     * @return Player
     */
    public function setRegistrationGroup(\TS\ApiBundle\Entity\RegistrationGroup $registrationGroup = null)
    {
        $this->registrationGroup = $registrationGroup;

        return $this;
    }

    /**
     * Get registrationGroup
     *
     * @return \TS\ApiBundle\Entity\RegistrationGroup 
     */
    public function getRegistrationGroup()
    {
        return $this->registrationGroup;
    }

    /**
     * Set isContactPlayer
     *
     * @param boolean $isContactPlayer
     * @return Player
     */
    public function setIsContactPlayer($isContactPlayer)
    {
        $this->isContactPlayer = $isContactPlayer;

        return $this;
    }

    /**
     * Get isContactPlayer
     *
     * @return boolean 
     */
    public function getIsContactPlayer()
    {
        return $this->isContactPlayer;
    }


    /**
     * Set person
     *
     * @param \TS\ApiBundle\Entity\Person $person
     * @return Player
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
     * Set firstName
     *
     * @param string $firstName
     * @return Player
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
     * @return Player
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
     * @return Player
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

    /**
     * Add boughtProducts
     *
     * @param \TS\FinancialBundle\Entity\BoughtProduct $boughtProducts
     * @return Player
     */
    public function addBoughtProduct(\TS\FinancialBundle\Entity\BoughtProduct $boughtProducts)
    {
        $this->boughtProducts[] = $boughtProducts;

        return $this;
    }

    /**
     * Remove boughtProducts
     *
     * @param \TS\FinancialBundle\Entity\BoughtProduct $boughtProducts
     */
    public function removeBoughtProduct(\TS\FinancialBundle\Entity\BoughtProduct $boughtProducts)
    {
        $this->boughtProducts->removeElement($boughtProducts);
    }

    /**
     * Get boughtProducts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBoughtProducts()
    {
        return $this->boughtProducts;
    }

    /**
     * Set nonreadyReason
     *
     * @param string $nonreadyReason
     * @return Player
     */
    public function setNonreadyReason($nonreadyReason)
    {
        $this->nonreadyReason = $nonreadyReason;

        return $this;
    }

    /**
     * Get nonreadyReason
     *
     * @return string 
     */
    public function getNonreadyReason()
    {
        return $this->nonreadyReason;
    }

    /**
     * Add disciplinePlayers
     *
     * @param \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayers
     * @return Player
     */
    public function addDisciplinePlayer(\TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayers)
    {
        $this->disciplinePlayers[] = $disciplinePlayers;

        return $this;
    }

    /**
     * Remove disciplinePlayers
     *
     * @param \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayers
     */
    public function removeDisciplinePlayer(\TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayers)
    {
        $this->disciplinePlayers->removeElement($disciplinePlayers);
    }

    /**
     * Get disciplinePlayers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDisciplinePlayers()
    {
        return $this->disciplinePlayers;
    }
}
