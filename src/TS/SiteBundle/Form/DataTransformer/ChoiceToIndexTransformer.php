<?php
namespace TS\SiteBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class ChoiceToIndexTransformer implements DataTransformerInterface
{
    /**
     * @var choiceOptions in an array
     */
    private $choiceOptions;

    /**
     * @param Tournament entity $tournament
     */
    public function __construct($choiceOptions)
    {
        $this->choiceOptions = $choiceOptions;
    }

    /**
     * Transforms a choice to an index.
     */
    public function transform($choice)
    {
        $key = array_search($choice, $this->choiceOptions);
        if ($key === false) {
        	return null;
        } else {
        	return $key;
        }
    }

    /**
     * Transforms a index to an choice string.
     */
    public function reverseTransform($index)
    {
        if (!is_null($index) && array_key_exists($index, $this->choiceOptions)) {
        	return $this->choiceOptions[$index];
        } else {
        	return null;
        }
    }
}