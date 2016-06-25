<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TS\SettingsBundle\Form\Type\DisciplinesType;


class DisciplinesController extends MainController
{

    /**
     * Settings page for disciplines
     */
    public function disciplinesAction(Request $request) {
        $tournament = $this->tournament;
        $form = $this->createForm(new DisciplinesType(), $tournament, array('tournament'=>$this->tournament));

        if ($request->isMethod('POST')) {
            // Create an array of the current tournament disciplines
            $originalTournamentDisciplines = array();
            foreach ($tournament->getDisciplines() as $discipline) {
                $originalTournamentDisciplines[] = $discipline;
            }
            // and also for tournament disciplineTypes
            $originalTournamentDisciplineTypes = array();
            foreach ($tournament->getDisciplineTypes() as $disciplineType) {
                $originalTournamentDisciplineTypes[] = $disciplineType;
            }

            $form->handleRequest($request);

            if (!$form->isValid()) {
                $flashMessage = $this->get('translator')->trans('flash.form.error', array(), 'settings');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
            } else {
                $em = $this->getDoctrine()->getManager();

                // Set tournament in Discipline entity
                foreach ($tournament->getDisciplines() as $discipline) {
                    if (is_null($discipline->getTournament())) {
                        $discipline->setTournament($tournament);
                    }
                }
                foreach ($tournament->getDisciplineTypes() as $disciplineType) {
                    if (is_null($disciplineType->getTournament())) {
                        $disciplineType->setTournament($tournament);
                    }
                }

                // find disciplines that are no longer present
                foreach ($tournament->getDisciplines() as $discipline) {
                    foreach ($originalTournamentDisciplines as $key => $toDel) {
                        if ($toDel->getId() === $discipline->getId()) {
                            unset($originalTournamentDisciplines[$key]);
                        }
                    }
                }
                // remove the deleted disciplines
                foreach ($originalTournamentDisciplines as $discipline) {
                    if ($discipline->getPlayers()->isEmpty()) {
                        $em->remove($discipline);
                    } else {
                        $flashMessage = $this->get('translator')->trans('flash.discipline.delete.error', array('%name%'=>$discipline->getName()), 'settings');
                        $this->get('session')->getFlashBag()->add('error', $flashMessage);
                    }
                }

                // find discipline types that are no longer present
                foreach ($tournament->getDisciplineTypes() as $disciplineType) {
                    foreach ($originalTournamentDisciplineTypes as $key => $toDel) {
                        if ($toDel->getId() === $disciplineType->getId()) {
                            unset($originalTournamentDisciplineTypes[$key]);
                        }
                    }
                }
                // remove the deleted disciplineTypes
                foreach ($originalTournamentDisciplineTypes as $disciplineType) {
                    if ($disciplineType->getDisciplines()->isEmpty()) {
                        $em->remove($disciplineType);
                    } else {
                        $flashMessage = $this->get('translator')->trans('flash.disciplineType.delete.error', array('%name%'=>$disciplineType->getName()), 'settings');
                        $this->get('session')->getFlashBag()->add('error', $flashMessage);
                    }
                }

                // saving the tournament to the database
                $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.discipline.saved', array(), 'settings');
                $this->get('session')->getFlashBag()->add('success', $flashMessage);

                // workaround: when changing order of disciplines, this is not immediately displayed in the form; on a page reload it is
                return $this->redirect($this->generateUrl('settings_disciplines', array('tournamentUrl'=> $this->tournament->getUrl())));
            }
        }

        return $this->render('TSSettingsBundle:Disciplines:disciplines.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
