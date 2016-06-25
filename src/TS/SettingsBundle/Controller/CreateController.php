<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use TS\ApiBundle\Entity\Discipline;
use TS\ApiBundle\Entity\DisciplineType;
use TS\ApiBundle\Entity\Tournament;
use TS\SettingsBundle\Form\Type\CreateTournamentType;
use TS\SiteBundle\Entity\Site;
use TS\SiteBundle\Entity\SitePage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TS\NotificationBundle\NotificationEvents;
use TS\NotificationBundle\Event\TournamentEvent;

class CreateController extends Controller
{

    /** @var \TS\ApiBundle\Entity\Tournament $tournament */
    private $tournament;

    /**
     * Settings page for creating a new tournament
     */
    public function createAction(Request $request) {
        if (!is_object($this->getUser())) {
            // you need to be logged in to create tournament
            throw new AccessDeniedException();
        }

        $this->tournament = new Tournament();

        // check if tournament settings have to be copied
        $copyTournament = null;
        if ($request->query->has("copy")) {
            $copyTournamentId = $request->query->get("copy");
            /* @var \TS\ApiBundle\Entity\Tournament $copyTournament */
            $copyTournament = $this->getDoctrine()
                ->getRepository('TSApiBundle:Tournament')
                ->find($copyTournamentId);
            if (false === $this->get('security.context')->isGranted("EDIT", $copyTournament)) {
                $copyTournament = null;
            }
        }
        if (!is_null($copyTournament)) {
            // copy tournament settings
            $copiedTournament = $this->getDoctrine()
                ->getRepository('TSApiBundle:Tournament')
                ->cloneTournament($copyTournament);
            $this->tournament = $copiedTournament;
        } else {
            // Add default data to new tournament
            $this->tournament->addOrganizerPerson($this->getUser()->getPerson());
            $this->tournament->setEmailFrom($this->getUser()->getPerson()->getEmail());
            $this->tournament->setContactName($this->getUser()->getPerson()->getName());

            $this->tournamentSite = new Site();
            $this->tournamentSite->setTournament($this->tournament);
            $this->tournamentSite->setLocationAddress("");
            $this->tournament->setSite($this->tournamentSite);

            $sitePage = new SitePage();
            $sitePage->setSite($this->tournamentSite);
            $sitePage->setUrl('index');
            $sitePage->setHtml('More info will follow soon...');
            $sitePage->setTitle('Index');
            $sitePage->setShowInfoBlock(true);
            $this->tournamentSite->addSitePage($sitePage);

            // create disciplines
            $this->createDisciplines($this->tournament);
        }

        $tournamentName = $this->get('session')->get('tournamentName');
        $this->tournament->setName($tournamentName);
        $this->tournament->setUrl($this->slugify($tournamentName));

        // Create form
        $form = $this->createForm(new CreateTournamentType(), $this->tournament, array('person'=>$this->getUser()->getPerson()));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $locationAddress = (is_null($form->get('locationAddress')->getData())) ? '' : $form->get('locationAddress')->getData();
            $this->tournament->getSite()->setLocationAddress($locationAddress);

            if ($form->isValid()) {
                $em = $this->getDoctrine()
                    ->getManager();
                $em->persist($this->tournament);
                $em->flush();

                $event = new TournamentEvent($this->tournament);
                $this->get('event_dispatcher')->dispatch(NotificationEvents::TOURNAMENT_NEW, $event);

                $flashMessage = $this->get('translator')->trans('flash.createTournament.success', array(), 'settings');
                $this->get('session')->getFlashBag()->add('info', $flashMessage);
                return $this->redirect($this->generateUrl('settings_index', array('tournamentUrl'=> $this->tournament->getUrl())));
            }
        }

        return $this->render('TSSettingsBundle:Create:create.html.twig', array(
            'tournamentName' => $tournamentName,
            'form' => $form->createView(),
            'copy' => $request->query->get("copy")
        ));
    }

    /**
     * Create disciplines for a new tournament
     * @param \TS|ApiBundle\Entity\Tournament $tournament
     */
    private function createDisciplines(&$tournament) {
        // create singles/doubles/mixed type
        $singlesDisciplineType = new DisciplineType();
        $singlesDisciplineType->setTournament($tournament);
        $singlesDisciplineType->setName("Singles");
        $this->tournament->addDisciplineType($singlesDisciplineType);
        $doublesDisciplineType = new DisciplineType();
        $doublesDisciplineType->setTournament($tournament);
        $doublesDisciplineType->setName("Doubles");
        $this->tournament->addDisciplineType($doublesDisciplineType);
        $mixedDisciplineType = new DisciplineType();
        $mixedDisciplineType->setTournament($tournament);
        $mixedDisciplineType->setName("Mixed");
        $this->tournament->addDisciplineType($mixedDisciplineType);

        // men singles
        $discipline1 = new Discipline();
        $discipline1->setTournament($this->tournament);
        $discipline1->setName("Men Singles");
        $discipline1->setGender("M");
        $discipline1->setDisciplineType($singlesDisciplineType);
        $this->tournament->addDiscipline($discipline1);

        // ladies singles
        $discipline2 = new Discipline();
        $discipline2->setTournament($this->tournament);
        $discipline2->setName("Ladies Singles");
        $discipline2->setGender("F");
        $discipline1->setDisciplineType($singlesDisciplineType);
        $this->tournament->addDiscipline($discipline2);

        // men doubles
        $discipline3 = new Discipline();
        $discipline3->setTournament($this->tournament);
        $discipline3->setName("Men Doubles");
        $discipline3->setGender("M");
        $discipline1->setDisciplineType($doublesDisciplineType);
        $this->tournament->addDiscipline($discipline3);

        // ladies doubles
        $discipline4 = new Discipline();
        $discipline4->setTournament($this->tournament);
        $discipline4->setName("Ladies Doubles");
        $discipline4->setGender("F");
        $discipline1->setDisciplineType($doublesDisciplineType);
        $this->tournament->addDiscipline($discipline4);

        // mixed doubles
        $discipline5 = new Discipline();
        $discipline5->setTournament($this->tournament);
        $discipline5->setName("Mixed Doubles");
        $discipline5->setGender("B");
        $discipline1->setDisciplineType($mixedDisciplineType);
        $this->tournament->addDiscipline($discipline5);
    }

    private function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }
}
