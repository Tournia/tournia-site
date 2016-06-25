<?php
namespace TS\ApiBundle\Controller\v2;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Entity\Player;
use TS\NotificationBundle\Event\PlayerEvent;
use TS\NotificationBundle\NotificationEvents;


class PlayersController extends ApiV2MainController
{


    /**
     * Get list of players
     *
     * @ApiDoc(
     *   views="v2",
     *   section="Players",
     *   description="Players.list",
     *   filters = {
     *		{"name"="full", "required"="false", "type"="boolean", "default"=false, "description"="Whether to return all information about the player"},
     *      {"name"="status", "required"="false", "type"="array", "description"="Filter by status of player"},
     *      {"name"="playerId", "required"="false", "type"="integer", "description"="Specific Player ID", "default"="All players"},
     *   }
     * )
     */
    public function listAction() {
        $full = $this->getParam('full', false, false);

        // Use for meta data
        $columns = array(); // save columns in a separate array, which can be returned

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Player');
        $query = $repository->createQueryBuilder('p')
            ->andWhere('p.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->orderBy('p.id', 'ASC');

        $filterStatus = $this->getParam('status', false, array());
        if (!empty($filterStatus)) {
            $query = $query
                ->andWhere('p.status IN (:selectedStatus)')
                ->setParameter('selectedStatus', $filterStatus);
        }
        $filterPlayer = $this->getParam('playerId', false, null);
        if (!empty($filterPlayer)) {
            $query = $query
                ->andWhere('p.id = :playerId')
                ->setParameter('playerId', $filterPlayer);
        }

        $playersResult = $query->getQuery()->getResult();

        $playersArray = array();
        foreach ($playersResult as $player) {
            if ($full) {
                // use full player data
                $playerData = $this->getFullPlayerData($player);

                // now check which columns are new, and add these to $columns
                $playerColumns = array_keys($playerData);
                foreach ($playerColumns as $column) {
                    if (!in_array($column, $columns)) {
                        $columns[] = $column;
                    }
                }

                $playersArray[$player->getId()] = $playerData;
            } else {
                $playersArray[$player->getId()] = array(
                    'playerId' => $player->getId(),
                    'name' => $player->getName(true),
                );
            }
        }

        if ($full) {
            $res = array(
                'meta' => array(
                    'columns' => $columns,
                ),
                'players' => $playersArray,
            );
        } else {
            $res = array(
                'players' => $playersArray,
            );
        }

        return $this->handleResponse($res);
    }

    /**
     * Create a player
     *
     * @ApiDoc(
     *   views="v2",
     *   section="Players",
     *   description="Players.create",
     *   filters = {
     *		{"name"="firstName", "required"="true", "type"="string", "description"="First name"},
     *		{"name"="lastName", "required"="true", "type"="string", "description"="Last name"},
     *		{"name"="gender", "required"="true", "type"="character, M or F", "description"="Gender, M or F"},
     *		{"name"="groupId", "required"="true", "type"="integer", "description"="Group ID"},
     *		{"name"="status", "required"="false", "type"="string", "description"="Status of player"}
     *   }
     * )
     */
    public function createAction() {
        $registrationGroup = $this->getRegistrationGroup($this->getParam('groupId'));

        $player = new Player();
        $player->setFirstName($this->getParam('firstName'));
        $player->setLastName($this->getParam('lastName'));
        $player->setGender($this->getParam('gender'));
        $player->setRegistrationGroup($registrationGroup);
        $player->setTournament($this->tournament);
        $status = $this->getParam('status', false, $this->tournament->getNewPlayerStatus());
        $player->setStatus($status);

        $this->em()->persist($player);

        // Create new player event
        $event = new PlayerEvent($player);
        $this->get('event_dispatcher')->dispatch(NotificationEvents::PLAYER_NEW, $event);

        $message = 'created new player '. $player->getName();
        if (!is_null($registrationGroup)) {
            $message .= ' for group '. $registrationGroup->getName();
        }
        $this->newMessage('success', 'New player', $message);
        $res = array(
            'message' => $message,
            'playerId' => $player->getId()
        );
        return $this->handleResponse($res);
    }

    /**
     * Get information about specific player
     *
     * @ApiDoc(
     *   views="v2",
     *   section="Players",
     *   description="Players.get",
     *   requirements = {
     *       {"name"="playerId", "dataType"="Integer", "description"="Player ID"},
     *   },
     *   filters = {
     *		{"name"="full", "required"="false", "type"="boolean", "default"=false, "description"="Whether to return all information about the player"},
     *   }
     * )
     */
    public function getAction($playerId) {
        $player = $this->getPlayer($playerId);
        
        if ($this->getParam('full', false, false)) {
            // append full player data
            $res = $this->getFullPlayerData($player);
        } else {
            $res = array(
                'playerId' => $player->getId(),
                'name' => $player->getName(),
            );
        }
        return $this->handleResponse($res);
    }

    /**
     * Get matches of a specific player
     *
     * @ApiDoc(
     *   views="v2",
     *   section="Players",
     *   description="Players.matches",
     *   deprecated=true,
     *   requirements = {
     *       {"name"="playerId", "dataType"="Integer", "description"="Player ID"},
     *   }
     * )
     */
    public function matchesAction($playerId) {
        $player = $this->getPlayer($playerId);
        $res = $this->getPlayerMatchesData($player);
        return $this->handleResponse($res);
    }

    /**
     * Change whether player is ready
     *
     * @ApiDoc(
     *   views="v2",
     *   section="Players",
     *   description="Players.setReady",
     *   requirements = {
     *       {"name"="playerId", "dataType"="Integer", "description"="Player ID"},
     *   },
     *   filters = {
     *		{"name"="ready", "required"="true", "type"="string", "description"="Wether player is ready", "pattern"="(true|false|toggle)"},
     *		{"name"="nonreadyReason", "required"="false", "type"="string", "description"="Optional reason for why player is not ready. Only applied when ready==false"}
     *   }
     * )
     */
    public function setReadyAction($playerId) {
        $player = $this->getPlayer($playerId);
        if ($this->getParam('ready') == "toggle") {
            $setReady = $player->getReady() == false;
        } else {
            $setReady = $this->getParam('ready') == "true";
        }

        $player->setReady($setReady);
        if ($setReady) {
            // remove non-ready reason
            $player->setNonreadyReason(null);

            $res = 'set player '. $player->getName() .' to ready';
            $this->newMessage('success', 'Player ready', $res, 'player');
        } else {
            $res = 'postponed player '. $player->getName();

            // apply optional non-ready reason
            $nonreadyReason = $this->getParam('nonreadyReason', false, null);
            if ($nonreadyReason != null) {
                $player->setNonreadyReason($nonreadyReason);
                $res .= " with reason ". $nonreadyReason;
            }

            $this->newMessage('success', 'Player postponed', $res, 'player');
        }

        return $this->handleResponse($res);
    }

    // get data of all matches of a certain player
    private function getPlayerMatchesData($player) {
        $res = array();

        // lookup matches with this player
        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Match');
        $query = $repository->createQueryBuilder('m')
            ->andWhere('m.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->leftJoin('m.team1', 'team1')
            ->leftJoin('m.team2', 'team2')
            ->leftJoin('team1.players', 'players1')
            ->leftJoin('team2.players', 'players2')
            ->andWhere('players1.id = :playerId OR players2.id = :playerId')
            ->setParameter('playerId', $player->getId())
            ->orderBy('m.localId', 'ASC')
            ->getQuery();
        $matches = $query->getResult();

        foreach ($matches as $match) {
            $matchArray = array();
            $this->fillPlayingData($match, $matchArray);
            $res[] = $matchArray;
        }

        return $res;
    }
    private function fillPlayingData($match, &$matchArray) {
        $matchArray['matchId'] = $match->getId();
        $matchArray['localId'] = $match->getLocalId();

        // lookup players of team1 and team2
        $team1 = $match->getTeam1();
        $matchArray['team1Players'] = array();
        if (!is_null($team1)) {
            $matchArray['team1Id'] = $team1->getId();
            foreach($team1->getPlayersForAllPositions() as $player) {
                $matchArray['team1Players'][$player->getId()] = $player->getName();
            }
        }
        $team2 = $match->getTeam2();
        $matchArray['team2Players'] = array();
        if (!is_null($team2)) {
            $matchArray['team2Id'] = $team2->getId();
            foreach($team2->getPlayersForAllPositions() as $player) {
                $matchArray['team2Players'][$player->getId()] = $player->getName();
            }
        }

        $matchArray['pool'] = $match->getPool()->getName();
        $matchArray['round'] = $match->getRound();
        $matchArray['status'] = ucfirst($match->getStatus());
        $matchArray['score'] = $match->getScoreTextual();
        if (!is_null($match->getLocation())) {
            $matchArray['location'] = $match->getLocation()->getName();
        }
        return $matchArray;
    }

    /**
     * Retrieve all player data and put in array
     * @param \TS\ApiBundle\Entity\Player $player
     * @return array
     */
    private function getFullPlayerData($player) {
        $playerArray = array(
            'playerId' => $player->getId(),
            'firstName' => $player->getFirstName(),
            'lastName' => $player->getLastName(),
            'fullName' => $player->getName(),
            'registrationDate' => $player->getRegistrationDate()->format("d-M-Y H:i:s"),
            'status' => $player->getStatus(),
            'ready' => $player->getReady(),
            'nonreadyReason' => $player->getNonreadyReason(),
        );
        $playerArray['gender'] = $player->getGender() == "M" ? "Male" : "Female";

        $playerArray['disciplines'] = array();
        // add DisciplineType columns, so that the order (i.e. position) of Disciplines is correct
        foreach ($this->tournament->getDisciplineTypes() as $disciplineType) {
            $playerArray['disciplines'][$disciplineType->getName()] = '';
        }
        foreach ($player->getDisciplinePlayers() as $disciplinePlayer) {
            /* @var \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayer */
            $disciplineTypeName = $disciplinePlayer->getDiscipline()->getDisciplineType()->getName();
            $playerArray['disciplines'][$disciplineTypeName] = array(
                'disciplineId' => $disciplinePlayer->getDiscipline()->getId(),
                'disciplineName' => $disciplinePlayer->getDiscipline()->getName(),
                'partner' => $disciplinePlayer->getPartner()
            );
        }

        $playerArray['teams'] = array();
        foreach ($player->getTeams() as $team) { /* @var \TS\ApiBundle\Entity\Team $team */
            // look for disciplines that are connected to the pool
            $registeredForDiscipline = null;
            foreach ($team->getPool()->getInputDisciplines() as $discipline) {
                foreach ($player->getDisciplinePlayers() as $disciplinePlayer) {
                    if ($disciplinePlayer->getDiscipline() == $discipline) {
                        $registeredForDiscipline = array(
                            'disciplineType' => $discipline->getDisciplineType()->getName(),
                            'disciplineId' => $discipline->getId(),
                            'disciplineName' => $discipline->getName(),
                        );
                    }
                }
            }

            $playerArray['teams'][] = array(
                'poolId' => $team->getPool()->getId(),
                'poolName' => $team->getPool()->getName(),
                'teamId' => $team->getId(),
                'teamName' => $team->getName(),
                'givenUp' => $team->getGivenUp(),
                'registeredForDiscipline' => $registeredForDiscipline,
            );
        }

        if ($this->tournament->getRegistrationGroupEnabled()) {
            $playerArray['registrationGroup'] = (!is_null($player->getRegistrationGroup())) ? $player->getRegistrationGroup()->getName() : '';
            $playerArray['registrationGroupContactPlayer'] = $player->getIsContactPlayer() ? "Yes" : "No";
        }

        $playerArray['paymentBalance'] = $this->tournament->getPaymentCurrency() ." ". ($player->getPaymentBalance() / 100);

        if ($player->getPerson() != null) {
            $playerArray['personEmail'] = $player->getPerson()->getEmail();
            $playerArray['personName'] = $player->getPerson()->getName();
        }

        $playerArray['boughtProducts'] = array();
        foreach ($player->getBoughtProducts() as $boughtProduct) { /* @var \TS\FinancialBundle\Entity\BoughtProduct $boughtProduct */
            $playerArray['boughtProducts'][] = array(
                'boughtProductId' => $boughtProduct->getId(),
                'quantity' => $boughtProduct->getQuantity(),
                'name' => $boughtProduct->getName(),
                'amount' => $boughtProduct->getAmount(),
            );
        }

        // add RegistrationFormFields columns, so that the order (i.e. position) of RegistrationFormFields is correct
        foreach ($this->tournament->getRegistrationFormFields() as $field) {
            $playerArray[$field->getName()] = '';
        }
        foreach ($player->getRegistrationFormValues() as $formValue) {
            $value = $formValue->getValue();
            if ($formValue->getField()->getType() == 'checkbox') {
                $value = $formValue->getValue() == '1' ? "Yes" : "No";
            }
            $playerArray[$formValue->getField()->getName()] = $value;
        }

        return $playerArray;
    }
}