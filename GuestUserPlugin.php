<?php

define('GUEST_USER_PLUGIN_DIR', PLUGIN_DIR . '/GuestUser');
require_once(GUEST_USER_PLUGIN_DIR . '/helpers/functions.php');
include(FORM_DIR . '/User.php');
//require_once(GUEST_USER_PLUGIN_DIR . '/libraries/GuestUserForm.php');


class GuestUserPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'define_acl',
        'public_header',
        'public_head',
        'admin_theme_header',
        'config',
        'config_form',
        'before_save_form_user'
    );

    protected $_filters = array(
        'public_navigation_admin_bar',
        'public_show_admin_bar',
        'guest_user_widgets'       
    );

    public function setUp()
    {
       
        parent::setUp();
        require_once(GUEST_USER_PLUGIN_DIR . '/libraries/GuestUser_ControllerPlugin.php');
        Zend_Controller_Front::getInstance()->registerPlugin(new GuestUser_ControllerPlugin);
    }

    public function hookInstall()
    {
        $db = $this->_db;
        $sql = "CREATE TABLE IF NOT EXISTS `$db->GuestUserTokens` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `token` text COLLATE utf8_unicode_ci NOT NULL,
                  `user_id` int NOT NULL,
                  `email` tinytext COLLATE utf8_unicode_ci NOT NULL,
                  `created` datetime NOT NULL,
                  `confirmed` tinyint(1) DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;
                ";

        $db->query($sql);        
        set_option('guest_user_login_text', 'Login');
        set_option('guest_user_register_text', 'Register');
    }


    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addRole(new Zend_Acl_Role('guest'), null);
    }

    public function hookConfig($args)
    {        
        $post = $args['post'];
        foreach($post as $option=>$value) {
            set_option($option, $value);
        }
    }

    public function hookConfigForm()
    {
        include 'config_form.php';
    }

    public function hookAdminThemeHeader($args)
    {
        $request = $args['request'];
        if($request->getControllerName() == 'plugins' && $request->getParam('name') == 'GuestUser') {
            queue_js_file('tiny_mce/tiny_mce');
            $js = "if (typeof(Omeka) !== 'undefined'){
                Omeka.wysiwyg();
            };";
            queue_js_string($js);
        }

    }
    public function hookPublicHead($args)
    {
        //$request = $args['request'];
        
        queue_css_file('guest-user');        
        queue_js_file('guest-user');
        //if($request->getModuleName() == 'guest-user') {
        //    queue_js_file('guest-user-password');
        //}

    }

    public function hookPublicHeader($args)
    {        
        $html = "<div id='guest-user-register-info'>";
        $user = current_user();
        if(!$user) {
            $shortCapabilities = get_option('guest_user_short_capabilities');
            if($shortCapabilities != '') {
                $html .= $shortCapabilities;
            }
        }
        $html .= "</div>";
        echo $html;
    }

    public function hookBeforeSaveFormUser($args)
    {
        $post = $args['post'];
        $request = $args['request'];
        if (! $record->active && ($record->role == 'guest') && ($post['active'] == 1)) {
            try {
                $this->_sendMadeActiveEmail($record);
            } catch (Exception $e) {
                _log($e);
            }
        }
    }

    public function filterPublicShowAdminBar($show)
    {
        return true;
    }
    
    public function filterPublicNavigationAdminBar($navLinks)
    {
        //Clobber the default admin link if user is guest
        $user = current_user();
        if($user) {
            if($user->role == 'guest') {
                unset($navLinks[1]);
            } 
            $navLinks[0]['id'] = 'admin-bar-welcome';
            $meLink = array('id'=>'guest-user-me',
                    'uri'=>url('guest-user/user/me'),
                    'label' => get_option('guest_user_dashboard_label')
            );
            $filteredLinks = apply_filters('guest_user_links' , array('guest-user-me'=>$meLink) );
            $navLinks[0]['pages'] = $filteredLinks; 
        
            return $navLinks;
        }
        
        $navLinks = array(
                'guest-user-login' => array(
                    'id' => 'guest-user-login',
                    'label' => __('Login'),
                    'uri' => url('guest-user/user/login')
                ),
                
                'guest-user-register' => array(
                    'id' => 'guest-user-register', 
                    'label' => __('Register'),
                    'uri' => url('guest-user/user/register'),
                    )
                
                );
        return $navLinks;

        
    }
    

    public function filterGuestUserWidgets($widgets)
    {
        $widget = array('label'=>'My Account');
        $passwordUrl = url('guest-user/user/change-password');
        $accountUrl = url('guest-user/user/update-account');
        $html = "<ul>";
        $html .= "<li><a href='$passwordUrl'>Change Password</a></li>";
        $html .= "<li><a href='$accountUrl'>Update Account Info</a></li>";
        $html .= "</ul>";
        $widget['content'] = $html;
        $widgets[] = $widget;
        return $widgets;
    }

    private function _sendMadeActiveEmail($record)
    {
        $email = $record->email;
        $name = $record->name;
 
        $siteTitle  = get_option('site_title');
        $subject = "Your $siteTitle account";
        $body = "An admin has made your account active. You can now log in with your password";
        $from = get_option('administrator_email');
        $mail = new Zend_Mail();
        $mail->setBodyText($body);
        $mail->setFrom($from, "$siteTitle Administrator");
        $mail->addTo($email, $name);
        $mail->setSubject($subject);
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->send();
    }
}

?>
