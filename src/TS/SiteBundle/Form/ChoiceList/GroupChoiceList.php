<?php

namespace TS\SiteBundle\Form\ChoiceList

use Symfony\Component\Form\Extension\Core\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

use TS\ApiBundle\Entity\Tournament;

class GroupChoiceList extends LazyChoiceList 
{
    public function __construct($tournamentUrl) {
    	
    }
    
    protected function loadChoiceList()
    {
        //fetch and process api data
        
        return new ChoiceList($choices, $labels);

    }
}