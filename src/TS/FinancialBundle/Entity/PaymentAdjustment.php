<?php
namespace TS\FinancialBundle\Entity;

use Sylius\Bundle\OrderBundle\Model\Adjustment as BaseAdjustment;
use Sylius\Bundle\OrderBundle\Model\OrderInterface;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class PaymentAdjustment extends BaseAdjustment
{

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $quantity;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->quantity = 1;
    }

    /**
     * Set Adjustable order
     * @var OrderInterface
     * @param OrderInterface $order
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return PaymentAdjustment
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
