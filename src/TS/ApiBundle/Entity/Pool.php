<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Pool
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class Pool
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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="pools")
     * @Assert\NotNull()
     * @Gedmo\SortableGroup
     */
    private $tournament;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * Algorithm of discipline. Can be $ALGORITHM_SWISS_LADDER or $ALGORITHM_ROUND_ROBIN
     * @var string
     *
     * @ORM\Column(type="string", length=16, nullable=false)
     */
    protected $algorithm;
    public static $ALGORITHM_SWISSLADDER = "swissladder";
    public static $ALGORITHM_ROUNDROBIN = "roundrobin";

    /**
     * @ORM\ManyToMany(targetEntity="Discipline", inversedBy="pools", cascade={"persist"})
     */
    private $inputDisciplines;

    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\Team", mappedBy="pool", cascade={"persist"})
     */
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity="\TS\ApiBundle\Entity\Match", mappedBy="pool")
     */
    private $matches;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(value = 1, message = "The minimum number of players in a team is 1")
     */
    private $nrPlayersInTeam;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->algorithm = self::$ALGORITHM_SWISSLADDER;
        $this->inputDisciplines = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->nrPlayersInTeam = 1;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->tournament = null;
            $this->inputDisciplines = new ArrayCollection();
            $this->teams = new ArrayCollection();
            $this->matches = new ArrayCollection();
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
     * @return Pool
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
     * Set position
     *
     * @param integer $position
     * @return Pool
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
     * Set algorithm
     *
     * @param string $algorithm
     * @return Pool
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * Get algorithm
     *
     * @return string 
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Pool
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
     * Add inputDisciplines
     *
     * @param \TS\ApiBundle\Entity\Discipline $inputDisciplines
     * @return Pool
     */
    public function addInputDiscipline(\TS\ApiBundle\Entity\Discipline $inputDisciplines)
    {
        $this->inputDisciplines[] = $inputDisciplines;

        return $this;
    }

    /**
     * Remove inputDisciplines
     *
     * @param \TS\ApiBundle\Entity\Discipline $inputDisciplines
     */
    public function removeInputDiscipline(\TS\ApiBundle\Entity\Discipline $inputDisciplines)
    {
        $this->inputDisciplines->removeElement($inputDisciplines);
    }

    /**
     * Get inputDisciplines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInputDisciplines()
    {
        return $this->inputDisciplines;
    }

    /**
     * Add teams
     *
     * @param \TS\ApiBundle\Entity\Team $teams
     * @return Pool
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
     * Add matches
     *
     * @param \TS\ApiBundle\Entity\Match $matches
     * @return Pool
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
     * Set nrPlayersInTeam
     *
     * @param integer $nrPlayersInTeam
     * @return Pool
     */
    public function setNrPlayersInTeam($nrPlayersInTeam)
    {
        $this->nrPlayersInTeam = $nrPlayersInTeam;

        return $this;
    }

    /**
     * Get nrPlayersInTeam
     *
     * @return integer
     */
    public function getNrPlayersInTeam()
    {
        return $this->nrPlayersInTeam;
    }
}
