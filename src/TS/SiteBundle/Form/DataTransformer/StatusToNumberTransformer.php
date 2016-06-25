<?php
namespace TS\SiteBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class StatusToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var Tournament enitity
     */
    private $tournament;

    /**
     * @param Tournament entity $tournament
     */
    public function __construct($tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * Transforms a status to an index.
     */
    public function transform($status)
    {
        $key = array_search($status, $this->tournament->getStatusOptions());
        if ($key === false) {
        	return "";
        } else {
        	return $key;
        }
    }

    /**
     * Transforms a index to an status string.
     */
    public function reverseTransform($index)
    {
        $optionsArray = $this->tournament->getStatusOptions();
        if (!is_null($index) && $index <= sizeof($optionsArray)) {
        	return $optionsArray[$index];
        } else {
        	return "";
        }
    }
}