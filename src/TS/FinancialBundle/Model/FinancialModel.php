<?php

namespace TS\FinancialBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;


class FinancialModel
{
    private $container;
    private $doctrine;


    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');
    }


    /**
     * Calculate the Value Added Taxes (VAT) over cart adjustments.
     * Cart adjustments are excluding VAT, so result = amount * VAT%
     * @param \Sylius\Bundle\CartBundle\Model\CartInterface $cart
     * @param float $vatPercentage The VAT percentage
     * @return int The VAT in cents
     */
    public function calculateVat($cart, $vatPercentage) {
        $adjustmentTotal = 0;
        foreach ($cart->getAdjustments() as $adjustment) {
            $adjustmentTotal += $adjustment->getAmount();
        }
        $vatPercentage = $vatPercentage / 100;
        return round($adjustmentTotal * $vatPercentage);
    }

    /**
     * Calculate the Value Added Taxes (VAT) over cart adjustments.
     * Cart adjustments are including VAT, so adjustment amounts are changed to exclude VAT
     * @param \Sylius\Bundle\CartBundle\Model\CartInterface $cart
     * @param float $vatPercentage The VAT percentage
     * @return int The VAT in cents
     */
    public function calculateVatMakeExcl($cart, $vatPercentage) {
        $vatPercentage = $vatPercentage / 100;
        $totalInclAmount = 0;
        $totalExclAmount = 0;
        foreach ($cart->getAdjustments() as $adjustment) {
            $inclAmount = $adjustment->getAmount();
            $exclAmount = round($inclAmount / (1+$vatPercentage));
            $adjustment->setAmount($exclAmount);
            
            $totalInclAmount += $inclAmount;
            $totalExclAmount += $exclAmount;
        }

        return $totalInclAmount - $totalExclAmount;
    }

    /**
     * Get VAT Country choices
     * @return array() with key the countryCode and value the countryName
     */
    public function getVatCountryChoices() {
        $repository = $this->container->get('doctrine')
            ->getRepository('TSFinancialBundle:VatCountry');
        $query = $repository->createQueryBuilder('vc')
            ->orderBy('vc.countryName', 'ASC')
            ->getQuery();
        $vatCountries = $query->getResult();

        $choices = array();
        foreach ($vatCountries as $vatCountry) { /* @var \TS\FinancialBundle\Entity\VatCountry $vatCountry */
            $choices[$vatCountry->getCountryCode()] = $vatCountry->getCountryName();
        }
        return $choices;
    }
}