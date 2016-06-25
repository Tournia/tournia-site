<?php
namespace TS\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TS\ApiBundle\Entity\Person;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\RegistrationGroup;

class RegistrationGroupEvent extends Event
{
    protected $registrationGroup;

    private $originalRegistrationGroup;
    private $originalContactPlayerNames;

    public function __construct(RegistrationGroup $registrationGroup)
    {
        $this->registrationGroup = $registrationGroup;
    }

    public function getRegistrationGroup()
    {
        return $this->registrationGroup;
    }

    /**
     * Before changing group, the original values have to be saved so changes can be displayed
     */
    public function saveOriginalRegistrationGroup() {
        $this->originalRegistrationGroup = clone $this->registrationGroup;

        $this->originalContactPlayerNames = array();
        foreach ($this->registrationGroup->getPlayers() as $player) {
            /* @var \TS\ApiBundle\Entity\Player $player */
            if ($player->getIsContactPlayer()) {
                $this->originalContactPlayerNames[] = $player->getName(false);
            }
        }
    }

    /**
     * Return the cloned player from saveOriginalRegistrationGroup()
     * @return \TS\ApiBundle\Entity\RegistrationGroup
     */
    public function getOriginalRegistrationGroup() {
        return $this->originalRegistrationGroup;
    }

    public function getOriginalContactPlayerNames() {
        return $this->originalContactPlayerNames;
    }
}