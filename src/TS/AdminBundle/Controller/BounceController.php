<?php

namespace TS\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BounceController extends Controller
{
    /**
     * Remove bounced email from database
     * This controlled is called by Mandrill SMTP server
     */
    public function bounceAction(Request $request)
    {
        $removedEmail = "";
        if ($request->getMethod() == "POST") {
            $postArray = json_decode(stripslashes($_POST['mandrill_events']), true);
            foreach ($postArray as $event) {

                if ($event['event'] == "hard_bounce") {
                    $bouncedEmail = $event['msg']['email'];

                    // remove bouncedEmail from database
                    $repository = $this->getDoctrine()
                        ->getRepository('TSApiBundle:Person');
                    $query = $repository->createQueryBuilder('p')
                        ->andWhere('p.email = :bouncedEmail')
                        ->setParameter('bouncedEmail', $bouncedEmail)
                        ->getQuery();
                    $person = $query->getOneOrNullResult(); /* @var \TS\ApiBundle\Entity\Person $person */
                    if ($person) {
                        $person->setEmail(null);
                        $this->getDoctrine()->getManager()->flush();

                        $removedEmail .= " ". $bouncedEmail ." (found)";
                    } else {
                        $removedEmail .= " ". $bouncedEmail ." (not found)";
                    }
                }
            }

            $mailText = "". print_r($postArray, true);
            $mailText .= " removed: ". $removedEmail;
            $this->mail($mailText);
        }


        return $this->render('TSAdminBundle:Bounce:bounce.html.twig', array(
            'email' => $removedEmail,
        ));
    }

    private function mail($text) {
        $message = \Swift_Message::newInstance()
            ->setSubject('Tournia bounce')
            ->setFrom("bounces@tournia.net")
            ->setTo('sjoerd@tournia.net')
            ->setBody($text)
        ;
        $this->get('mailer')->send($message);
    }
}
