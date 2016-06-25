<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use TS\SiteBundle\Entity\File;
use TS\SettingsBundle\Form\Type\SiteType;
use TS\SettingsBundle\Form\Type\OrganizerEmailType;
use Symfony\Component\Form\FormError;


class SiteController extends MainController
{
    
    /**
	 * Edit tournament information
	 */
    public function editAction(Request $request) {
	    $site = $this->tournament->getSite();
	    
    	$form = $this->createForm(new SiteType(), $site, array(
            'em'=>$this->getDoctrine()->getManager(),
            'site'=>$site)
        );
        
        if ($request->isMethod('POST')) {
        	// Create an array of the current SitePages objects in the database (to make deletion possible)
        	$originalSitePages = array();
		    foreach ($site->getSitePages() as $page) {
		        $originalSitePages[] = $page;
		    }

       		$form->handleRequest($request);

       		if (sizeof($site->getSitePages()) == 0) {
       			$form->get('sitePages')->addError(new FormError('At least one page must exist'));
       		}

	        if (!$form->isValid()) {
                $flashMessage = $this->get('translator')->trans('flash.form.error', array(), 'settings');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
	        } else {
	        	$em = $this->getDoctrine()->getManager();
	        	
	        	// uploading files
	        	$uploadFile = $form['upload']->getData();
	        	if ($uploadFile != null) {
	        		$file = new File();
	        		$file->upload($uploadFile, $site);
	        		$em->persist($file);
	        		$site->addFile($file);
	        	}

	        	// Set site in entity SitePage
	        	foreach ($site->getSitePages() as $page) {
	        		if (is_null($page->getSite())) {
	        			$page->setSite($site);
	        		}
	        	}

	        	// find website pages that are no longer present
		        foreach ($site->getSitePages() as $page) {
		            foreach ($originalSitePages as $key => $toDel) {
		                if ($toDel->getId() === $page->getId()) {
		                    unset($originalSitePages[$key]);
		                }
		            }
		        }
		        // remove the deleted website pages
		        foreach ($originalSitePages as $page) {
		            $em->remove($page);
		        }
	        	
	            // saving the tournament to the database
			    $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.site.saved', array(), 'settings');
			    $this->get('session')->getFlashBag()->add('success', $flashMessage);
	        }
	    }

	    $specialFiles = $file = $this->getDoctrine()->getRepository('TSSiteBundle:File')->getAllSpecialFiles();
	    
	    $templateArray = array(
	        'form' => $form->createView(),
	        'specialFiles' => $specialFiles,
	    );

	    return $this->render('TSSettingsBundle:Site:site.html.twig', $templateArray);
	}
    
    /**
      * Delete a tournament file
      */
    public function deleteFileAction($fileId)
    {
        /** @var \TS\SiteBundle\Entity\File $file */
        $file = $this->getDoctrine()
        	->getRepository('TSSiteBundle:File')
        	->findOneBy(array('tournament'=>$this->tournament, 'id'=>$fileId));
        if (!$file) {
	        throw $this->createNotFoundException('No file found for id '.$fileId .' and tournament '. $this->tournament->getName());
	    }

        // Lookup reference to File, and remove these
		if ($file->getSite()->getFrontImage() == $file) {
			$file->getSite()->setFrontImage(null);
			$file->getSite()->setFrontImagePosition(null);
		}
        if ($file->getSite()->getHeaderBackgroundImage() == $file) {
            $file->getSite()->setHeaderBackgroundImage(null);
            $file->getSite()->setHeaderBackgroundImagePosition(null);
        }
        if ($file->getSite()->getInfoBlockImage() == $file) {
            $file->getSite()->setInfoBlockImage(null);
        }
		if ($file->getSite()->getFacebookImage() == $file) {
			$file->getSite()->setFacebookImage(null);
		}

	    
	    $em = $this->getDoctrine()->getManager();
	    $em->remove($file);
		$em->flush();

        $flashMessage = $this->get('translator')->trans('flash.file.deleted', array(), 'settings');
        $this->get('session')->getFlashBag()->add('success', $flashMessage);
	    return $this->redirect($this->generateUrl('settings_site', array('tournamentUrl'=>$this->tournament->getUrl())));
    }

    /**
     * Publish a site
     */
    public function publishAction() {
        $this->tournament->getSite()->setIsPublished(true);
        $this->getDoctrine()->getManager()->flush();

        $flashMessage = $this->get('translator')->trans('flash.site.published', array(), 'settings');
        $this->get('session')->getFlashBag()->add('success', $flashMessage);
        return $this->redirect($this->generateUrl('settings_site', array('tournamentUrl'=>$this->tournament->getUrl())));
    }
}