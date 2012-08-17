<?php

class GuestUserForm extends Omeka_Form_User
{
    
    public function init()
    {
        parent::init();
        //need to remove submit so I can add in new elements
        $this->removeElement('submit');
        $this->addElement('password', 'new_password',
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
        $this->addElement('password', 'new_password_confirm',
                array(
                    'label'         => 'Password again for match',
                    'required'      => true,
                    'class'         => 'textinput',
                    'errorMessages' => array(Omeka_Form_ChangePassword::ERROR_NEW_PASSWORD_CONFIRM_REQUIRED)
                )
        );
        $this->addElement('submit', 'submit', array('label' => 'Register'));               
    }    
}