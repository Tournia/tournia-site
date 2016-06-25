<?php
namespace TS\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TS\ApiBundle\Entity\Person;

class PersonEvent extends Event
{
    protected $person;

    /**
     * @var object The object on which the player has received authorization. Can be null, a Player or a Tournament
     */
    protected $addedAuthorization;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    public function getPerson()
    {
        return $this->person;
    }

    /**
     * The object on which the player has received authorization.
     * @param mixed $object Can be null, a Player or a Tournament
     */
    public function setAddedAuthorization($object) {
        $this->addedAuthorization = $object;
    }

    public function getAddedAuthorization() {
        return $this->addedAuthorization;
    }
}