<?php
namespace TS\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class VatCountry
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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $countryName;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $vatPercentage;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $invoiceDescription;


    /**
     * Constructor
     */
    public function __construct()
    {

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
     * Set countryCode
     *
     * @param string $countryCode
     * @return VatCountry
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set countryName
     *
     * @param string $countryName
     * @return VatCountry
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * Get countryName
     *
     * @return string 
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * Set vatPercentage
     *
     * @param string $vatPercentage
     * @return VatCountry
     */
    public function setVatPercentage($vatPercentage)
    {
        $this->vatPercentage = $vatPercentage;

        return $this;
    }

    /**
     * Get vatPercentage
     *
     * @return string 
     */
    public function getVatPercentage()
    {
        return $this->vatPercentage;
    }

    /**
     * Set invoiceDescription
     *
     * @param string $invoiceDescription
     * @return VatCountry
     */
    public function setInvoiceDescription($invoiceDescription)
    {
        $this->invoiceDescription = $invoiceDescription;

        return $this;
    }

    /**
     * Get invoiceDescription
     *
     * @return string 
     */
    public function getInvoiceDescription()
    {
        return $this->invoiceDescription;
    }
}
