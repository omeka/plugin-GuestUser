<?php


class GuestUser_ControllerPlugin extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_preventAdminAccess($request);
    }

    protected function _preventAdminAccess($request)
    {
        $user = current_user();
        // If we're logged in, then prevent access to the admin for guest users
        if ($user && $user->role == 'guest' && is_admin_theme()) {
            $this->getRedirect()->gotoUrl(WEB_ROOT . '/guest-user/user/me');
        }
    }


    protected function getRedirect()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    }
}


?>
