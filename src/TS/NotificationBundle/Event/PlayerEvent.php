<?php
namespace TS\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TS\ApiBundle\Entity\Person;
use TS\ApiBundle\Entity\Player;

class PlayerEvent extends Event
{
    protected $player;

    private $originalPlayer;

    /**
     * @var boolean Whether to send player a notification, by default true
     */
    protected $sendPlayerNotification;

    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->sendPlayerNotification = true;
    }

    public function getplayer()
    {
        return $this->player;
    }

    /**
     * Whether to send player a notification
     * @param boolean $notify
     */
    public function setSendPlayerNotification($notify) {
        $this->sendPlayerNotification = $notify;
    }

    public function getSendPlayerNotification() {
        return $this->sendPlayerNotification;
    }

    /**
     * Before changing player, the original values have to be saved so changes can be displayed
     */
    public function saveOriginalPlayer() {
        $this->originalPlayer = clone $this->player;

        foreach($this->player->getDisciplinePlayers() as $disciplinePlayer) {
            $copiedDisciplinePlayer = clone $disciplinePlayer;
            $copiedDisciplinePlayer->setPlayer($this->originalPlayer);
            $copiedDisciplinePlayer->setDiscipline($disciplinePlayer->getDiscipline());

            $this->originalPlayer->addDisciplinePlayer($copiedDisciplinePlayer);
        }
        $this->originalPlayer->setPerson($this->player->getPerson());
        $this->originalPlayer->setRegistrationGroup($this->player->getRegistrationGroup());
        $this->originalPlayer->setTournament($this->player->getTournament());
        foreach($this->player->getRegistrationFormValues() as $formValue) {
            $copiedFormValue = clone $formValue;
            $copiedFormValue->setPlayer($this->originalPlayer);
            $copiedFormValue->setField($formValue->getField());

            $this->originalPlayer->addRegistrationFormValue($copiedFormValue);
        }
    }

    /**
     * Return the cloned player from saveOriginalPlayer()
     * @return \TS\ApiBundle\Entity\Player
     */
    public function getOriginalPlayer() {
        return $this->originalPlayer;
    }
}