<?php
namespace TS\SiteBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use TS\ApiBundle\Entity\Player;

class PlayerToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Transforms an object (player) to a string (id).
     *
     * @param  Player|null $player
     * @return string
     */
    public function transform($player)
    {
        if (null === $player) {
            return "";
        }

        return $player->getId();
    }

    /**
     * Transforms a string ($id) to an object (player).
     *
     * @param  string $id
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (player) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $player = $this->em
            ->getRepository('TSApiBundle:Player')
            ->findOneById($id)
        ;

        if (null === $player) {
            throw new TransformationFailedException(sprintf(
                'A player with id "%s" does not exist!',
                $id
            ));
        }

        return $player;
    }
}