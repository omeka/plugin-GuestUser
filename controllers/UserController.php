<?php

class GuestUser_UserController extends Omeka_Controller_Action
{

    public function loginAction()
    {
        $session = new Zend_Session_Namespace;
        $session->redirect = $_SERVER['HTTP_REFERER'];
        $this->_redirect('users/login');
    }

    public function registerAction()
    {
        if(current_user()) {
            $this->redirect->gotoUrl('/');
        }
        $openRegistration = (get_option('guest_user_open') == 'on');
        $user = new User();

        $form = new Omeka_Form_User(array('user'=>$user));
        $form->removeElement('submit');
        $form->removeElement('institution');
        $form->addElement('password', 'new_password',
            array(
                'label'         => 'New Password',
                'required'      => true,
                'class'         => 'textinput',
                'validators'    => array(
                    array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' =>
                        array(
                            'messages' => array(
                                'isEmpty' => Omeka_Form_ChangePassword::ERROR_NEW_PASSWORD_REQUIRED
                            )
                        )
                    ),
                    array(
                        'validator' => 'Confirmation',
                        'options'   => array(
                            'field'     => 'new_password_confirm',
                            'messages'  => array(
                                Omeka_Validate_Confirmation::NOT_MATCH => Omeka_Form_ChangePassword::ERROR_NEW_PASSWORD_CONFIRM_REQUIRED
                            )
                         )
                    ),
                    array(
                        'validator' => 'StringLength',
                        'options'   => array(
                            'min' => User::PASSWORD_MIN_LENGTH,
                            'messages' => array(
                                Zend_Validate_StringLength::TOO_SHORT => Omeka_Form_ChangePassword::ERROR_NEW_PASSWORD_TOO_SHORT
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
                'errorMessages' => array(Omeka_Form_ChangePassword::ERROR_NEW_PASSWORD_CONFIRM_REQUIRED)
            )
        );
        $form->addElement('submit', 'submit', array('label' => 'Register'));
        $form->setSubmitButtonText('Register');
        $this->view->form = $form;

        if (!$this->getRequest()->isPost() || !$form->isValid($_POST)) {
            return;
        }
        $user->role = 'guest';
        if($openRegistration) {
            $user->active = true;
        }
        $user->setPassword($_POST['new_password']);
        try {
            if ($user->saveForm($_POST)) {
                $token = $this->createToken($user);
                $this->sendConfirmationEmail($user, $token); //confirms that they registration request is legit
                if($openRegistration) {
                    $message = "Thank you for registering. Please check your email for a confirmation message. Once you have confirmed your request, you will be able to log in.";
                    $this->flashSuccess($message);
                } else {
                    $message = "Thank you for registering. Please check your email for a confirmation message. Once you have confirmed your request and an administrator activates your account, you will be able to log in.";
                    $this->flashSuccess($message);
                }
            }
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        }
    }

    public function meAction()
    {
        $user = current_user();
        if(!$user) {
            $this->redirect->gotoUrl('/');
        }
        $widgets = array();
        $widgets = apply_filters('guest_user_widgets', $widgets);
        $this->view->widgets = $widgets;
    }

    public function confirmAction()
    {
        $db = get_db();
        $token = $this->getRequest()->getParam('token');
        $record = $db->getTable('GuestUserToken')->findByToken($token);
        if($record) {
            $record->confirmed = true;
            $record->save();
            $user = $db->getTable('User')->find($record->user_id);
            $this->sendAdminNewConfirmedUserEmail($user);
            $this->sendConfirmedEmail($user);
            $this->flashSuccess("Please check the email we just sent you for the next steps! You're almost there!");
            $this->redirect->gotoUrl('users/login');
        } else {
            $this->flashError('Invalid token');
        }
    }

    public function changePasswordAction()
    {
        $user = current_user();
        if(!$user) {
            return;
        }

        $form = new Omeka_Form_ChangePassword();
        $form->setUser($user);
        $this->view->form = $form;

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (isset($_POST['new_password'])) {
            if ($form->isValid($_POST)) {
                $values = $form->getValues();
                $user->setPassword($values['new_password']);
                $user->forceSave();
                $this->flashSuccess(__("Password changed!"));
                $success = true;
            }
        } else {
            if (!$form->isValid($_POST)) {
                return;
            }
            try {
                if ($user->saveForm($form->getValues())) {
                    $this->flashSuccess(__('The user %s was successfully changed!', $user->username));
                    $success = true;
                }
            } catch (Omeka_Validator_Exception $e) {
                $this->flashValidationErrors($e);
            }
        }

    }

    protected function sendConfirmedEmail($user)
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
        $mail = $this->getMail($user, $body, $subject);
        try {
            $mail->send();
        } catch (Exception $e) {
            _log($e);
            _log($body);
        }

    }

    protected function sendConfirmationEmail($user, $token)
    {
        $siteTitle = get_option('site_title');
        $url = WEB_ROOT . '/guest-user/user/confirm/token/' . $token->token;
        $subject = "Your request to join $siteTitle";
        $body = "You have registered for an account on $siteTitle. Please confirm your registration by following this link: $url If you did not request to join $siteTitle please disregard this email.";
        $mail = $this->getMail($user, $body, $subject);
        try {
            $mail->send();
        } catch (Exception $e) {
            _log($e);
            _log($body);
        }
    }

    protected function sendAdminNewConfirmedUserEmail($user)
    {
        $siteTitle = get_option('site_title');
        $url = WEB_ROOT . "/admin/users/edit/" . $user->id;
        $subject = "New request to join $siteTitle";
        $body = "A new user has confirmed that they want to join $siteTitle.  ";
        $body .= "\n\n<a href='$url'>" . $user->username . "</a>";
        $mail = $this->getMail($user, $body, $subject);
        $mail->clearRecipients();
        $mail->addTo(get_option('administrator_email'), "$siteTitle Administrator");
         try {
            $mail->send();
        } catch (Exception $e) {
            _log($body);
        }
    }

    protected function getMail($user, $body, $subject)
    {
        if(method_exists($user, 'getEntity')) {
            $entity = $user->getEntity();
            $email = $entity->email;
            $name = $entity->getName();
        } else {
            $email = $user->email;
            $name = $users->name;
        }
        $siteTitle  = get_option('site_title');
        $from = get_option('administrator_email');
        $mail = new Zend_Mail();
        $mail->setBodyText($body);
        $mail->setFrom($from, "$siteTitle Administrator");
        $mail->addTo($email, $name);
        $mail->setSubject($subject);
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        return $mail;
    }

    protected function createToken($user)
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

