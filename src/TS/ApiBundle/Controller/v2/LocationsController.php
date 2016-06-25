<?php
namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Controller\v2\ApiV2MainController;
use TS\ApiBundle\Entity\Location;

class LocationsController extends ApiV2MainController
{



    /**
     * Get list of locations
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Locations",
     *  description="Locations.list"
     * )
     */
    public function listAction() {
        $res = $this->getLocationsData();
        return $this->handleResponse($res);
    }

    /**
     * Get a location
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Locations",
     *  description="Locations.get"
     * )
     */
    public function getAction($locationId) {
        $location = $this->getLocation($locationId);
        $res = array(
            'id' => $location->getId(),
            'name' => $location->getName(),
            'onHold' => $location->getOnHold(),
            'nonreadyReason' => $location->getNonreadyReason(),
            'position' => $location->getPosition(),
        );
        return $this->handleResponse($res);
    }

    /**
     * Create a new location
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Locations",
     *  description="Locations.create",
     *  filters = {
     *		{"name"="name", "required"="true", "type"="string", "description"="Name of location"}
     *  }
     * )
     */
    public function createAction() {
        $name = $this->getParam('name');
        $location = new Location();
        $location->setName($name);
        $location->setTournament($this->tournament);
        $this->em()->persist($location);
        $res = 'created a new location '. $name;
        $this->newMessage('success', 'Added location', $res);
        return $this->handleResponse($res);
    }


    /**
     * Edit location
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Locations",
     *  description="Locations.edit",
     *  requirements = {
     *     {"name"="locationId", "dataType"="Integer", "description"="Location ID"}
     *  },
     *  filters = {
     *		{"name"="name", "required"="false", "type"="string", "description"="Name of location"},
     *		{"name"="position", "required"="false", "type"="integer", "description"="Position of location"}
     *  }
     * )
     */
    public function editAction($locationId) {
        $location = $this->getLocation($locationId);
        $name = $this->getParam('name', false, null);
        $position = $this->getParam('position', false, null);
        $res = '';

        if (!is_null($name)) {
            $location->setName($name);
            $res = 'changed location name to '. $location->getName();
            $this->newMessage('success', 'Location changed', $res);
        }
        if (!is_null($position)) {
            $location->setPosition($position);
            if ($res != '') {
                $res .= ". ";
            }
            $res .= 'changed position of location '. $location->getName();
            $this->newMessage('success', 'Location changed', $res);
        }
        $this->em()->persist($location);

        return $this->handleResponse($res);
    }

    /**
     * Remove location
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Locations",
     *  description="Locations.remove",
     *  requirements = {
     *		{"name"="locationId", "required"="true", "dateType"="Integer", "description"="Location ID"}
     *  }
     * )
     */
    public function removeAction($locationId) {
        $location = $this->getLocation($locationId);

        if (!is_null($location->getMatch())) {
            $this->throwError('Location '. $location->getName() .' currently has match '. $location->getMatch()->getLocalId() .' playing, and can therefore not be removed.', self::$ERROR_BAD_REQUEST);
        }

        $res = 'removed location '. $location->getName();
        $this->newMessage('success', 'Location removed', $res);
        $this->em()->remove($location);

        return $this->handleResponse($res);
    }

    /**
     * Change a location for on hold
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Locations",
     *  description="Locations.setOnHold",
     *  requirements = {
     *     {"name"="locationId", "dataType"="Integer", "description"="Location ID"}
     *  },
     *  filters = {
     *		{"name"="onHold", "required"="true", "type"="boolean", "description"="Set location to on hold or not", "pattern"="(true|false|toggle)"},
     *		{"name"="nonreadyReason", "required"="false", "type"="string", "description"="Optional reason for why location is not ready. Only applied when onHold==true"}
     *  }
     * )
     */
    public function setOnHoldAction($locationId) {
        $location = $this->getLocation($locationId);

        if ($this->getParam('onHold') == "toggle") {
            $setOnHold = $location->getOnHold() == false;
        } else {
            $setOnHold = $this->getParam('onHold') == "true";
        }

        $location->setOnHold($setOnHold);
        if ($setOnHold) {
            $res = 'set location '. $location->getName() .' on hold';

            // apply optional non-ready reason
            $nonreadyReason = $this->getParam('nonreadyReason', false, null);
            if ($nonreadyReason != null) {
                $location->setNonreadyReason($nonreadyReason);
                $res .= " with reason ". $nonreadyReason;
            }

            $this->newMessage('success', 'Location on hold', $res);
        } else {
            $res = 'set location '. $location->getName() .' ready';

            // remove non-ready reason
            $location->setNonreadyReason(null);

            $this->newMessage('success', 'Location ready', $res);
        }

        return $this->handleResponse($res);
    }


    // get data of all locations
    private function getLocationsData() {
        $res = array();

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Location');
        $locations = $repository->getBySortableGroups(array('tournament'=>$this->tournament));

        foreach ($locations as $location) { /* @var \TS\ApiBundle\Entity\Location $location */
            $locationArray = array(
                'id' => $location->getId(),
                'name' => $location->getName(),
                'onHold' => $location->getOnHold(),
                'nonreadyReason' => $location->getNonreadyReason(),
                'position' => $location->getPosition(),
            );
            $res[] = $locationArray;
        }

        return $res;
    }
}