<?php
namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class RegistrationFormField
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
     /**
     * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="registrationFormFields")
     * @Assert\NotNull()
     * @Gedmo\SortableGroup
     */
    private $tournament;
    
    /**
     * @ORM\OneToMany(targetEntity="RegistrationFormValue", mappedBy="field")
     */
    private $values;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Enter a name")
     */
    private $name;
    
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $infoText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $formComment;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $isRequired;
    
    /**
     * @ORM\Column(type="string")
     */
    private $type;
    
    /**
     * @var array
     * 
     * @ORM\Column(type="array")
     */
    private $choiceOptions;
    
    /**
     * @ORM\Column(type="boolean")
     */
    /* private $choiceMultiple; // value currently only supports text (string) answers, not array */
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $choiceExpanded;

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
     * Constructor
     */
    public function __construct()
    {
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
        $this->choiceOptions = array();
        $this->choiceExpanded = false;
        $this->isHidden = false;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->tournament = null;
            $this->values = new ArrayCollection();
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
     * @return RegistrationFormField
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
     * Set infoText
     *
     * @param string $infoText
     * @return RegistrationFormField
     */
    public function setInfoText($infoText)
    {
        $this->infoText = $infoText;
    
        return $this;
    }

    /**
     * Get infoText
     *
     * @return string 
     */
    public function getInfoText()
    {
        return $this->infoText;
    }

    /**
     * Set isRequired
     *
     * @param boolean $isRequired
     * @return RegistrationFormField
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    
        return $this;
    }

    /**
     * Get isRequired
     *
     * @return boolean 
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return RegistrationFormField
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
     * Set choiceOptions
     *
     * @param array $choiceOptions
     * @return RegistrationFormField
     */
    public function setChoiceOptions($choiceOptions)
    {
        $this->choiceOptions = $choiceOptions;
    
        return $this;
    }

    /**
     * Get choiceOptions
     *
     * @return array 
     */
    public function getChoiceOptions()
    {
        return $this->choiceOptions;
    }

    /**
     * Set choiceExpanded
     *
     * @param boolean $choiceExpanded
     * @return RegistrationFormField
     */
    public function setChoiceExpanded($choiceExpanded)
    {
        $this->choiceExpanded = $choiceExpanded;
    
        return $this;
    }

    /**
     * Get choiceExpanded
     *
     * @return boolean 
     */
    public function getChoiceExpanded()
    {
        return $this->choiceExpanded === true;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return RegistrationFormField
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
     * Add values
     *
     * @param \TS\ApiBundle\Entity\RegistrationFormValue $values
     * @return RegistrationFormField
     */
    public function addValue(\TS\ApiBundle\Entity\RegistrationFormValue $values)
    {
        $this->values[] = $values;
    
        return $this;
    }

    /**
     * Remove values
     *
     * @param \TS\ApiBundle\Entity\RegistrationFormValue $values
     */
    public function removeValue(\TS\ApiBundle\Entity\RegistrationFormValue $values)
    {
        $this->values->removeElement($values);
    }

    /**
     * Get values
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set formComment
     *
     * @param string $formComment
     * @return RegistrationFormField
     */
    public function setFormComment($formComment)
    {
        $this->formComment = $formComment;

        return $this;
    }

    /**
     * Get formComment
     *
     * @return string 
     */
    public function getFormComment()
    {
        return $this->formComment;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return RegistrationFormField
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
     * @return RegistrationFormField
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
}
