<?php

class GuestUser_UserController extends Omeka_Controller_AbstractActionController
{

    public function loginAction()
    {
        $session = new Zend_Session_Namespace;
        if(!$session->redirect) {
            $session->redirect = $_SERVER['HTTP_REFERER'];
        }
        
        $this->redirect('users/login');
    }

    public function registerAction()
    {
        
        if(current_user()) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
        $openRegistration = (get_option('guest_user_open') == 'on');
        $instantAccess = (get_option('guest_user_instant_access') == 'on');
        $user = new User();

        $form = $this->_getForm(array('user'=>$user));
        $form->setSubmitButtonText('Register');
        $this->view->form = $form;

        if (!$this->getRequest()->isPost() || !$form->isValid($_POST)) {
            return;
        }
        $user->role = 'guest';
        if($openRegistration || $instantAccess) {
            $user->active = true;
        }
        $user->setPassword($_POST['new_password']);
        $user->setPostData($_POST);
        try {
            if ($user->save($_POST)) {
                $token = $this->_createToken($user);
                $this->_sendConfirmationEmail($user, $token); //confirms that they registration request is legit
                if($instantAccess) {
                    //log them right in, and return them to the previous page
                    $authAdapter = new Omeka_Auth_Adapter_UserTable($this->_helper->db->getDb());
                    $authAdapter->setIdentity($user->username)->setCredential($_POST['new_password']);                    
                    $authResult = $this->_auth->authenticate($authAdapter);
                    if (!$authResult->isValid()) {
                        if ($log = $this->_getLog()) {
                            $ip = $this->getRequest()->getClientIp();
                            $log->info("Failed login attempt from '$ip'.");
                        }
                        $this->_helper->flashMessenger($this->getLoginErrorMessages($authResult), 'error');
                        return;
                    }             
                    $activation = UsersActivations::factory($user);
                    $activation->save();
                    $this->_helper->flashMessenger(__("You are logged in temporarily. Please check your email for a confirmation message. Omce you have confirmed your request, you can log in."));
                    $this->redirect($_SERVER['HTTP_REFERER']);
                    return;
                }
                if($openRegistration) {
                    $message = "Thank you for registering. Please check your email for a confirmation message. Once you have confirmed your request, you will be able to log in.";
                    $this->_helper->flashMessenger($message, 'success');
                    $activation = UsersActivations::factory($user);
                    $activation->save();
                    
                } else {
                    $message = "Thank you for registering. Please check your email for a confirmation message. Once you have confirmed your request and an administrator activates your account, you will be able to log in.";
                    $this->_helper->flashMessenger($message, 'success');
                }
            }
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        }
    }

    public function updateAccountAction()
    {
        $user = current_user();
        
        $form = $this->_getForm(array('user'=>$user));
        $form->getElement('new_password')->setLabel(__("New Password"));
        $form->getElement('new_password')->setRequired(false);
        $form->getElement('new_password_confirm')->setRequired(false);
        $form->addElement('password', 'current_password',
                        array(
                                'label'         => __('Current Password'),
                                'required'      => true,
                                'class'         => 'textinput',
                        )
        );        
        
        $oldPassword = $form->getElement('current_password');
        $oldPassword->setOrder(0);
        $form->addElement($oldPassword);
        
        //$form->removeElement('new_password_confirm');
        $form->setSubmitButtonText('Update');
        $form->setDefaults($user->toArray());
        $this->view->form = $form;
        
        if (!$this->getRequest()->isPost() || !$form->isValid($_POST)) {
            return;
        }  
        
        if($user->password != $user->hashPassword($_POST['current_password'])) {
            $this->_helper->flashMessenger(__("Incorrect password"), 'error');
            return;
        }
        
        $user->setPassword($_POST['new_password']);
        $user->setPostData($_POST);
        try {
            $user->save($_POST);
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        }              
    }
    
    public function meAction()
    {
        $user = current_user();
        if(!$user) {
            $this->redirect('/');
        }
        $widgets = array();
        $widgets = apply_filters('guest_user_widgets', $widgets);
        $this->view->widgets = $widgets;
    }

    public function staleTokenAction() 
    {
        $auth = $this->getInvokeArg('bootstrap')->getResource('Auth');
        //http://framework.zend.com/manual/en/zend.auth.html
        $auth->clearIdentity();
        //$_SESSION = array();
        //Zend_Session::destroy();
        
    }
    
    public function confirmAction()
    {
        $db = get_db();
        $token = $this->getRequest()->getParam('token');
        $records = $db->getTable('GuestUserToken')->findBy(array('token'=>$token));
        $record = $records[0];
        if($record) {
            $record->confirmed = true;
            $record->save();
            $user = $db->getTable('User')->find($record->user_id);
            $this->_sendAdminNewConfirmedUserEmail($user);
            $this->_sendConfirmedEmail($user);
            $message = "Please check the email we just sent you for the next steps! You're almost there!";
            $this->_helper->flashMessenger($message, 'success');
            $this->redirect('users/login');
        } else {
            $this->_helper->flashMessenger('Invalid token', 'error');
        }
    }

    protected function _getForm($options)
    {
        $form = new Omeka_Form_User($options);
        //need to remove submit so I can add in new elements
        $form->removeElement('submit');
        $form->addElement('password', 'new_password',
            array(
                    'label'         => __('Password'),
                    'required'      => true,
                    'class'         => 'textinput',
                    'validators'    => array(
                        array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => 
                            array(
                                'messages' => array(
                                    'isEmpty' => __("New password must be entered.")
                                )
                            )
                        ),
                        array(
                            'validator' => 'Confirmation', 
                            'options'   => array(
                                'field'     => 'new_password_confirm',
                                'messages'  => array(
                                    Omeka_Validate_Confirmation::NOT_MATCH => __('New password must be typed correctly twice.')
                                )
                             )
                        ),
                        array(
                            'validator' => 'StringLength',
                            'options'   => array(
                                'min' => User::PASSWORD_MIN_LENGTH,
                                'messages' => array(
                                    Zend_Validate_StringLength::TOO_SHORT => __("New password must be at least %min% characters long.")
                                )
                            )
                        )
                    )
            )
        );
        $form->addElement('password', 'new_password_confirm',
                        array(
                                'label'         => 'Password again for match',
                                'required'      => true,
                                'class'         => 'textinput',
                                'errorMessages' => array(__('New password must be typed correctly twice.'))
                        )
        );
        if(Omeka_Captcha::isConfigured() && (get_option('guest_user_recaptcha') == 'on')) {
            $form->addElement('captcha', 'captcha',  array(
                'class' => 'hidden',
                'style' => 'display: none;',
                'label' => "Please verify you're a human",
                'type' => 'hidden',
                'captcha' => Omeka_Captcha::getCaptcha()
            ));
        }
        $form->addElement('submit', 'submit', array('label' => 'Register'));
        return $form;        
    }
    
    protected function _sendConfirmedEmail($user)
    {
        $siteTitle = get_option('site_title');
        $body = "Thanks for joining $siteTitle!";
        if(get_option('guest_user_open') == 'on') {
            $body .= "\n\n You can now log in using the password you chose.";
        } else {
            $body .= "\n\n When an administrator approves your account, you will receive another message that you" .
                    "can log in with the password you chose.";
        }
        $subject = "Registration for $siteTitle";
        $mail = $this->_getMail($user, $body, $subject);
        try {
            $mail->send();
        } catch (Exception $e) {
            _log($e);
            _log($body);
        }

    }

    protected function _sendConfirmationEmail($user, $token)
    {
        $siteTitle = get_option('site_title');
        $url = WEB_ROOT . '/guest-user/user/confirm/token/' . $token->token;
        $subject = "Your request to join $siteTitle";
        $body = "You have registered for an account on $siteTitle. Please confirm your registration by following this link: $url If you did not request to join $siteTitle please disregard this email.";
        $mail = $this->_getMail($user, $body, $subject);
        try {
            $mail->send();
        } catch (Exception $e) {
            _log($e);
            _log($body);
        }
    }

    protected function _sendAdminNewConfirmedUserEmail($user)
    {
        $siteTitle = get_option('site_title');
        $url = WEB_ROOT . "/admin/users/edit/" . $user->id;
        $subject = "New request to join $siteTitle";
        $body = "A new user has confirmed that they want to join $siteTitle.  ";
        $body .= "\n\n<a href='$url'>" . $user->username . "</a>";
        $mail = $this->_getMail($user, $body, $subject);
        $mail->clearRecipients();
        $mail->addTo(get_option('administrator_email'), "$siteTitle Administrator");
         try {
            $mail->send();
        } catch (Exception $e) {
            _log($body);
        }
    }

    protected function _getMail($user, $body, $subject)
    {    
        $siteTitle  = get_option('site_title');
        $from = get_option('administrator_email');
        $mail = new Zend_Mail();
        $mail->setBodyText($body);
        $mail->setFrom($from, "$siteTitle Administrator");
        $mail->addTo($user->email, $user->name);
        $mail->setSubject($subject);
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        return $mail;
    }

    protected function _createToken($user)
    {
        $token = new GuestUserToken();
        $token->user_id = $user->id;
        $token->token = sha1("tOkenS@1t" . microtime());
        if(method_exists($user, 'getEntity')) {
            $token->email = $user->getEntity()->email;
        } else {
            $token->email = $user->email;
        }

        $token->save();
        return $token;
    }
}

