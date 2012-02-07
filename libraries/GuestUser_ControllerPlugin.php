<?php


class GuestUser_ControllerPlugin extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_preventAdminAccess($request);

    }

    protected function _preventAdminAccess($request)
    {
        $user = Omeka_Context::getInstance()->getCurrentUser();
        // If we're logged in, then prevent access to the admin for MyOmeka users
        if ($user && $user->role == 'guest' && is_admin_theme()) {
            //$url = uri('guest-user/user/me');
            $this->getRedirect()->gotoUrl(WEB_ROOT . '/guest-user/user/me');
        }
    }


    protected function getRedirect()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    }
}


?>
