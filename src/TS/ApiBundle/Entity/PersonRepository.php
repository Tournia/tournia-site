<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PersonRepository
 */
class PersonRepository extends EntityRepository
{

    /**
     * Merge tow Persons.
     * LoginAccounts, Authorizations (organizingTournaments, players) and execPayments are moved from $loginAccount->getPerson() to $person
     * After performing this method, $oldPerson is removed
     * @param \TS\ApiBundle\Entity\Person $oldPerson
     * @param \TS\ApiBundle\Entity\Person $newPerson
     */
    public function mergePersons($oldPerson, $newPerson)
    {
        // take over loginAccounts
        $this->addIfNotContains($oldPerson->getLoginAccounts(), $newPerson->getLoginAccounts());
        foreach ($oldPerson->getLoginAccounts() as $loginAccount) {
            $loginAccount->setPerson($newPerson);
        }

        // take over organizingTournaments
        $this->addIfNotContains($oldPerson->getOrganizingTournaments(), $newPerson->getOrganizingTournaments());
        foreach ($oldPerson->getOrganizingTournaments() as $organizingTournament) {
            $organizingTournament->removeOrganizerPerson($oldPerson);
        }

        // take over players
        $this->addIfNotContains($oldPerson->getPlayers(), $newPerson->getPlayers());
        foreach ($oldPerson->getPlayers() as $player) {
            $player->setPerson($newPerson);
        }

        // update carts (and with that, orders)
        if (!is_null($oldPerson->getCarts())) {
            foreach ($oldPerson->getCarts() as $cart) { /* @var \TS\FinancialBundle\Entity\Cart $cart */
                $cart->setExecPerson($newPerson);
            }
        }

        // check empty values and take over
        if (empty($newPerson->getEmail())) {
            $newPerson->setEmail($oldPerson->getEmail());
        }
        if (empty($newPerson->getFirstName())) {
            $newPerson->setFirstName($oldPerson->getFirstName());
        }
        if (empty($newPerson->getLastName())) {
            $newPerson->setLastName($oldPerson->getLastName());
        }
        if (empty($newPerson->getGender())) {
            $newPerson->setGender($oldPerson->getGender());
        }

        // remove OldPerson and DB flush
        $em = $this->getEntityManager();
        $em->remove($oldPerson);
        $em->flush();
    }

    private function addIfNotContains($fromElements, $toElements) {
        foreach ($fromElements as $element) {
            if (!$toElements->contains($element)) {
                $toElements->add($element);
            }
        }
    }
}
