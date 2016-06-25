<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\NoResultException;

/**
 * Team
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\ApiBundle\Entity\TeamRepository")
 */
class Team
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Pool", inversedBy="teams")
     * @Assert\NotNull()
     */
    private $pool;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament")
     * @ORM\JoinColumn(name="tournament", referencedColumnName="id")
     */
    private $tournament;
    
    /**
     * @ORM\ManyToMany(targetEntity="\TS\ApiBundle\Entity\Player", inversedBy="teams", cascade={"persist"})
     *
     */
    private $players;
    
    /**
     * link between players and position of player in team. Key is the position and value an array. This values array is built up with key==0 for original player and possibly key==1 for replacement player, which refers to a playerId
     * @ORM\Column(type="array")
     */
    private $playersPositionArray;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $givenUp;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nonreadyReason;
    
    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="team1")
     */
    private $matches1;
    
    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="team2")
     */
    private $matches2;
    
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->playersPositionArray = array();
        $this->players = new ArrayCollection();
        $this->givenUp = false;
        $this->matches1 = new ArrayCollection();
        $this->matches2 = new ArrayCollection();
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
     * @return Team
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
        $res = "";
        if (is_null($this->name)) {
        	// TODO: generate name
        	$players = $this->getPlayersForAllPositions();
            if (sizeof($players) == 0) {
                $res = "-";
            } else {
                foreach ($players as $player) {
                    $res .= $player->getName() ." & ";
                }
                $res = substr($res, 0, -3);
            }
        } else {
        	$res = $this->name;
        }
        return $res;
    }
    
    
    /**
     * Set a Player for certain position
     * When overwriting an existing player, the replacement player will be removed
     * @param int $position The index of the player
     * @param \TS\ApiBundle\Entity\Player The player. If player is null, the player (and possibly replacement player) will be removed.
     */
    public function setPlayerForPosition($position, $player)
    {
        if (is_null($player)) {
        	// remove player
        	if (array_key_exists($position, $this->playersPositionArray)) {
        		$this->setReplacementPlayerForPosition($position, null); // also remove replacement player
        		$oldPlayerId = $this->playersPositionArray[$position][0];
        		unset($this->playersPositionArray[$position]);
        		// check if there are not more of this player in this team -> if so, remove player
        		$this->cleanupPlayerReference($oldPlayerId);
        	}
        } else {
        	// set player
        	if (array_key_exists($position, $this->playersPositionArray)) {
        		// overwrite existing player
        		$this->setReplacementPlayerForPosition($position, null); // also remove replacement player
        		$oldPlayerId = $this->playersPositionArray[$position][0];
        		// first remove existing player
        		$this->playersPositionArray[$position] = array();
        		$this->cleanupPlayerReference($oldPlayerId);
        	} else {
        		$this->playersPositionArray[$position] = array();
        	}
    		// now set new player
    		if (!$this->players->contains($player)) {
    			$this->addPlayer($player);
    		}
    		$this->playersPositionArray[$position][0] = $player->getId();
        }
        
        return $this;
    }
    
    // remove player object from $players if there are no more references to it in playersPositionArray
    private function cleanupPlayerReference($oldPlayerId) {
    	$foundPlayer = false;
    	
    	foreach ($this->playersPositionArray as $position=>$positionArray) {
    		foreach ($positionArray as $playerId) {
    			if ($oldPlayerId == $playerId) {
    				$foundPlayer = true;
    			}
    		}
    	}
    	
    	if ($foundPlayer == false) {
    		// remove player
    		foreach ($this->players as $player) {
        		if ($player->getId() == $oldPlayerId) {
        			$this->removePlayer($player);
        		}
        	}
    	}
    }
    
    /**
     * Set replacement playerId for certain position
     *
     * @param int $position The index of the player. The index must exist in normal(non-replacement) player to be able to set a replacement player
     * @param \TS\ApiBundle\Entity\Player The player. If player is null, the replacement player will be removed.
     */
    public function setReplacementPlayerForPosition($position, $player) {
        if (is_null($player)) {
        	// remove player
        	if (array_key_exists($position, $this->playersPositionArray) && (sizeof($this->playersPositionArray[$position]) == 2)) {
        		$oldPlayerId = $this->playersPositionArray[$position][1];
        		unset($this->playersPositionArray[$position][1]);
        		// check if there are not more of this player in this team -> if so, remove player
        		$this->cleanupPlayerReference($oldPlayerId);
        	}
        } else {
        	// set player
        	if (array_key_exists($position, $this->playersPositionArray)) {
        		// add new player, but only if position exists in normal players
        		if (sizeof($this->playersPositionArray[$position]) == 2) {
	        		// overwrite existing replacement player
	        		$oldPlayerId = $this->playersPositionArray[$position][1];
	        		// first remove existing player
	        		unset($this->playersPositionArray[$position][1]);
	        		$this->cleanupPlayerReference($oldPlayerId);
	        	}
	    		// now set new replacement player
	    		if (!$this->players->contains($player)) {
	    			$this->addPlayer($player);
	    		}
	    		$this->playersPositionArray[$position][1] = $player->getId();
	        }
        }
        
        return $this;
    }
    
    /**
      * Check whether a replacment player is set for a certain position
      * @param int $position The index of player in team
      * @return boolean Whether a replacement player is set
      */
    public function hasReplacementPlayerForPosition($position) {
    	return (array_key_exists($position, $this->playersPositionArray) && (sizeof($this->playersPositionArray[$position]) == 2));
    }
    
    /**
     * Get Player for a certain position
     *
     * @param int $position The index of the player
     * @param boolean $returnOriginalPlayer Whether to return the original player if there is a replacement player set
     * @return \TS\ApiBundle\Entity\Player The player. If position is not set, null will be returned.
     */
    public function getPlayerForPosition($position, $returnOriginalPlayer = false)
    {
        if (!array_key_exists($position, $this->playersPositionArray)) {
        	return null;
        } else if (!$returnOriginalPlayer && $this->hasReplacementPlayerForPosition($position)) {
        	// replacement player set, and should be returned
        	$playerId = $this->playersPositionArray[$position][1];
        	foreach ($this->players as $player) {
        		if ($player->getId() == $playerId) {
        			return $player;
        		}
        	}
        } else {
        	// returning original player
        	$playerId = $this->playersPositionArray[$position][0];
        	foreach ($this->players as $player) {
        		if ($player->getId() == $playerId) {
        			return $player;
        		}
        	}
        }
    }
    
    /**
     * Get all Players in this Team
     *
     * @param boolean $returnOriginalPlayers Whether to return original players if there is a replacement player set
     * @return array The key is the position and value the Player
     */
    public function getPlayersForAllPositions($returnOriginalPlayers = false)
    {
        $res = array();
        
        foreach ($this->playersPositionArray as $position=>$positionArray) {
        	if (!$returnOriginalPlayers && (sizeof($positionArray) == 2)) {
        		// return replacement player
        		$playerId = $positionArray[1];
        	} else {
        		// return normal player
        		$playerId = $positionArray[0];
        	}
        	foreach ($this->players as $player) {
        		if ($player->getId() == $playerId) {
        			$res[$position] = $player;
        		}
        	}
        }
        
        return $res;
    }
    
	

    /**
     * Add players
     *
     * @param \TS\ApiBundle\Entity\Player $players
     * @return Team
     */
    private function addPlayer(\TS\ApiBundle\Entity\Player $players)
    {
        $this->players[] = $players;
    
        return $this;
    }

    /**
     * Remove players
     *
     * @param \TS\ApiBundle\Entity\Player $players
     */
    private function removePlayer(\TS\ApiBundle\Entity\Player $players)
    {
        $this->players->removeElement($players);
    }

    /**
     * Get all players (without position information)
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlayers()
    {
        return $this->players;
    }
    
    /**
     * Set givenUp
     *
     * @param boolean $givenUp
     * @return Team
     */
    public function setGivenUp($givenUp)
    {
        $this->givenUp = $givenUp;
    
        return $this;
    }

    /**
     * Get givenUp
     *
     * @return boolean 
     */
    public function getGivenUp()
    {
        return $this->givenUp;
    }

    /**
     * Add matches1
     *
     * @param \TS\ApiBundle\Entity\Match $matches1
     * @return Team
     */
    public function addMatches1(\TS\ApiBundle\Entity\Match $matches1)
    {
        $this->matches1[] = $matches1;
    
        return $this;
    }

    /**
     * Remove matches1
     *
     * @param \TS\ApiBundle\Entity\Match $matches1
     */
    public function removeMatches1(\TS\ApiBundle\Entity\Match $matches1)
    {
        $this->matches1->removeElement($matches1);
    }

    /**
     * Get matches1
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMatches1()
    {
        return $this->matches1;
    }

    /**
     * Add matches2
     *
     * @param \TS\ApiBundle\Entity\Match $matches2
     * @return Team
     */
    public function addMatches2(\TS\ApiBundle\Entity\Match $matches2)
    {
        $this->matches2[] = $matches2;
    
        return $this;
    }

    /**
     * Remove matches2
     *
     * @param \TS\ApiBundle\Entity\Match $matches2
     */
    public function removeMatches2(\TS\ApiBundle\Entity\Match $matches2)
    {
        $this->matches2->removeElement($matches2);
    }

    /**
     * Get matches2
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMatches2()
    {
        return $this->matches2;
    }
    
    /**
      * Get all matches of matches1 and matches2 combined
      * @return array
      */
    public function getMatches() {
    	$res = array();
    	foreach ($this->matches1 as $match) {
    		$res[] = $match;
    	}
    	foreach ($this->matches2 as $match) {
    		$res[] = $match;
    	}
    	return $res;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Team
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
     * Set playersPositionArray
     *
     * @param array $playersPositionArray
     * @return Team
     */
    public function setPlayersPositionArray($playersPositionArray)
    {
        $this->playersPositionArray = $playersPositionArray;
    
        return $this;
    }

    /**
     * Get playersPositionArray
     *
     * @return array 
     */
    public function getPlayersPositionArray()
    {
        return $this->playersPositionArray;
    }

    /**
     * Set nonreadyReason
     *
     * @param string $nonreadyReason
     * @return Team
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
     * @return Team
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
