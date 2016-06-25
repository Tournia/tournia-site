<?php
namespace TS\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TS\ApiBundle\Entity\Tournament;

class TournamentEvent extends Event
{
    protected $tournament;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    public function getTournament()
    {
        return $this->tournament;
    }
}