<?php
namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use TS\ApiBundle\Controller\v2\ApiV2MainController;


class MessagesController extends ApiV2MainController
{

    /**
     * Get list of messages, for a specific page number
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Messages",
     *  description="Messages.list",
     *  requirements = {
     *		{"name"="pageNr", "dataType"="Integer", "description"="Page number"},
     *  }
     * )
     */
    public function listAction($pageNr) {
        $messagesData = $this->getMessagesData($pageNr);
        $res = array(
            'messages' => $messagesData,
            'nrPages' => ceil(sizeof($this->tournament->getUpdateMessages()) / 50),
        );

        return $this->handleResponse($res);
    }


    // get data of all messages at a certain page
    private function getMessagesData($pageNr) {
        $res = array();

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:UpdateMessage');
        $query = $repository->createQueryBuilder('um')
            ->andWhere('um.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->orderBy('um.datetime', 'DESC')
            ->getQuery();
        $messages = $query
            ->setMaxResults(50)
            ->setFirstResult(50*($pageNr-1))
            ->getResult();

        foreach ($messages as $message) {
            $personName = (is_object($message->getLoginAccount())) ? $message->getLoginAccount()->getPerson()->getName() : "Unknown";
            $messageArray = array(
                'id' => $message->getId(),
                'text' => $message->getText(),
                'datetime' => $message->getDatetime()->format("d-M-Y H:i:s"),
                'person' => $personName,
                'type' => $message->getType(),
                'title' => $message->getTitle()
            );
            $res[] = $messageArray;
        }

        return $res;
    }
}