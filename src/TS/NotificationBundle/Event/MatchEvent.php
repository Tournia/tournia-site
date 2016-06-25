<?php
namespace TS\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TS\ApiBundle\Entity\Match;

class MatchEvent extends Event
{
    protected $match;

    public function __construct(Match $match)
    {
        $this->match = $match;
    }

    public function getMatch()
    {
        return $this->match;
    }
}