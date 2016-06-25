<?php

namespace TS\AccountBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use TS\AccountBundle\Form\Type\ProfileType;

class SettingsController extends MainController
{

    /**
     * Show invoices of person
     */
    public function financialAction(Request $request)
    {
        $invoiceRepository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:Invoice');

        $personInvoices = $invoiceRepository->getPersonInvoices($this->person);
        $tournamentInvoices = $invoiceRepository->getTournamentInvoices($this->person);

        return $this->render('TSAccountBundle:Settings:financial.html.twig', array(
            'personInvoices' => $personInvoices,
            'tournamentInvoices' => $tournamentInvoices,
        ));
    }

    /**
     * Show tournaments of person
     */
    public function mytournamentsAction() {
        $playerRepository = $this->getDoctrine()->getRepository('TSApiBundle:Player');
        $query = $playerRepository->createQueryBuilder('player')
            ->select('player')
            ->join("player.tournament", "tournament")
            ->andWhere('player.person = :person')
            ->setParameter('person', $this->person)
            ->orderBy('tournament.id', "DESC");
        $players = $query->getQuery()->getResult();

        return $this->render('TSAccountBundle:Settings:mytournaments.html.twig', array(
            'players' => $players,
        ));
    }

    /**
     * Edit the Person profile
     */
    public function profileAction(Request $request)
    {
        $form    = $this->createForm(new ProfileType(), $this->person);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()
                    ->getManager();
                $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.settings.profile.saved', array(), 'account');
                $this->get('session')->getFlashBag()->add('success', $flashMessage);
            }
        }

        return $this->render('TSAccountBundle:Settings:profile.html.twig', array(
            'form'    => $form->createView()
        ));
    }
}
