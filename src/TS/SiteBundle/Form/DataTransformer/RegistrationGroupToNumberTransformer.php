<?php
namespace TS\SiteBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use TS\ApiBundle\Entity\RegistrationGroup;

class RegistrationGroupToNumberTransformer implements DataTransformerInterface
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
     * Transforms an object (group) to a string (id).
     *
     * @param  RegistrationGroup|null $group
     * @return string
     */
    public function transform($group)
    {
        if (null === $group) {
            return "";
        }

        return $group->getId();
    }

    /**
     * Transforms a string ($id) to an object (group).
     *
     * @param  string $id
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (group) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $group = $this->em
            ->getRepository('TSApiBundle:RegistrationGroup')
            ->findOneById($id)
        ;

        if (null === $group) {
            throw new TransformationFailedException(sprintf(
                'A group with id "%s" does not exist!',
                $id
            ));
        }

        return $group;
    }
}