<?php
namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class Discipline
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
     /**
     * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="disciplines")
     * @Assert\NotNull()
     * @Gedmo\SortableGroup
     */
    private $tournament;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;
    
     /**
     * @var string
     *
     * @ORM\Column(type="string", length=1)
     * @Assert\NotBlank(message = "Choose a gender")
     * @Assert\Choice(choices = {"M", "F", "B"}, message = "Choose a valid gender")
     */
    private $gender;

    /**
     * @ORM\ManyToOne(targetEntity="DisciplineType", inversedBy="disciplines")
     * @Assert\NotNull()
     */
    private $disciplineType;

    /**
     * @ORM\OneToMany(targetEntity="DisciplinePlayer", mappedBy="discipline", cascade={"persist"})
     */
    private $players;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isHidden;

    /**
     * @ORM\ManyToMany(targetEntity="Pool", mappedBy="inputDisciplines")
     */
    private $pools;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registrationPlayers = new ArrayCollection();
        $this->isHidden = false;
        $this->pools = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Discipline
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
     * Set gender
     *
     * @param string $gender
     * @return Discipline
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
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Discipline
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
     * Set position
     *
     * @param integer $position
     * @return Discipline
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
     * Set isHidden
     *
     * @param boolean $isHidden
     * @return Discipline
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * Get isHidden
     *
     * @return boolean 
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * Add players
     *
     * @param \TS\ApiBundle\Entity\DisciplinePlayer $players
     * @return Discipline
     */
    public function addPlayer(\TS\ApiBundle\Entity\DisciplinePlayer $players)
    {
        $this->players[] = $players;

        return $this;
    }

    /**
     * Remove players
     *
     * @param \TS\ApiBundle\Entity\DisciplinePlayer $players
     */
    public function removePlayer(\TS\ApiBundle\Entity\DisciplinePlayer $players)
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
     * Set disciplineType
     *
     * @param \TS\ApiBundle\Entity\DisciplineType $disciplineType
     * @return Discipline
     */
    public function setDisciplineType(\TS\ApiBundle\Entity\DisciplineType $disciplineType = null)
    {
        $this->disciplineType = $disciplineType;

        return $this;
    }

    /**
     * Get disciplineType
     *
     * @return \TS\ApiBundle\Entity\DisciplineType 
     */
    public function getDisciplineType()
    {
        return $this->disciplineType;
    }

    /**
     * Add pools
     *
     * @param \TS\ApiBundle\Entity\Pool $pools
     * @return Discipline
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
}
