<?php
namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class RegistrationFormValue
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="registrationFormValues")
     * @Assert\NotNull()
     */
    private $player;
    
   /**
     * @ORM\ManyToOne(targetEntity="RegistrationFormField", inversedBy="values")
     * @Assert\NotNull()
     */
    private $field;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $value;



    public function __clone()
    {
        if ($this->id) {
            $this->player = null;
            $this->field = null;
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
     * Set value
     *
     * @param string $value
     * @return RegistrationFormValue
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set player
     *
     * @param \TS\ApiBundle\Entity\Player $player
     * @return RegistrationFormValue
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
     * Set field
     *
     * @param \TS\ApiBundle\Entity\RegistrationFormField $field
     * @return RegistrationFormValue
     */
    public function setField(\TS\ApiBundle\Entity\RegistrationFormField $field = null)
    {
        $this->field = $field;
    
        return $this;
    }

    /**
     * Get field
     *
     * @return \TS\ApiBundle\Entity\RegistrationFormField 
     */
    public function getField()
    {
        return $this->field;
    }
}