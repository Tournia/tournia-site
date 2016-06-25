<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpKernel\Exception\HttpException;
use TS\ApiBundle\Model\InconsistenciesModel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InconsistenciesController extends ApiV2MainController
{


    /**
     * Get list of inconsistencies for a tournament
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Inconsistencies",
     *  description="Inconsistencies.list"
     * )
     */
    public function listAction() {
        $inconsistenciesModel = new InconsistenciesModel($this->getDoctrine());
        $resArray = $inconsistenciesModel->getAllInconsistencies($this->tournament);
        return $this->handleResponse($resArray);
    }

    /**
     * Get list of players which are registered for a discipline, but not in the connected pool
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Inconsistencies",
     *  description="Inconsistencies.playerRegisteredNotInPool"
     * )
     */
    public function playerRegisteredNotInPoolAction() {
        $poolId = $this->getParam('poolId', false, null);
        $pool = (!empty($poolId)) ? $this->getPool($poolId) : null;

        $inconsistenciesModel = new InconsistenciesModel($this->getDoctrine());
        $resArray = $inconsistenciesModel->getPlayersRegisteredNotInPool($this->tournament, $pool);
        return $this->handleResponse($resArray);
    }

}