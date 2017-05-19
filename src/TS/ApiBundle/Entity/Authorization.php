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
 * @ORM\Entity()
 */
class Authorization
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
     * @ORM\OneToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", mappedBy="authorization")
     */
    private $tournament;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $createRegistrationChoice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createRegistrationStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createRegistrationEnd;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $changeRegistrationChoice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $changeRegistrationStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $changeRegistrationEnd;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $apiChoice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $apiStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $apiEnd;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $liveScoreChoice;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $live2ndCallChoice;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $livePassword;




    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createRegistrationChoice = 1;
        $this->changeRegistrationChoice = 1;
        $this->apiChoice = 1;
        $this->liveScoreChoice = 1;
        $this->live2ndCallChoice = 1;
        $this->livePassword = '';
    }

    public function __clone()
    {
        if ($this->id) {
            $this->tournament = null;
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
     * Check if authorization is given, based on allowed value and current date
     *
     * @return boolean
     */
    private function checkAuth($allowed, $start, $end) {
        $res = $allowed == 1;
        if ($allowed == -1) {
            // date specific
            $todayDate = new \DateTime("now");
            $res = (is_null($start) || $start <= $todayDate) &&
                (is_null($end) || $end >= $todayDate);
        }

        return $res;
    }

    /**
     * Is createRegistration allowed, based on allowed value and current date
     *
     * @return boolean
     */
    public function isCreateRegistrationAllowed()
    {
        return $this->checkAuth($this->createRegistrationChoice, $this->createRegistrationStart, $this->createRegistrationEnd);
    }

    /**
     * Set createRegistrationChoice
     *
     * @param integer $createRegistrationChoice
     * @return Authorization
     */
    public function setCreateRegistrationChoice($createRegistrationChoice)
    {
        $this->createRegistrationChoice = $createRegistrationChoice;

        return $this;
    }

    /**
     * Get createRegistrationChoice
     *
     * @return integer
     */
    public function getCreateRegistrationChoice()
    {
        return $this->createRegistrationChoice;
    }

    /**
     * Set createRegistrationStart
     *
     * @param \DateTime $createRegistrationStart
     * @return Authorization
     */
    public function setCreateRegistrationStart($createRegistrationStart)
    {
        $this->createRegistrationStart = $createRegistrationStart;

        return $this;
    }

    /**
     * Get createRegistrationStart
     *
     * @return \DateTime 
     */
    public function getCreateRegistrationStart()
    {
        return $this->createRegistrationStart;
    }

    /**
     * Set createRegistrationEnd
     *
     * @param \DateTime $createRegistrationEnd
     * @return Authorization
     */
    public function setCreateRegistrationEnd($createRegistrationEnd)
    {
        $this->createRegistrationEnd = $createRegistrationEnd;

        return $this;
    }

    /**
     * Get createRegistrationEnd
     *
     * @return \DateTime 
     */
    public function getCreateRegistrationEnd()
    {
        return $this->createRegistrationEnd;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Authorization
     */
    public function setTournament(\TS\ApiBundle\Entity\Tournament $tournament)
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
     * Is changeRegistration allowed, based on allowed value and current date
     *
     * @return boolean
     */
    public function isChangeRegistrationAllowed()
    {
        return $this->checkAuth($this->changeRegistrationChoice, $this->changeRegistrationStart, $this->changeRegistrationEnd);
    }

    /**
     * Set changeRegistrationChoice
     *
     * @param integer $changeRegistrationChoice
     * @return Authorization
     */
    public function setChangeRegistrationChoice($changeRegistrationChoice)
    {
        $this->changeRegistrationChoice = $changeRegistrationChoice;

        return $this;
    }

    /**
     * Get changeRegistrationChoice
     *
     * @return integer
     */
    public function getChangeRegistrationChoice()
    {
        return $this->changeRegistrationChoice;
    }

    /**
     * Set changeRegistrationStart
     *
     * @param \DateTime $changeRegistrationStart
     * @return Authorization
     */
    public function setChangeRegistrationStart($changeRegistrationStart)
    {
        $this->changeRegistrationStart = $changeRegistrationStart;

        return $this;
    }

    /**
     * Get changeRegistrationStart
     *
     * @return \DateTime 
     */
    public function getChangeRegistrationStart()
    {
        return $this->changeRegistrationStart;
    }

    /**
     * Set changeRegistrationEnd
     *
     * @param \DateTime $changeRegistrationEnd
     * @return Authorization
     */
    public function setChangeRegistrationEnd($changeRegistrationEnd)
    {
        $this->changeRegistrationEnd = $changeRegistrationEnd;

        return $this;
    }

    /**
     * Get changeRegistrationEnd
     *
     * @return \DateTime 
     */
    public function getChangeRegistrationEnd()
    {
        return $this->changeRegistrationEnd;
    }

    /**
     * Is api allowed, based on allowed value and current date
     *
     * @return boolean
     */
    public function isApiAllowed()
    {
        return $this->checkAuth($this->apiChoice, $this->apiStart, $this->apiEnd);
    }

    /**
     * Set apiChoice
     *
     * @param integer $apiChoice
     * @return Authorization
     */
    public function setApiChoice($apiChoice)
    {
        $this->apiChoice = $apiChoice;

        return $this;
    }

    /**
     * Get apiChoice
     *
     * @return integer
     */
    public function getApiChoice()
    {
        return $this->apiChoice;
    }

    /**
     * Set apiStart
     *
     * @param \DateTime $apiStart
     * @return Authorization
     */
    public function setApiStart($apiStart)
    {
        $this->apiStart = $apiStart;

        return $this;
    }

    /**
     * Get apiStart
     *
     * @return \DateTime 
     */
    public function getApiStart()
    {
        return $this->apiStart;
    }

    /**
     * Set apiEnd
     *
     * @param \DateTime $apiEnd
     * @return Authorization
     */
    public function setApiEnd($apiEnd)
    {
        $this->apiEnd = $apiEnd;

        return $this;
    }

    /**
     * Get apiEnd
     *
     * @return \DateTime 
     */
    public function getApiEnd()
    {
        return $this->apiEnd;
    }

    /**
     * Is liveScore allowed, based on allowed value
     *
     * @return boolean
     */
    public function isLiveScoreAllowed()
    {
        return $this->liveScoreChoice == 1;
    }

    /**
     * Set liveScoreChoice
     *
     * @param integer $liveScoreChoice
     * @return Authorization
     */
    public function setLiveScoreChoice($liveScoreChoice)
    {
        $this->liveScoreChoice = $liveScoreChoice;

        return $this;
    }

    /**
     * Get liveScoreChoice
     *
     * @return integer
     */
    public function getLiveScoreChoice()
    {
        return $this->liveScoreChoice;
    }

    /**
     * Is live2ndCall allowed, based on allowed value
     *
     * @return boolean
     */
    public function isLive2ndCallAllowed()
    {
        return $this->live2ndCallChoice == 1;
    }

    /**
     * Set live2ndCallChoice
     *
     * @param integer $live2ndCallChoice
     * @return Authorization
     */
    public function setLive2ndCallChoice($live2ndCallChoice)
    {
        $this->live2ndCallChoice = $live2ndCallChoice;

        return $this;
    }

    /**
     * Get live2ndCallChoice
     *
     * @return integer
     */
    public function getLive2ndCallChoice()
    {
        return $this->live2ndCallChoice;
    }

    /**
     * Set livePassword
     *
     * @param string $livePassword
     * @return Site
     */
    public function setLivePassword($livePassword)
    {
        $this->livePassword = $livePassword;

        return $this;
    }

    /**
     * Get livePassword
     *
     * @return string
     */
    public function getLivePassword()
    {
        return $this->livePassword;
    }
}
