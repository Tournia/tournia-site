<?php
namespace TS\ControlBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use TS\ApiBundle\Entity\Product;

class ProductToIdTransformer implements DataTransformerInterface
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
     * Transforms an object (product) to a string (id).
     *
     * @param  Product|null $product
     * @return string
     */
    public function transform($product)
    {
        if (null === $product) {
            return "";
        }

        return $product->getId();
    }

    /**
     * Transforms a string ($id) to an object (product).
     *
     * @param  string $id
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (product) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $product = $this->em
            ->getRepository('TSFinancialBundle:Product')
            ->findOneById($id)
        ;

        if (null === $product) {
            throw new TransformationFailedException(sprintf(
                'A product with id "%s" does not exist!',
                $id
            ));
        }

        return $product;
    }
}