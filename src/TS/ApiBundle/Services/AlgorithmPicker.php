<?php

namespace TS\ApiBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlgorithmPicker
{
    private $entityManager;
    private $eventDispatcher;

    public function __construct(EntityManager $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $dispatcher;
    }
    
    public function pick($classLocation, $namespace)
    {
        //Determine default algorithm class name
        $arrayAlgorithmClassLocation = explode("\\", $classLocation);

        $sAlgorithmSort = $arrayAlgorithmClassLocation[0];
        $sAlgorithmName = $arrayAlgorithmClassLocation[1];
        $sAlgorithmClassName = $arrayAlgorithmClassLocation[1] . "Algorithm";
        
        $sAlgorithmClass = $namespace . '\\' . $sAlgorithmSort . '\\' .$sAlgorithmName  . '\\' . $sAlgorithmClassName;

		if (!class_exists($sAlgorithmClass)) {
			throw new NotFoundHttpException('Algorithm class ' . $sAlgorithmClass .' does not exist');
		}

        $objectAlgorithm = new $sAlgorithmClass($this->entityManager, $this->eventDispatcher);
        
        if ($objectAlgorithm instanceof $namespace .'\\AlgorithmAbstract') {
            return $objectAlgorithm;
        }
        else {
            throw new Exception('The algorithm class has to be an instance of the algorithm interface class');
        }
    }
}