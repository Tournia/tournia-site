<?php
namespace TS\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class PayOut
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
     * @ORM\OneToMany(targetEntity="\TS\FinancialBundle\Entity\BoughtProduct", mappedBy="payOut")
     *
     */
    private $boughtProducts;

    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="payOuts")
     */
    private $tournament;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $paypalAccount;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $bankAccount;

    /**
     * @var integer The total amount which is paid out to the organizer, in cents
     *
     * @ORM\Column(type="integer")
     */
    private $paidoutAmount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTime;

    /**
     * @ORM\OneToOne(targetEntity="\TS\FinancialBundle\Entity\Invoice", mappedBy="payOut")
     */
    private $invoice;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->boughtProducts = new ArrayCollection();
        $this->paidoutAmount = 0;
        $this->dateTime = new \DateTime("now");
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
     * Set paypalAccount
     *
     * @param string $paypalAccount
     * @return PayOut
     */
    public function setPaypalAccount($paypalAccount)
    {
        $this->paypalAccount = $paypalAccount;

        return $this;
    }

    /**
     * Get paypalAccount
     *
     * @return string 
     */
    public function getPaypalAccount()
    {
        return $this->paypalAccount;
    }

    /**
     * Set bankAccount
     *
     * @param string $bankAccount
     * @return PayOut
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return string 
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set paidoutAmount
     *
     * @param integer $paidoutAmount
     * @return PayOut
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
     * Set dateTime
     *
     * @param \DateTime $dateTime
     * @return PayOut
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime 
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Add boughtProducts
     *
     * @param \TS\FinancialBundle\Entity\BoughtProduct $boughtProducts
     * @return PayOut
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
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return PayOut
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
     * Set invoice
     *
     * @param \TS\FinancialBundle\Entity\Invoice $invoice
     * @return PayOut
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
