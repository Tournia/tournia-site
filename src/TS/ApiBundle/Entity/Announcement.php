<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Announcement
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class Announcement
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
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="announcements")
     * @Assert\NotNull()
     */
    private $tournament;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $type;
    
    /**
     * @ORM\ManyToOne(targetEntity="Match", inversedBy="announcements")
     */
    private $match;
    
    /**
     * @var array
     * 
     * @ORM\Column(type="array")
     */
    private $playerIds;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTime;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->playerIds = array();
        $this->dateTime = new \DateTime("now");
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
     * Set type
     *
     * @param string $type
     * @return Announcement
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set playerIds
     *
     * @param array $playerIds
     * @return Announcement
     */
    public function setPlayerIds($playerIds)
    {
        $this->playerIds = $playerIds;
    
        return $this;
    }

    /**
     * Get playerIds
     *
     * @return array 
     */
    public function getPlayerIds()
    {
        return $this->playerIds;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Announcement
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
     * Set match
     *
     * @param \TS\ApiBundle\Entity\Match $match
     * @return Announcement
     */
    public function setMatch(\TS\ApiBundle\Entity\Match $match = null)
    {
        $this->match = $match;
    
        return $this;
    }

    /**
     * Get match
     *
     * @return \TS\ApiBundle\Entity\Match 
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return Announcement
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    
        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime 
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}