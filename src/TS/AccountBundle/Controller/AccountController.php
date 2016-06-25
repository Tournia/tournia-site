<?php

namespace TS\AccountBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use TS\AccountBundle\Form\Type\AccountType;
use TS\AccountBundle\Model\AuthorizationModel;

class AccountController extends MainController
{

    /**
     * Display the loginAccounts
     */
    public function accountAction(Request $request)
    {
        $emailLoginAccount = null;
        $facebookLoginAccount = null;
        $googleLoginAccount = null;
        foreach ($this->person->getLoginAccounts() as $loginAccount) {
            /* @var \TS\ApiBundle\Entity\LoginAccount $loginAccount */
            if ($loginAccount->getMethod() == "email") {
                $emailLoginAccount = $loginAccount;
            } else if ($loginAccount->getMethod() == "facebook") {
                $facebookLoginAccount = $loginAccount;
            } else if ($loginAccount->getMethod() == "google") {
                $googleLoginAccount = $loginAccount;
            }
        }

        if (!is_null($emailLoginAccount)) {
            // create form with email LoginAcount, to be able to change username
            $form = $this->createForm(new AccountType(), $emailLoginAccount);
        } else {
            $form = $this->createForm(new AccountType());
            $form->remove('username');
        }

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // check for changed username

                $em = $this->getDoctrine()
                    ->getManager();
                $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.account.saved', array(), 'account');
                $this->get('session')->getFlashBag()->add('success', $flashMessage);
            }
        }

        return $this->render('TSAccountBundle:Account:account.html.twig', array(
            'form' => $form->createView(),
            'emailLoginAccount' => $emailLoginAccount,
            'facebookLoginAccount' => $facebookLoginAccount,
            'googleLoginAccount' => $googleLoginAccount,
        ));
    }

    /**
     * Add email LoginAccount
     */
    public function addAccountAction(Request $request)
    {
        if (empty($this->person->getEmail())) {
            $profileLink = $this->generateUrl('account_settings_profile');
            $flashMessage = $this->get('translator')->trans('flash.account.addAccount.error', array('%startLink%'=>'<a href="'. $profileLink .'">', '%endLink%'=>'</a>'), 'account');
            $this->get('session')->getFlashBag()->add('error', $flashMessage);
        } else {
            $model = new AuthorizationModel($this->container);
            $model->createEmailLoginAccountForPerson($this->person);
            $flashMessage = $this->get('translator')->trans('flash.account.addAccount.success', array('%email%'=>$this->person->getEmail()), 'account');
            $this->get('session')->getFlashBag()->add('success', $flashMessage);
        }

        return $this->redirect($this->generateUrl('account_settings_account'));
    }

    /**
     * Remove loginAccount
     */
    public function removeAccountAction($loginAccountId, Request $request)
    {
        $loginAccount = $this->getDoctrine()
            ->getRepository('TSApiBundle:LoginAccount')
            ->findOneBy(array('id' => $loginAccountId, 'person' => $this->getUser()->getPerson()));
        if (!$loginAccount) {
            $flashMessage = $this->get('translator')->trans('flash.account.removeAccount.error', array(), 'account');
            $this->get('session')->getFlashBag()->add('error', $flashMessage);
            return $this->redirect($this->generateUrl('front_index'));
        }

        $em = $this->getDoctrine()->getManager();
        $loginAccount->getPerson()->removeLoginAccount($loginAccount);
        $em->remove($loginAccount);
        $em->flush();

        $flashMessage = $this->get('translator')->trans('flash.account.removeAccount.success', array(), 'account');
        $this->get('session')->getFlashBag()->add('success', $flashMessage);
        return $this->redirect($this->generateUrl('account_settings_account'));
    }


}
