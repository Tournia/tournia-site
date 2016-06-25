<?php
namespace TS\FinancialBundle\Entity;

use Sylius\Bundle\CartBundle\Model\CartItem as BaseCartItem;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\CartBundle\Model\CartInterface;
use Sylius\Bundle\OrderBundle\Model\OrderItemInterface;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class CartItem extends BaseCartItem
{
    
	/**
     * @ORM\ManyToOne(targetEntity="\TS\FinancialBundle\Entity\Product")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Player")
     */
    protected $player;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $adjustments;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set product
     *
     * @param \TS\FinancialBundle\Entity\Product $product
     * @return CartItem
     */
    public function setProduct(\TS\FinancialBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \TS\FinancialBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }


    /**
      * Compare cart items to each other
      * If the added item is equal to an existing one, their quantities are summed, but no new item is added to the cart. 
      */
    public function equals(OrderItemInterface $cartItem)
    {
        return $this->product === $cartItem->getProduct() && $this->player == $cartItem->getPlayer();
    }


    /**
     * Set player
     *
     * @param \TS\ApiBundle\Entity\Player $player
     * @return CartItem
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
     * Add adjustments
     *
     * @param \Sylius\Bundle\OrderBundle\Model\Adjustment $adjustments
     * @return CartItem
     */
    public function addAdjustment(\Sylius\Bundle\OrderBundle\Model\AdjustmentInterface $adjustments)
    {
        $this->adjustments[] = $adjustments;

        return $this;
    }

    /**
     * Remove adjustments
     *
     * @param \Sylius\Bundle\OrderBundle\Model\Adjustment $adjustments
     */
    public function removeAdjustment(\Sylius\Bundle\OrderBundle\Model\AdjustmentInterface $adjustments)
    {
        $this->adjustments->removeElement($adjustments);
    }

    /**
     * Get adjustments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }
}
