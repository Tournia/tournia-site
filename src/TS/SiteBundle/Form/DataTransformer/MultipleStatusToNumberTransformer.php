<?php
namespace TS\SiteBundle\Form\DataTransformer;

use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToBooleanArrayTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class MultipleStatusToNumberTransformer extends ChoicesToBooleanArrayTransformer
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
     * Transforms a status array to an options array
     */
    public function transform($array)
    {
        $res = array();
        if (is_array($array)) {
	        foreach ($array as $key=>$value) {
	        	$res[$key] = true;
	        }
	    }
        return $res;
    }

    /**
     * Transforms selected indexes to an status array.
     */
    public function reverseTransform($values)
    {
        $res = array();
        $statusOptions = $this->tournament->getStatusOptions();
    	foreach ($values as $value) {
    		if ($value <= sizeof($statusOptions)) {
    			$res[] = $statusOptions[$value];
    		}
        }
        return $res;
    }
}