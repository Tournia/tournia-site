<?php
namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class DisciplinePlayer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="disciplinePlayers")
     * @Assert\NotNull()
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="Discipline", inversedBy="players")
     * @Assert\NotNull()
     */
    private $discipline;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $partner;


    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __clone()
    {
        if ($this->id) {
            $this->player = null;
            $this->discipline = null;
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
     * Set partner
     *
     * @param string $partner
     * @return DisciplinePlayer
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return string 
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * Set player
     *
     * @param \TS\ApiBundle\Entity\Player $player
     * @return DisciplinePlayer
     */
    public function setPlayer(\TS\ApiBundle\Entity\Player $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \TS\ApiBundle\Entity\Player 
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Set discipline
     *
     * @param \TS\ApiBundle\Entity\Discipline $discipline
     * @return DisciplinePlayer
     */
    public function setDiscipline(\TS\ApiBundle\Entity\Discipline $discipline = null)
    {
        $this->discipline = $discipline;

        return $this;
    }

    /**
     * Get discipline
     *
     * @return \TS\ApiBundle\Entity\Discipline 
     */
    public function getDiscipline()
    {
        return $this->discipline;
    }
}
