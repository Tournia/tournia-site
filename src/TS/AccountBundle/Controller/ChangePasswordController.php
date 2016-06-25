<?php

namespace TS\AccountBundle\Controller;

use FOS\UserBundle\Controller\ChangePasswordController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;

class ChangePasswordController extends BaseController
{

    /**
     * Change password by forwarding request to FOSUserBundle:ChangePassword:changePassword
     */
    public function changePasswordAction(Request $request) {
        $currentLoginAccount = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($currentLoginAccount) || !$currentLoginAccount instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $person = $currentLoginAccount->getPerson();
        // find Email LoginAccount for Person
        $emailLoginAccount = $this->container->get('doctrine')
            ->getRepository('TSApiBundle:LoginAccount')
            ->findOneBy(array('method' => 'email', 'person' => $person));
        if (!$emailLoginAccount) {
            $flashMessage = $this->get('translator')->trans('flash.changePassword.noEmailAccount', array(), 'account');
            $this->get('session')->getFlashBag()->add('error', $flashMessage);
            return $this->redirect($this->generateUrl('account_settings_account'));
        }

        // set LoginAccount to email LoginAccount, otherwise password cannot be changed
        $this->container->get('security.context')->getToken()->setUser($emailLoginAccount);
        $response = parent::changePasswordAction($request);
        $this->container->get('security.context')->getToken()->setUser($currentLoginAccount);

        return $response;
    }


}
