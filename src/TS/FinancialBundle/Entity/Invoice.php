<?php
namespace TS\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\FinancialBundle\Entity\InvoiceRepository")
 */
class Invoice
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $invoiceNr;

    /**
     * @ORM\OneToOne(targetEntity="\TS\FinancialBundle\Entity\Cart", inversedBy="invoice", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $cartOrder;

    /**
     * @ORM\OneToOne(targetEntity="\TS\FinancialBundle\Entity\PayOut", inversedBy="invoice", cascade={"persist"})
     */
    private $payOut;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $token;


    /**
     * Constructor
     */
    public function __construct()
    {
        // generate random token
        $this->token = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 30);
    }

    /**
     * Get invoiceNr
     *
     * @return string 
     */
    public function getInvoiceNr()
    {
        return $this->invoiceNr;
    }

    /**
     * Set cartOrder
     *
     * @param \TS\FinancialBundle\Entity\Cart $cartOrder
     * @return Invoice
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
     * Set payOut
     *
     * @param \TS\FinancialBundle\Entity\PayOut $payOut
     * @return Invoice
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

    /**
     * Set token
     *
     * @param string $token
     * @return Invoice
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }
}
