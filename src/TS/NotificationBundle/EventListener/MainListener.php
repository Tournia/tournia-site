<?php

namespace TS\NotificationBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TS\ApiBundle\Entity\Person;

abstract class MainListener extends Controller
{

    protected $container;

    /**
     * Send an email
     * @param string $txtTemplateName Location of Twig template
     * @param array $templateArray keys and values that are passed to the Twig template
     * @param mixed $to Can be a Person object, array (email address=>name) or string
     * @param \TS\ApiBundle\Entity\Tournament $tournament Optional tournament, which is used in the mail template
     * @param mixed $cc Can be array (email address=>name) or string email address
     * @param mixed $bcc Can be array (email address=>name) or string email address
     * @param boolean $replyToTournament Whether the reply email address is from the tournament organization
     */
    protected function sendEmail($templateName, $templateArray = array(), $to, $tournament = null, $cc = null, $bcc = null, $replyToTournament = false) {
        if ($to instanceof Person) {
            $templateArray['person'] = $to;
            if (!empty($to->getEmail())) {
                $to = $to->getEmail();
            }
        }
        $templateArray['tournament'] = $tournament;

        $twig = $this->container->get('twig');
        $twigContext = $twig->mergeGlobals($templateArray);
        $template = $twig->loadTemplate($templateName);
        $subject = $template->renderBlock('mailSubject', $twigContext);

        // add logo image to html
        $message = \Swift_Message::newInstance();
        $htmlLogoImg = $message->embed(\Swift_Image::fromPath(__DIR__ . '/../Resources/public/images/logo_text.png'));
        $templateArray['htmlLogoImg'] = $htmlLogoImg;
        $templateArray['htmlTitle'] = $subject;

        $twigContext = $twig->mergeGlobals($templateArray);
        $mailText = $template->renderBlock('mailText', $twigContext);
        $mailHtml = $template->renderBlock('mailHtml', $twigContext);

        // prevent empty email address
        if (is_array($to) && reset($to) && empty(key($to))) {
            $to = null;
        }
        if (is_array($cc) && reset($cc) && empty(key($cc))) {
            $cc = null;
        }
        if (is_array($bcc) && reset($bcc) && empty(key($bcc))) {
            $bcc = null;
        }

        if (empty($to) && empty($cc) && empty($bcc)) {
            // nothing to email
            return;
        }

        if (empty($to)) {
            // move cc to to
            $to = $cc;
            $cc = null;
        }

        // generate and send mail
        $message = $message
            ->setFrom($this->container->getParameter('email_from_email'))
            ->setReturnPath($this->container->getParameter('email_bounces'))
            ->setSubject($subject)
            ->setTo($to)
            ->setCc($cc)
            ->setBcc($bcc)
            ->setContentType('text/html')
            ->setBody($mailHtml)
            ->addPart($mailText, 'text/plain');
        if ($replyToTournament && !empty($tournament)) {
            $message->setReplyTo(array($tournament->getEmailFrom() => $tournament->getContactName()));
        }
        $this->container->get('mailer')->send($message);
    }

    /**
     * Generates url from the router service
     * @param string $routingName
     * @param array $routingParams
     */
    protected function getUrl($routingName, $routingParams=array()) {
        return $this->container->get('router')->generate($routingName, $routingParams, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
