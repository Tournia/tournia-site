<?php
namespace TS\FinancialBundle\Entity;

use Sylius\Bundle\ProductBundle\Model\ProductInterface;
use Sylius\Bundle\ProductBundle\Model\Product as BaseProduct;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class Product extends BaseProduct
{
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="products")
     * @Gedmo\SortableGroup
     */
    protected $tournament;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $price;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $properties;

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
     * @ORM\Column(type="boolean")
     */
    private $initiallySelected;
    

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->isHidden = false;
        $this->initiallySelected = true;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->tournament = null;
            $this->properties = new ArrayCollection();
        }
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Product
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
     * Set price
     *
     * @param integer $price
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer 
     */
    public function getPrice()
    {
        return $this->price;
    }


    /**
     * Add properties
     *
     * @param \Sylius\Bundle\ProductBundle\Model\ProductProperty $properties
     * @return Product
     */
    public function addProperty(\Sylius\Bundle\ProductBundle\Model\ProductPropertyInterface $properties)
    {
        $this->properties[] = $properties;

        return $this;
    }

    /**
     * Remove properties
     *
     * @param \Sylius\Bundle\ProductBundle\Model\ProductProperty $properties
     */
    public function removeProperty(\Sylius\Bundle\ProductBundle\Model\ProductPropertyInterface $properties)
    {
        $this->properties->removeElement($properties);
    }

    /**
     * Get properties
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Product
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
     * @return Product
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
     * Set initiallySelected
     *
     * @param boolean $initiallySelected
     * @return Product
     */
    public function setInitiallySelected($initiallySelected)
    {
        $this->initiallySelected = $initiallySelected;

        return $this;
    }

    /**
     * Get initiallySelected
     *
     * @return boolean 
     */
    public function getInitiallySelected()
    {
        return $this->initiallySelected;
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        $this->slug = $this->tournament->getId() . $slug;

        return $this;
    }
}
