<?php

namespace TS\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * NotificationSubscription
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\NotificationBundle\Entity\NotificationSubscriptionRepository")
 * @UniqueEntity("deviceToken")
 */
class NotificationSubscription
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
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Person", inversedBy="notificationSubscriptions")
     */
    private $person;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Assert\NotBlank()
     */
    private $deviceToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=false)
     * @Assert\NotBlank()
     */
    private $platform;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $upcomingMatchPeriod;

    /**
     * @ORM\Column(type="boolean")
     */
    private $newMatchEnabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $startMatchEnabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $scoreMatchEnabled;




    /**
     * Constructor
     */
    public function __construct()
    {

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
     * Set deviceToken
     *
     * @param string $deviceToken
     * @return NotificationSubscription
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;

        return $this;
    }

    /**
     * Get deviceToken
     *
     * @return string 
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return NotificationSubscription
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform
     *
     * @return string 
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return NotificationSubscription
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set upcomingMatchPeriod
     *
     * @param integer $upcomingMatchPeriod
     * @return NotificationSubscription
     */
    public function setUpcomingMatchPeriod($upcomingMatchPeriod)
    {
        $this->upcomingMatchPeriod = $upcomingMatchPeriod;

        return $this;
    }

    /**
     * Get upcomingMatchPeriod
     *
     * @return integer 
     */
    public function getUpcomingMatchPeriod()
    {
        return $this->upcomingMatchPeriod;
    }

    /**
     * Set newMatchEnabled
     *
     * @param boolean $newMatchEnabled
     * @return NotificationSubscription
     */
    public function setNewMatchEnabled($newMatchEnabled)
    {
        $this->newMatchEnabled = $newMatchEnabled;

        return $this;
    }

    /**
     * Get newMatchEnabled
     *
     * @return boolean 
     */
    public function getNewMatchEnabled()
    {
        return $this->newMatchEnabled;
    }

    /**
     * Set scoreMatchEnabled
     *
     * @param boolean $scoreMatchEnabled
     * @return NotificationSubscription
     */
    public function setScoreMatchEnabled($scoreMatchEnabled)
    {
        $this->scoreMatchEnabled = $scoreMatchEnabled;

        return $this;
    }

    /**
     * Get scoreMatchEnabled
     *
     * @return boolean 
     */
    public function getScoreMatchEnabled()
    {
        return $this->scoreMatchEnabled;
    }

    /**
     * Set person
     *
     * @param \TS\ApiBundle\Entity\Person $person
     * @return NotificationSubscription
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
     * Set startMatchEnabled
     *
     * @param boolean $startMatchEnabled
     * @return NotificationSubscription
     */
    public function setStartMatchEnabled($startMatchEnabled)
    {
        $this->startMatchEnabled = $startMatchEnabled;

        return $this;
    }

    /**
     * Get startMatchEnabled
     *
     * @return boolean 
     */
    public function getStartMatchEnabled()
    {
        return $this->startMatchEnabled;
    }
}
