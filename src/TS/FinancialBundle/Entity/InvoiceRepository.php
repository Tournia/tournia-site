<?php

namespace TS\FinancialBundle\Entity;

use Doctrine\ORM\EntityRepository;
use TS\ApiBundle\Entity\Tournament;
use TS\ApiBundle\Entity\Person;

/**
 * InvoiceRepository
 *
 */
class InvoiceRepository extends EntityRepository
{
    /**
     * Get invoices for a specific Person
     * @param \TS\ApiBundle\Entity\Person $person
     * @return array
     */
    public function getPersonInvoices(Person $person)
    {
        $query = $this->createQueryBuilder('i')
            ->leftJoin("i.cartOrder", "cartOrder")
            ->andWhere('cartOrder.execPerson = :person')
            ->andWhere('i.payOut is NULL')
            ->setParameter('person', $person)
            ->getQuery();
        return $query->getResult();
    }

    /**
     * Get invoices for tournaments, in which a Person is organizer
     * @param \TS\ApiBundle\Entity\Person $person
     * @return array
     */
    public function getTournamentInvoices(Person $person) {
        $query = $this->createQueryBuilder('i')
            ->leftJoin("i.payOut", "payOut")
            ->leftJoin("payOut.tournament", "tournament")
            ->leftJoin("tournament.organizerPersons", "organizerPersons")
            ->andWhere('organizerPersons = :person')
            ->andWhere('i.payOut is not NULL')
            ->setParameter('person', $person)
            ->getQuery();
        return $query->getResult();
    }
}
