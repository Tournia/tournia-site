<?php

namespace TS\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sylius\Bundle\OrderBundle\Model\OrderInterface;


class PersonsController extends MainController
{


    /**
     * Show persons
     */
    public function overviewAction(Request $request)
    {
        $this->checkAccess();

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Person');
        $persons = $repository->findAll();
        return $this->render('TSAdminBundle:Persons:overview.html.twig', array(
            'persons' => $persons,
        ));
    }

    /**
     * Merge persons
     */
    public function mergeAction(Request $request)
    {
        $this->checkAccess();



        $form = $this->createFormBuilder()
            ->add('person1', 'integer', array(
                'label' => 'Person 1 ID'
            ))
            ->add('person2', 'integer', array(
                'label' => 'Person 2 ID (removed)'
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $person1 =

                $personRepository = $this->getDoctrine()
                    ->getRepository('TSApiBundle:Person');

                $person1Id = $form->get('person1')->getData();
                $person1 = $personRepository->find($person1Id);
                if (!$person1) {
                    $this->get('session')->getFlashBag()->add('error', 'Person 1 with ID '. $person1Id .' not found');
                }
                $person2Id = $form->get('person2')->getData();
                $person2 = $personRepository->find($person2Id);
                if (!$person2) {
                    $this->get('session')->getFlashBag()->add('error', 'Person 2 with ID '. $person2Id .' not found');
                }
                if ($person1 && $person2) {
                    // merge persons
                    $this->get('session')->getFlashBag()->add('success', 'Merged '. $person1->getName() .' ['. $person1->getId() .'] with '. $person2->getName() .' ['. $person2->getId() .']');
                    $personRepository->mergePersons($person2, $person1);
                }
            }
        }

        return $this->render('TSAdminBundle:Persons:merge.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
