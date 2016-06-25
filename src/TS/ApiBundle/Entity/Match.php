<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Match
 *
 * @ORM\Table(name="Matchh")
 * @ORM\Entity(repositoryClass="TS\ApiBundle\Entity\MatchRepository")
 */
class Match
{
	const STATUS_POSTPONED = "postponed";
	const STATUS_READY = "ready";
	const STATUS_PLAYING = "playing";
	const STATUS_FINISHED = "finished";
	const STATUS_PLAYED = "played";
	
	
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $localId;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Pool", inversedBy="matches")
     */
    private $pool;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="matches")
     * @Assert\NotNull()
     */
    private $tournament;
    
    /**
     * @ORM\OneToOne(targetEntity="Location", inversedBy="match")
     */
    private $location;
    
    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="matches1")
     */
    private $team1;
    
    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="matches2")
     */
    private $team2;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $startTime;
    
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $round;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $priority;
    
    /**
     * @var array
     * 
     * @ORM\Column(type="array")
     */
    private $score;
    
    /**
     * @ORM\OneToMany(targetEntity="Announcement", mappedBy="match", cascade={"persist"})
     *
     */
    private $announcements;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nonreadyReason;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->round = "";
        $this->priority = false;
        $this->score = array();
        $this->announcements = new ArrayCollection();
        $this->status = self::STATUS_READY;
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
     * Set round
     *
     * @param string $round
     * @return Match
     */
    public function setRound($round)
    {
        $this->round = $round;
    
        return $this;
    }

    /**
     * Get round
     *
     * @return string 
     */
    public function getRound()
    {
        return $this->round;
    }


    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Match
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
     * Set team1
     *
     * @param \TS\ApiBundle\Entity\Team $team1
     * @return Match
     */
    public function setTeam1(\TS\ApiBundle\Entity\Team $team1 = null)
    {
        $this->team1 = $team1;
    
        return $this;
    }

    /**
     * Get team1
     *
     * @return \TS\ApiBundle\Entity\Team 
     */
    public function getTeam1()
    {
        return $this->team1;
    }

    /**
     * Set team2
     *
     * @param \TS\ApiBundle\Entity\Team $team2
     * @return Match
     */
    public function setTeam2(\TS\ApiBundle\Entity\Team $team2 = null)
    {
        $this->team2 = $team2;
    
        return $this;
    }

    /**
     * Get team2
     *
     * @return \TS\ApiBundle\Entity\Team 
     */
    public function getTeam2()
    {
        return $this->team2;
    }
    
    /**
     * Get team1 and team2 in an array
     *
     * @return array with two elements of \TS\ApiBundle\Entity\Team (which can be null)
     */
    public function getTeams()
    {
        $res = array();
        $res[1] = $this->team1;
        $res[2] = $this->team2;
        return $res;
    }

    /**
     * Set localId
     *
     * @param integer $localId
     * @return Match
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;
    
        return $this;
    }

    /**
     * Get localId
     *
     * @return integer 
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * Set location
     *
     * @param \TS\ApiBundle\Entity\Location $location
     * @return Match
     */
    public function setLocation(\TS\ApiBundle\Entity\Location $location = null)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return \TS\ApiBundle\Entity\Location 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime. If $startTime == "now", current time will be used
     * @return Match
     */
    public function setStartTime($startTime = "now")
    {
        if ($startTime == "now") {
        	$startTime = new \DateTime("now");
        }
        
        $this->startTime = $startTime;
    
        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime 
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set priority
     *
     * @param boolean $priority
     * @return Match
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    
        return $this;
    }

    /**
     * Get priority
     *
     * @return boolean 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set score
     *
     * @param array $score. Array with multiple elements for each set. That set/value is an array with two keys (1 and 2) with the scores of team1 and team2
     * @return Match
     */
    public function setScore($score)
    {
        $this->score = $score;
    
        return $this;
    }

    /**
     * Get score
     *
     * @return array 
     */
    public function getScore()
    {
        return $this->score;
    }
    
    // Get textual representation of score of sets
    public function getScoreTextual($showLongNotation=true) {
    	if (sizeof($this->score) == 0) {
    		return "";
    	} else if (!$showLongNotation) {
	    	// show totals, as in e.g. 1-1 or 2-0
	    	$team1 = 0;
	    	$team2 = 0;
	    	foreach ($this->score as $set=>$result) {
	    		if ($result[1] > $result[2]) {
	    			$team1++;
	    		} else {
	    			$team2++;
	    		}
	    	}
	    	return $team1 ."-". $team2;
	    } else {
	    	// show individual score, as in e.g. 21-5 9-21
	    	$res = "";
	    	$lastSet = sizeof($this->score) - 1;
	    	foreach ($this->score as $set=>$result) {
	    		$res .= $result[1] ."-". $result[2];
	    		if ($set != $lastSet) {
	    			$res .= " ";
	    		}
	    	}
	    	return $res;
	    }
    }
    
    
    

    /**
     * Add announcements
     *
     * @param \TS\ApiBundle\Entity\Announcement $announcements
     * @return Match
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
     * Set status
     *
     * @param string $status
     * @return Match
     */
    public function setStatus($status)
    {
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
     * Set nonreadyReason
     *
     * @param string $nonreadyReason
     * @return Match
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
     * Set pool
     *
     * @param \TS\ApiBundle\Entity\Pool $pool
     * @return Match
     */
    public function setPool(\TS\ApiBundle\Entity\Pool $pool = null)
    {
        $this->pool = $pool;

        return $this;
    }

    /**
     * Get pool
     *
     * @return \TS\ApiBundle\Entity\Pool
     */
    public function getPool()
    {
        return $this->pool;
    }
}
