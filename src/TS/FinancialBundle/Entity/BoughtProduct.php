<?php
namespace TS\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\FinancialBundle\Entity\BoughtProductRepository")
 */
class BoughtProduct
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
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="boughtProducts")
     */
    private $tournament;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\Range(min = 1, minMessage = "Minimum amount is 1")
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message = "Enter a name")
     */
    private $name;

    /**
     * @var integer The total amount of this bought product
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message = "Enter an amount")
     */
    private $amount;

    /**
     * @var integer The total amount of this bought product, which is paid out to the organizer
     *
     * @ORM\Column(type="integer")
     */
    private $paidoutAmount;

    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Player", inversedBy="boughtProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="\TS\FinancialBundle\Entity\Cart", inversedBy="boughtProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cartOrder;

    /**
     * @ORM\ManyToOne(targetEntity="\TS\FinancialBundle\Entity\PayOut", inversedBy="boughtProducts")
     */
    private $payOut;
    
    

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->paidoutAmount = 0;
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
     * @return Product
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
     * Set quantity
     *
     * @param string $quantity
     * @return BoughtProduct
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return string 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return BoughtProduct
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set player
     *
     * @param \TS\ApiBundle\Entity\Player $player
     * @return BoughtProduct
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
     * Set order
     *
     * @param \TS\FinancialBundle\Entity\Cart $cartOrder
     * @return BoughtProduct
     */
    public function setCartOrder(\TS\FinancialBundle\Entity\Cart $cartOrder = null)
    {
        $this->cartOrder = $cartOrder;

        return $this;
    }

    /**
     * Get cartOrder
     *
     * @return \TS\FinancialBundle\Entity\Cart 
     */
    public function getCartOrder()
    {
        return $this->cartOrder;
    }

    /**
     * Set paidoutAmount
     *
     * @param integer $paidoutAmount
     * @return BoughtProduct
     */
    public function setPaidoutAmount($paidoutAmount)
    {
        $this->paidoutAmount = $paidoutAmount;

        return $this;
    }

    /**
     * Get paidoutAmount
     *
     * @return integer 
     */
    public function getPaidoutAmount()
    {
        return $this->paidoutAmount;
    }

    /**
     * Get outstanding amount, which is amount - paidoutAmount
     */
    public function getOutstandingAmount() {
        return $this->amount - $this->paidoutAmount;
    }

    /**
     * Set payOut
     *
     * @param \TS\FinancialBundle\Entity\PayOut $payOut
     * @return BoughtProduct
     */
    public function setPayOut(\TS\FinancialBundle\Entity\PayOut $payOut = null)
    {
        $this->payOut = $payOut;

        return $this;
    }

    /**
     * Get payOut
     *
     * @return \TS\FinancialBundle\Entity\PayOut 
     */
    public function getPayOut()
    {
        return $this->payOut;
    }
}
