<?php

namespace TS\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Tournament
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\ApiBundle\Entity\TournamentRepository")
 * @UniqueEntity("url")
 */
class Tournament
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $url;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDateTime", type="datetime")
     * @Assert\NotBlank()
     */
    private $startDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDateTime", type="datetime")
     * @Assert\NotBlank()
     */
    private $endDateTime;
    
    /**
     * @ORM\ManyToMany(targetEntity="\TS\ApiBundle\Entity\Person", inversedBy="organizingTournaments", cascade={"persist"})
     *
     */
    private $organizerPersons;
    
    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="tournament")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $players;
    
    /**
     * @ORM\OneToMany(targetEntity="RegistrationGroup", mappedBy="tournament")
     */
    private $registrationGroups;
    
    /**
     * @var string
     *
     * @ORM\Column(name="emailFrom", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $emailFrom;
    
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $contactName;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $organizationEmailOnChange;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $registrationGroupEnabled;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $registrationGroupRequired;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=16)
     * @Assert\NotBlank(message = "Choose a currency")
     * @Assert\Choice(choices = {"EUR", "GBP", "NOK", "USD"}, message = "Choose a valid currency")
     */
    private $paymentCurrency;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $paymentUpdateStatus;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable=true)
     */
    private $paymentUpdateFromStatus;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $paymentUpdateToStatus;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $newPlayerStatus;
    
    /**
     * @var array
     * 
     * @ORM\Column(type="array")
     */
    private $statusOptions;
    
    /**
     * @ORM\OneToMany(targetEntity="RegistrationFormField", mappedBy="tournament", cascade={"persist", "remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $registrationFormFields;
    
    /**
     * @ORM\OneToMany(targetEntity="Discipline", mappedBy="tournament", cascade={"persist", "remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $disciplines;

    /**
     * @ORM\OneToMany(targetEntity="DisciplineType", mappedBy="tournament", cascade={"persist", "remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $disciplineTypes;

    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\UpdateMessage", mappedBy="tournament", cascade={"persist"})
     *
     */
    private $updateMessages;

    /**
     * @ORM\OneToMany(targetEntity="\TS\NotificationBundle\Entity\NotificationLog", mappedBy="tournament")
     *
     */
    private $notificationLogs;
    
    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\Location", mappedBy="tournament", cascade={"persist", "remove"})
     *
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\Pool", mappedBy="tournament", cascade={"persist", "remove"})
     *
     */
    private $pools;
    
    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\Match", mappedBy="tournament")
     *
     */
    private $matches;
    
    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\Announcement", mappedBy="tournament")
     *
     */
    private $announcements;
    
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value = 1, message = "The minimum number of sets is 1")
     */
    private $nrSets;

    /**
     * @var \TS\SiteBundle\Entity\Site $site
     * @ORM\OneToOne(targetEntity="\TS\SiteBundle\Entity\Site", inversedBy="tournament", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\ApiKey", mappedBy="tournament", cascade={"all"})
     *
     */
    private $apiKeys;

    /**
     * @ORM\OneToMany(targetEntity="\TS\FinancialBundle\Entity\Product", mappedBy="tournament", cascade={"all"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="\TS\FinancialBundle\Entity\BoughtProduct", mappedBy="tournament")
     *
     */
    private $boughtProducts;

    /**
     * @ORM\OneToMany(targetEntity="\TS\FinancialBundle\Entity\PayOut", mappedBy="tournament")
     *
     */
    private $payOuts;

    /**
     * Can be free, invoice or payments
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    private $financialMethod;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $paypalAccountUsername;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $paypalAccountPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $paypalAccountSignature;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $financialPayoutPaypalEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $financialPayoutBankAccount;


    /**
     * @ORM\Column(type="boolean")
     */
    private $organizationPaysServiceFee;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $checkScoreMin;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $checkScoreMax;

    /**
     * @var \TS\ApiBundle\Entity\Authorization $authorization
     * @ORM\OneToOne(targetEntity="\TS\ApiBundle\Entity\Authorization", inversedBy="tournament", cascade={"all"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $authorization;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value = 0, message = "The minimum number is 0")
     */
    private $maxRegistrationDisciplines;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->organizerPersons = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->registrationGroups = new ArrayCollection();
        $this->statusOptions = array("Registered", "Paid", "Cancelled");
        $this->paymentCurrency = 'EUR';
        $this->paymentUpdateStatus = true;
        $this->paymentUpdateFromStatus = "Registered";
        $this->paymentUpdateToStatus = "Paid";
        $this->newPlayerStatus = "Registered";
        $this->registrationFormFields = new ArrayCollection();
        $this->disciplines = new ArrayCollection();
        $this->disciplineTypes = new ArrayCollection();
        $this->updateMessages = new ArrayCollection();
        $this->notificationLogs = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->pools = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->announcements = new ArrayCollection();
        $this->payOuts = new ArrayCollection();
        $this->boughtProducts = new ArrayCollection();
        $this->apiKeys = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->nrSets = 2;
        $this->registrationGroupEnabled = true;
        $this->registrationGroupRequired = true;
        $this->organizationPaysServiceFee = false;
        $this->startDateTime = new \DateTime("now");
        $this->endDateTime = new \DateTime("now");
        $this->authorization = new Authorization();
        $this->financialMethod = "payments";
        $this->maxRegistrationDisciplines = 0;
    }

    public function __clone()
    {
        if ($this->id) {
            // these values shouldn't be cloned
            $this->players = new ArrayCollection();
            $this->registrationGroups = new ArrayCollection();
            $this->payOuts = new ArrayCollection();
            $this->boughtProducts = new ArrayCollection();
            $this->announcements = new ArrayCollection();
            $this->matches = new ArrayCollection();
            $this->notificationLogs = new ArrayCollection();
            $this->updateMessages = new ArrayCollection();
            $this->apiKeys = new ArrayCollection();

            // these values should be cloned, but has to be done manually (because of the tournament reference)
            $this->organizerPersons = new ArrayCollection();
            $this->disciplines = new ArrayCollection();
            $this->disciplineTypes = new ArrayCollection();
            $this->authorization = null;
            $this->registrationFormFields = new ArrayCollection();
            $this->locations = new ArrayCollection();
            $this->pools = new ArrayCollection();
            $this->products = new ArrayCollection();
            $this->site = null;
        }
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
     * @return Tournament
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
     * Set url
     *
     * @param string $url
     * @return Tournament
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set startDateTime
     *
     * @param \DateTime $startDateTime
     * @return this
     */
    public function setStartDateTime($startDateTime)
    {
        $this->startDateTime = $startDateTime;
    
        return $this;
    }

    /**
     * Get startDateTime
     *
     * @return \DateTime 
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * Set endDateTime
     *
     * @param \DateTime $endDateTime
     * @return this
     */
    public function setEndDateTime($endDateTime)
    {
        $this->endDateTime = $endDateTime;
    
        return $this;
    }

    /**
     * Get endDateTime
     *
     * @return \DateTime 
     */
    public function getEndDateTime()
    {
        return $this->endDateTime;
    }

    /**
     * Set emailFrom
     *
     * @param string $emailFrom
     * @return Tournament
     */
    public function setEmailFrom($emailFrom)
    {
        $this->emailFrom = $emailFrom;

        return $this;
    }

    /**
     * Get emailFrom
     *
     * @return string 
     */
    public function getEmailFrom()
    {
        return $this->emailFrom;
    }

    /**
     * Set contactName
     *
     * @param string $contactName
     * @return Tournament
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Get contactName
     *
     * @return string 
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set paymentCurrency
     *
     * @param string $paymentCurrency
     * @return Tournament
     */
    public function setPaymentCurrency($paymentCurrency)
    {
        $this->paymentCurrency = $paymentCurrency;

        return $this;
    }

    /**
     * Get paymentCurrency
     *
     * @return string 
     */
    public function getPaymentCurrency()
    {
        return $this->paymentCurrency;
    }

    /**
     * Set paymentUpdateStatus
     *
     * @param boolean $paymentUpdateStatus
     * @return Tournament
     */
    public function setPaymentUpdateStatus($paymentUpdateStatus)
    {
        $this->paymentUpdateStatus = $paymentUpdateStatus;

        return $this;
    }

    /**
     * Get paymentUpdateStatus
     *
     * @return boolean 
     */
    public function getPaymentUpdateStatus()
    {
        return $this->paymentUpdateStatus;
    }

    /**
     * Set paymentUpdateFromStatus
     *
     * @param string $paymentUpdateFromStatus
     * @return Tournament
     */
    public function setPaymentUpdateFromStatus($paymentUpdateFromStatus)
    {
        $this->paymentUpdateFromStatus = $paymentUpdateFromStatus;

        return $this;
    }

    /**
     * Get paymentUpdateFromStatus
     *
     * @return string 
     */
    public function getPaymentUpdateFromStatus()
    {
        return $this->paymentUpdateFromStatus;
    }

    /**
     * Set paymentUpdateToStatus
     *
     * @param string $paymentUpdateToStatus
     * @return Tournament
     */
    public function setPaymentUpdateToStatus($paymentUpdateToStatus)
    {
        $this->paymentUpdateToStatus = $paymentUpdateToStatus;

        return $this;
    }

    /**
     * Get paymentUpdateToStatus
     *
     * @return string 
     */
    public function getPaymentUpdateToStatus()
    {
        return $this->paymentUpdateToStatus;
    }

    /**
     * Set newPlayerStatus
     *
     * @param string $newPlayerStatus
     * @return Tournament
     */
    public function setNewPlayerStatus($newPlayerStatus)
    {
        $this->newPlayerStatus = $newPlayerStatus;

        return $this;
    }

    /**
     * Get newPlayerStatus
     *
     * @return string 
     */
    public function getNewPlayerStatus()
    {
        return $this->newPlayerStatus;
    }

    /**
     * Set statusOptions
     *
     * @param array $statusOptions
     * @return Tournament
     */
    public function setStatusOptions($statusOptions)
    {
        $this->statusOptions = $statusOptions;

        return $this;
    }

    /**
     * Get statusOptions
     *
     * @return array 
     */
    public function getStatusOptions()
    {
        return $this->statusOptions;
    }

    /**
     * Set nrSets
     *
     * @param integer $nrSets
     * @return Tournament
     */
    public function setNrSets($nrSets)
    {
        $this->nrSets = $nrSets;

        return $this;
    }

    /**
     * Get nrSets
     *
     * @return integer 
     */
    public function getNrSets()
    {
        return $this->nrSets;
    }

    /**
     * Add organizerPersons
     *
     * @param \TS\ApiBundle\Entity\Person $organizerPersons
     * @return Tournament
     */
    public function addOrganizerPerson(\TS\ApiBundle\Entity\Person $organizerPersons)
    {
        $this->organizerPersons[] = $organizerPersons;

        return $this;
    }

    /**
     * Remove organizerPersons
     *
     * @param \TS\ApiBundle\Entity\Person $organizerPersons
     */
    public function removeOrganizerPerson(\TS\ApiBundle\Entity\Person $organizerPersons)
    {
        $this->organizerPersons->removeElement($organizerPersons);
    }

    /**
     * Get organizerPersons
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrganizerPersons()
    {
        return $this->organizerPersons;
    }

    /**
     * Add players
     *
     * @param \TS\ApiBundle\Entity\Player $players
     * @return Tournament
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
     * Add RegistrationGroup
     *
     * @param \TS\ApiBundle\Entity\RegistrationGroup $registrationGroups
     * @return Tournament
     */
    public function addRegistrationGroup(\TS\ApiBundle\Entity\RegistrationGroup $registrationGroups)
    {
        $this->registrationGroups[] = $registrationGroups;

        return $this;
    }

    /**
     * Remove RegistrationGroup
     *
     * @param \TS\ApiBundle\Entity\RegistrationGroup $registrationGroups
     */
    public function removeRegistrationGroup(\TS\ApiBundle\Entity\RegistrationGroup $registrationGroups)
    {
        $this->registrationGroups->removeElement($registrationGroups);
    }

    /**
     * Get RegistrationGroups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRegistrationGroups()
    {
        return $this->registrationGroups;
    }

    /**
     * Add registrationFormFields
     *
     * @param \TS\ApiBundle\Entity\RegistrationFormField $registrationFormFields
     * @return Tournament
     */
    public function addRegistrationFormField(\TS\ApiBundle\Entity\RegistrationFormField $registrationFormFields)
    {
        $this->registrationFormFields[] = $registrationFormFields;

        return $this;
    }

    /**
     * Remove registrationFormFields
     *
     * @param \TS\ApiBundle\Entity\RegistrationFormField $registrationFormFields
     */
    public function removeRegistrationFormField(\TS\ApiBundle\Entity\RegistrationFormField $registrationFormFields)
    {
        $this->registrationFormFields->removeElement($registrationFormFields);
    }

    /**
     * Get registrationFormFields
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRegistrationFormFields()
    {
        return $this->registrationFormFields;
    }

    /**
     * Add disciplines
     *
     * @param \TS\ApiBundle\Entity\Discipline $disciplines
     * @return Tournament
     */
    public function addDiscipline(\TS\ApiBundle\Entity\Discipline $disciplines)
    {
        $this->disciplines[] = $disciplines;

        return $this;
    }

    /**
     * Remove disciplines
     *
     * @param \TS\ApiBundle\Entity\Discipline $disciplines
     */
    public function removeDiscipline(\TS\ApiBundle\Entity\Discipline $disciplines)
    {
        $this->disciplines->removeElement($disciplines);
    }

    /**
     * Get disciplines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDisciplines()
    {
        return $this->disciplines;
    }

    /**
     * Add updateMessages
     *
     * @param \TS\ApiBundle\Entity\UpdateMessage $updateMessages
     * @return Tournament
     */
    public function addUpdateMessage(\TS\ApiBundle\Entity\UpdateMessage $updateMessages)
    {
        $this->updateMessages[] = $updateMessages;

        return $this;
    }

    /**
     * Remove updateMessages
     *
     * @param \TS\ApiBundle\Entity\UpdateMessage $updateMessages
     */
    public function removeUpdateMessage(\TS\ApiBundle\Entity\UpdateMessage $updateMessages)
    {
        $this->updateMessages->removeElement($updateMessages);
    }

    /**
     * Get updateMessages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUpdateMessages()
    {
        return $this->updateMessages;
    }

    /**
     * Add locations
     *
     * @param \TS\ApiBundle\Entity\Location $locations
     * @return Tournament
     */
    public function addLocation(\TS\ApiBundle\Entity\Location $locations)
    {
        $this->locations[] = $locations;

        return $this;
    }

    /**
     * Remove locations
     *
     * @param \TS\ApiBundle\Entity\Location $locations
     */
    public function removeLocation(\TS\ApiBundle\Entity\Location $locations)
    {
        $this->locations->removeElement($locations);
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Add matches
     *
     * @param \TS\ApiBundle\Entity\Match $matches
     * @return Tournament
     */
    public function addMatch(\TS\ApiBundle\Entity\Match $matches)
    {
        $this->matches[] = $matches;

        return $this;
    }

    /**
     * Remove matches
     *
     * @param \TS\ApiBundle\Entity\Match $matches
     */
    public function removeMatch(\TS\ApiBundle\Entity\Match $matches)
    {
        $this->matches->removeElement($matches);
    }

    /**
     * Get matches
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Add announcements
     *
     * @param \TS\ApiBundle\Entity\Announcement $announcements
     * @return Tournament
     */
    public function addAnnouncement(\TS\ApiBundle\Entity\Announcement $announcements)
    {
        $this->announcements[] = $announcements;

        return $this;
    }

    /**
     * Remove announcements
     *
     * @param \TS\ApiBundle\Entity\Announcement $announcements
     */
    public function removeAnnouncement(\TS\ApiBundle\Entity\Announcement $announcements)
    {
        $this->announcements->removeElement($announcements);
    }

    /**
     * Get announcements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnnouncements()
    {
        return $this->announcements;
    }

    /**
     * Set site
     *
     * @param \TS\SiteBundle\Entity\Site $site
     * @return Tournament
     */
    public function setSite(\TS\SiteBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \TS\SiteBundle\Entity\Site 
     */
    public function getSite()
    {
        return $this->site;
    }


    /**
     * Set organizationEmailOnChange
     *
     * @param string $organizationEmailOnChange
     * @return Tournament
     */
    public function setOrganizationEmailOnChange($organizationEmailOnChange)
    {
        $this->organizationEmailOnChange = $organizationEmailOnChange;
    
        return $this;
    }

    /**
     * Get organizationEmailOnChange
     *
     * @return string 
     */
    public function getOrganizationEmailOnChange()
    {
        return $this->organizationEmailOnChange;
    }

    /**
     * Add disciplines
     * @see addDiscipline()
     *
     * @param \TS\ApiBundle\Entity\Discipline $disciplines
     * @return Tournament
     */
    private function addCategorie(\TS\ApiBundle\Entity\Discipline $disciplines)
    {
        return false;
    }

    /**
     * Remove disciplines
     * @see removeDiscipline()
     *
     * @param \TS\ApiBundle\Entity\Discipline $disciplines
     */
    private function removeCategorie(\TS\ApiBundle\Entity\Discipline $disciplines)
    {
        return false;
    }

    /**
     * Add matches
     * @see addMatch()
     *
     * @param \TS\ApiBundle\Entity\Match $matches
     * @return Tournament
     */
    private function addMatche(\TS\ApiBundle\Entity\Match $matches)
    {
        return false;
    }

    /**
     * Remove matches
     * @see removeMatch()
     *
     * @param \TS\ApiBundle\Entity\Match $matches
     */
    private function removeMatche(\TS\ApiBundle\Entity\Match $matches)
    {
        return false;
    }

    /**
     * Set registrationGroupEnabled
     *
     * @param boolean $registrationGroupEnabled
     * @return Tournament
     */
    public function setRegistrationGroupEnabled($registrationGroupEnabled)
    {
        $this->registrationGroupEnabled = $registrationGroupEnabled;

        return $this;
    }

    /**
     * Get registrationGroupEnabled
     *
     * @return boolean 
     */
    public function getRegistrationGroupEnabled()
    {
        return $this->registrationGroupEnabled;
    }

    /**
     * Set registrationGroupRequired
     *
     * @param boolean $registrationGroupRequired
     * @return Tournament
     */
    public function setRegistrationGroupRequired($registrationGroupRequired)
    {
        $this->registrationGroupRequired = $registrationGroupRequired;

        return $this;
    }

    /**
     * Get registrationGroupRequired
     *
     * @return boolean 
     */
    public function getRegistrationGroupRequired()
    {
        return $this->registrationGroupRequired;
    }

    /**
     * Add products
     *
     * @param \TS\FinancialBundle\Entity\Product $products
     * @return Tournament
     */
    public function addProduct(\TS\FinancialBundle\Entity\Product $products)
    {
        $this->products[] = $products;

        return $this;
    }

    /**
     * Remove products
     *
     * @param \TS\FinancialBundle\Entity\Product $products
     */
    public function removeProduct(\TS\FinancialBundle\Entity\Product $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add boughtProducts
     *
     * @param \TS\FinancialBundle\Entity\BoughtProduct $boughtProducts
     * @return Tournament
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
     * Get financialEnabled
     *
     * @return boolean 
     */
    public function getFinancialEnabled()
    {
        return $this->financialMethod == "payments" && $this->isPaypalAccountEnabled();
    }

    /**
     * Set financialPayoutPaypalEmail
     *
     * @param string $financialPayoutPaypalEmail
     * @return Tournament
     */
    public function setFinancialPayoutPaypalEmail($financialPayoutPaypalEmail)
    {
        $this->financialPayoutPaypalEmail = $financialPayoutPaypalEmail;

        return $this;
    }

    /**
     * Get financialPayoutPaypalEmail
     *
     * @return string 
     */
    public function getFinancialPayoutPaypalEmail()
    {
        return $this->financialPayoutPaypalEmail;
    }

    /**
     * Set organizationPaysServiceFee
     *
     * @param boolean $organizationPaysServiceFee
     * @return Tournament
     */
    public function setOrganizationPaysServiceFee($organizationPaysServiceFee)
    {
        $this->organizationPaysServiceFee = $organizationPaysServiceFee;

        return $this;
    }

    /**
     * Get organizationPaysServiceFee
     *
     * @return boolean 
     */
    public function getOrganizationPaysServiceFee()
    {
        return $this->organizationPaysServiceFee;
    }

    /**
     * Add payOuts
     *
     * @param \TS\FinancialBundle\Entity\PayOut $payOuts
     * @return Tournament
     */
    public function addPayOut(\TS\FinancialBundle\Entity\PayOut $payOuts)
    {
        $this->payOuts[] = $payOuts;

        return $this;
    }

    /**
     * Remove payOuts
     *
     * @param \TS\FinancialBundle\Entity\PayOut $payOuts
     */
    public function removePayOut(\TS\FinancialBundle\Entity\PayOut $payOuts)
    {
        $this->payOuts->removeElement($payOuts);
    }

    /**
     * Get payOuts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPayOuts()
    {
        return $this->payOuts;
    }

    /**
     * Set financialPayoutBankAccount
     *
     * @param string $financialPayoutBankAccount
     * @return Tournament
     */
    public function setFinancialPayoutBankAccount($financialPayoutBankAccount)
    {
        $this->financialPayoutBankAccount = $financialPayoutBankAccount;

        return $this;
    }

    /**
     * Get financialPayoutBankAccount
     *
     * @return string 
     */
    public function getFinancialPayoutBankAccount()
    {
        return $this->financialPayoutBankAccount;
    }

    /**
     * Set checkScoreMin
     *
     * @param integer $checkScoreMin
     * @return Tournament
     */
    public function setCheckScoreMin($checkScoreMin)
    {
        $this->checkScoreMin = $checkScoreMin;

        return $this;
    }

    /**
     * Get checkScoreMin
     *
     * @return integer 
     */
    public function getCheckScoreMin()
    {
        return $this->checkScoreMin;
    }

    /**
     * Set checkScoreMax
     *
     * @param integer $checkScoreMax
     * @return Tournament
     */
    public function setCheckScoreMax($checkScoreMax)
    {
        $this->checkScoreMax = $checkScoreMax;

        return $this;
    }

    /**
     * Get checkScoreMax
     *
     * @return integer 
     */
    public function getCheckScoreMax()
    {
        return $this->checkScoreMax;
    }

    /**
     * Set authorization
     *
     * @param \TS\ApiBundle\Entity\Authorization $authorization
     * @return Tournament
     */
    public function setAuthorization(\TS\ApiBundle\Entity\Authorization $authorization)
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Get authorization
     *
     * @return \TS\ApiBundle\Entity\Authorization 
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * Set financialMethod
     *
     * @param string $financialMethod
     * @return Tournament
     */
    public function setFinancialMethod($financialMethod)
    {
        $this->financialMethod = $financialMethod;

        return $this;
    }

    /**
     * Get financialMethod
     *
     * @return string 
     */
    public function getFinancialMethod()
    {
        return $this->financialMethod;
    }

    /**
     * Set paypalAccountUsername
     *
     * @param string $paypalAccountUsername
     * @return Tournament
     */
    public function setPaypalAccountUsername($paypalAccountUsername)
    {
        $this->paypalAccountUsername = $paypalAccountUsername;

        return $this;
    }

    /**
     * Get paypalAccountUsername
     *
     * @return string
     */
    public function getPaypalAccountUsername()
    {
        return $this->paypalAccountUsername;
    }

    /**
     * Set paypalAccountPassword
     *
     * @param string $paypalAccountPassword
     * @return Tournament
     */
    public function setPaypalAccountPassword($paypalAccountPassword)
    {
        $this->paypalAccountPassword = $paypalAccountPassword;

        return $this;
    }

    /**
     * Get paypalAccountPassword
     *
     * @return string
     */
    public function getPaypalAccountPassword()
    {
        return $this->paypalAccountPassword;
    }

    /**
     * Set paypalAccountSignature
     *
     * @param string $paypalAccountSignature
     * @return Tournament
     */
    public function setPaypalAccountSignature($paypalAccountSignature)
    {
        $this->paypalAccountSignature = $paypalAccountSignature;

        return $this;
    }

    /**
     * Get paypalAccountSignature
     *
     * @return string
     */
    public function getPaypalAccountSignature()
    {
        return $this->paypalAccountSignature;
    }

    /**
     * Is PayPal Account enabled?
     * @return bool
     */
    public function isPaypalAccountEnabled() {
        return $this->paypalAccountUsername != null && $this->paypalAccountPassword != null && $this->paypalAccountSignature != null;
    }

    /**
     * Add notificationLogs
     *
     * @param \TS\NotificationBundle\Entity\NotificationLog $notificationLogs
     * @return Tournament
     */
    public function addNotificationLog(\TS\NotificationBundle\Entity\NotificationLog $notificationLogs)
    {
        $this->notificationLogs[] = $notificationLogs;

        return $this;
    }

    /**
     * Remove notificationLogs
     *
     * @param \TS\NotificationBundle\Entity\NotificationLog $notificationLogs
     */
    public function removeNotificationLog(\TS\NotificationBundle\Entity\NotificationLog $notificationLogs)
    {
        $this->notificationLogs->removeElement($notificationLogs);
    }

    /**
     * Get notificationLogs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotificationLogs()
    {
        return $this->notificationLogs;
    }

    /**
     * Add disciplineTypes
     *
     * @param \TS\ApiBundle\Entity\DisciplineType $disciplineTypes
     * @return Tournament
     */
    public function addDisciplineType(\TS\ApiBundle\Entity\DisciplineType $disciplineTypes)
    {
        $this->disciplineTypes[] = $disciplineTypes;

        return $this;
    }

    /**
     * Remove disciplineTypes
     *
     * @param \TS\ApiBundle\Entity\DisciplineType $disciplineTypes
     */
    public function removeDisciplineType(\TS\ApiBundle\Entity\DisciplineType $disciplineTypes)
    {
        $this->disciplineTypes->removeElement($disciplineTypes);
    }

    /**
     * Get disciplineTypes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDisciplineTypes()
    {
        return $this->disciplineTypes;
    }

    /**
     * Add pools
     *
     * @param \TS\ApiBundle\Entity\Pool $pools
     * @return Tournament
     */
    public function addPool(\TS\ApiBundle\Entity\Pool $pools)
    {
        $this->pools[] = $pools;

        return $this;
    }

    /**
     * Remove pools
     *
     * @param \TS\ApiBundle\Entity\Pool $pools
     */
    public function removePool(\TS\ApiBundle\Entity\Pool $pools)
    {
        $this->pools->removeElement($pools);
    }

    /**
     * Get pools
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPools()
    {
        return $this->pools;
    }

    /**
     * Set maxRegistrationDisciplines
     *
     * @param integer $maxRegistrationDisciplines
     * @return Tournament
     */
    public function setMaxRegistrationDisciplines($maxRegistrationDisciplines)
    {
        $this->maxRegistrationDisciplines = $maxRegistrationDisciplines;

        return $this;
    }

    /**
     * Get maxRegistrationDisciplines
     *
     * @return integer
     */
    public function getMaxRegistrationDisciplines()
    {
        return $this->maxRegistrationDisciplines;
    }

    /**
     * Add apiKey
     *
     * @param \TS\ApiBundle\Entity\ApiKey $apiKey
     *
     * @return Tournament
     */
    public function addApiKey(\TS\ApiBundle\Entity\ApiKey $apiKey)
    {
        $this->apiKeys[] = $apiKey;

        return $this;
    }

    /**
     * Remove apiKey
     *
     * @param \TS\ApiBundle\Entity\ApiKey $apiKey
     */
    public function removeApiKey(\TS\ApiBundle\Entity\ApiKey $apiKey)
    {
        $this->apiKeys->removeElement($apiKey);
    }

    /**
     * Get apiKeys
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApiKeys()
    {
        return $this->apiKeys;
    }
}
