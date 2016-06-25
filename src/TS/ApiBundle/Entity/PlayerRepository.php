<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PlayerRepository
 *
 */
class PlayerRepository extends EntityRepository
{
	public function getAllPlayersFullInfo($tournament)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT player, registrationGroup, disciplinePlayers, registrationFormValues
                FROM TSApiBundle:Player player
                LEFT JOIN player.registrationGroup registrationGroup
                LEFT JOIN player.disciplinePlayers disciplinePlayers
				LEFT JOIN player.registrationFormValues registrationFormValues
                WHERE player.tournament = :tournament
                ORDER BY player.registrationDate ASC'
            )
            ->setParameter('tournament', $tournament)
            ->getResult();
    }

    /**
     * Get totat payment balance for a Player
     */
    public function getPaymentBalance(Player $player) {
        $balance = 0.0;
        foreach ($player->getPayments() as $payment) {
            $balance += $payment->getAmount();
        }
        return $balance;
    }
}
