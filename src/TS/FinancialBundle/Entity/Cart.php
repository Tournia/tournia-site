<?php
namespace TS\FinancialBundle\Entity;

use Sylius\Bundle\CartBundle\Model\Cart as BaseCart;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Cart extends BaseCart
{
    
	/**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Person", inversedBy="carts")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $execPerson;

    /**
     * @ORM\OneToOne(targetEntity="\JMS\Payment\CoreBundle\Entity\PaymentInstruction")
     */
    protected $paymentInstruction;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $items;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $adjustments;

    /**
     * @ORM\OneToMany(targetEntity="\TS\FinancialBundle\Entity\BoughtProduct", mappedBy="cartOrder", cascade={"persist"})
     *
     */
    private $boughtProducts;

    /**
     * @ORM\OneToOne(targetEntity="\TS\FinancialBundle\Entity\Invoice", mappedBy="cartOrder", cascade={"persist"})
     */
    private $invoice;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->adjustments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set paymentInstruction
     *
     * @param \JMS\Payment\CoreBundle\Entity\PaymentInstruction $paymentInstruction
     * @return Cart
     */
    public function setPaymentInstruction(\JMS\Payment\CoreBundle\Entity\PaymentInstruction $paymentInstruction = null)
    {
        $this->paymentInstruction = $paymentInstruction;

        return $this;
    }

    /**
     * Get paymentInstruction
     *
     * @return \JMS\Payment\CoreBundle\Entity\PaymentInstruction 
     */
    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    /**
     * Add items
     *
     * @param \TS\FinancialBundle\Entity\CartItem $items
     * @return Cart
     */
    public function addItem(\Sylius\Bundle\OrderBundle\Model\OrderItemInterface $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * Remove items
     *
     * @param \TS\FinancialBundle\Entity\CartItem $items
     */
    public function removeItem(\Sylius\Bundle\OrderBundle\Model\OrderItemInterface $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add adjustments
     *
     * @param \Sylius\Bundle\OrderBundle\Model\Adjustment $adjustments
     * @return Cart
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

    /**
     * Set executing person
     *
     * @param \TS\ApiBundle\Entity\Person $execPerson
     * @return Cart
     */
    public function setExecPerson(\TS\ApiBundle\Entity\Person $execPerson = null)
    {
        $this->execPerson = $execPerson;

        return $this;
    }

    /**
     * Get executing person
     *
     * @return \TS\ApiBundle\Entity\Person 
     */
    public function getExecPerson()
    {
        return $this->execPerson;
    }

    /**
     * Add boughtProducts
     *
     * @param \TS\FinancialBundle\Entity\BoughtProduct $boughtProducts
     * @return Cart
     */
    public function addBoughtProduct(\TS\FinancialBundle\Entity\BoughtProduct $boughtProducts)
    {
        $this->boughtProducts[] = $boughtProducts;

        return $this;
    }

    /**
     * Remove boughtProducts
     *
     * @param \TS\FinancialBundle\Entity\BoughtProduct $boughtProducts
     */
    public function removeBoughtProduct(\TS\FinancialBundle\Entity\BoughtProduct $boughtProducts)
    {
        $this->boughtProducts->removeElement($boughtProducts);
    }

    /**
     * Get boughtProducts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBoughtProducts()
    {
        return $this->boughtProducts;
    }

    /**
     * Set invoice
     *
     * @param \TS\FinancialBundle\Entity\Invoice $invoice
     * @return Cart
     */
    public function setInvoice(\TS\FinancialBundle\Entity\Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return \TS\FinancialBundle\Entity\Invoice 
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
