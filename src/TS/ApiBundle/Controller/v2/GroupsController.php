<?php
namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Controller\v2\ApiV2MainController;
use TS\ApiBundle\Entity\RegistrationGroup;
use TS\NotificationBundle\Event\RegistrationGroupEvent;
use TS\NotificationBundle\NotificationEvents;

class GroupsController extends ApiV2MainController
{



    /**
     * Get list of groups
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Groups",
     *  description="Groups.list"
     * )
     */
    public function listAction() {
        $res = $this->getGroupsData();
        return $this->handleResponse($res);
    }

    /**
     * Get a group
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Groups",
     *  description="Groups.get"
     * )
     */
    public function getAction($groupId) {
        $group = $this->getRegistrationGroup($groupId);
        $res = array(
            'groupId' => $group->getId(),
            'name' => $group->getName(),
            'country' => $group->getCountry(),
        );
        return $this->handleResponse($res);
    }

    /**
     * Create a new group
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Groups",
     *  description="Groups.create",
     *  filters = {
     *		{"name"="name", "required"="true", "type"="string", "description"="Name of group"},
     *		{"name"="country", "required"="true", "type"="string", "description"="Country of group"}
     *  }
     * )
     */
    public function createAction() {
        $name = $this->getParam('name');
        $group = new RegistrationGroup();
        $group->setName($this->getParam('name'));
        $group->setCountry($this->getParam('country'));
        $group->setTournament($this->tournament);
        $this->em()->persist($group);

        $message = 'created a new group '. $group->getName();
        $this->newMessage('success', 'Added group', $message);
        $res = array(
            'message' => $message,
            'groupId' => $group->getId()
        );
        return $this->handleResponse($res);
    }


    /**
     * Edit group
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Groups",
     *  description="Groups.edit",
     *  requirements = {
     *     {"name"="groupId", "type"="integer", "description"="Group ID"}
     *  },
     *  filters = {
     *		{"name"="name", "required"="false", "type"="string", "description"="Name of location"},
     *		{"name"="country", "required"="false", "type"="string", "description"="Country of location"}
     *  }
     * )
     */
    public function editAction($groupId) {
        $group = $this->getRegistrationGroup($groupId);

        $event = new RegistrationGroupEvent($group);
        $event->saveOriginalRegistrationGroup();

        $group->setName($this->getParam('name', false, $group->getName()));
        $group->setCountry($this->getParam('country', false, $group->getCountry()));

        // Create changed RegistrationGroup event
        $this->container->get('event_dispatcher')->dispatch(NotificationEvents::REGISTRATIONGROUP_CHANGE, $event);

        $res = 'changed group name to '. $group->getName() .' and country '. $group->getCountry();
        $this->newMessage('success', 'Group changed', $res);
        return $this->handleResponse($res);
    }

    /**
     * Remove group. Group should be empty, i.e. has no players.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Groups",
     *  description="Groups.remove",
     *  requirements = {
     *		{"name"="groupId", "type"="integer", "description"="Group ID"}
     *  }
     * )
     */
    public function removeAction($groupId) {
        $group = $this->getRegistrationGroup($groupId);

        if (sizeof($group->getPlayers()) > 0) {
            $res = 'There are players in group '. $group->getName() .' that need to be deleted first';
            $this->newMessage('error', 'Group not empty', $res);
        } else {
            $res = 'removed group '. $group->getName();
            $this->em()->remove($group);
            $this->newMessage('success', 'Group removed', $res);
        }

        return $this->handleResponse($res);
    }


    // get data of all registration groups
    private function getGroupsData() {
        $res = array();

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:RegistrationGroup');
        $query = $repository->createQueryBuilder('g')
            ->andWhere('g.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->orderBy('g.name', 'ASC')
            ->getQuery();
        $registrationGroups = $query->getResult();

        foreach ($registrationGroups as $group) {
            $res[$group->getId()] = array(
                'groupId' => $group->getId(),
                'name' => $group->getName(),
                'country' => $group->getCountry(),
            );
        }

        return $res;
    }
}