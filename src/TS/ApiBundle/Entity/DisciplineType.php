<?php
namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class DisciplineType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="disciplineTypes")
     * @Assert\NotNull()
     * @Gedmo\SortableGroup
     */
    private $tournament;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Discipline", mappedBy="disciplineType", cascade={"persist"})
     */
    private $disciplines;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $partnerRegistration;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->disciplines = new ArrayCollection();
        $this->partnerRegistration = false;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->tournament = null;
            $this->disciplines = new ArrayCollection();
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
     * @return DisciplineType
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
     * @return DisciplineType
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
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return DisciplineType
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
     * Add disciplines
     *
     * @param \TS\ApiBundle\Entity\Discipline $disciplines
     * @return DisciplineType
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
     * Set partnerRegistration
     *
     * @param boolean $partnerRegistration
     * @return Tournament
     */
    public function setPartnerRegistration($partnerRegistration)
    {
        $this->partnerRegistration = $partnerRegistration;

        return $this;
    }

    /**
     * Get partnerRegistration
     *
     * @return boolean
     */
    public function getPartnerRegistration()
    {
        return $this->partnerRegistration;
    }
}
