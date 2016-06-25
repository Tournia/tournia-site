<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Location
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class Location
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="locations")
     * @Assert\NotNull()
     * @Gedmo\SortableGroup
     */
    private $tournament;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $onHold;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nonreadyReason;
    
    /**
     * @ORM\OneToOne(targetEntity="Match", mappedBy="location")
     */
    private $match;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->onHold = false;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->tournament = null;
            $this->match = null;
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
     * @return Location
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
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Location
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
     * Set onHold
     *
     * @param boolean $onHold
     * @return Location
     */
    public function setOnHold($onHold)
    {
        $this->onHold = $onHold;
    
        return $this;
    }

    /**
     * Get onHold
     *
     * @return boolean 
     */
    public function getOnHold()
    {
        return $this->onHold;
    }

    /**
     * Set match
     *
     * @param \TS\ApiBundle\Entity\Match $match
     * @return Location
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
     * Set position
     *
     * @param integer $position
     * @return Location
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set nonreadyReason
     *
     * @param string $nonreadyReason
     * @return Location
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
}
