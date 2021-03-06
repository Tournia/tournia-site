<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UpdateMessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UpdateMessageRepository extends EntityRepository
{

	public function getLastId() {
		try {
			$res = $this->getEntityManager()
				->createQuery('SELECT u.id FROM TSApiBundle:UpdateMessage u ORDER BY u.id DESC')
				->setMaxResults(1)
				->getSingleResult();
			return $res['id'];
		} catch (\Doctrine\Orm\NoResultException $e) {
			return 0;
		}
	}
	
	// Get UpdateMessages from (not including) an id till (including) an id
	public function getBetweenId($fromId, $toId, $tournament) {
		$query = $this->createQueryBuilder('u')
			->andWhere('u.tournament = :tournament')
			->setParameter('tournament', $tournament)
			->andWhere('u.id > :fromId AND u.id <= :toId')
			->setParameter('fromId', $fromId)
			->setParameter('toId', $toId)
			->orderBy('u.id', 'ASC')
			->getQuery();
		return $query->getResult();
	}
}	
