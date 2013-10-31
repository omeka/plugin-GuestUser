<?php

define('GUEST_USER_PLUGIN_DIR', PLUGIN_DIR . '/GuestUser');
require_once(GUEST_USER_PLUGIN_DIR . '/helpers/functions.php');
include(FORM_DIR . '/User.php');
//require_once(GUEST_USER_PLUGIN_DIR . '/libraries/GuestUserForm.php');


class GuestUserPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'define_acl',
        'public_header',
        'public_head',
        'admin_theme_header',
        'config',
        'config_form',
        'before_save_user',
        'initialize'
    );

    protected $_filters = array(
        'public_navigation_admin_bar',
        'public_show_admin_bar',
        'guest_user_widgets'       
    );

    
    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }    
    
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
        
        //if plugin was uninstalled/reinstalled, reactivate the guest users
        $guestUsers = $this->_db->getTable('User')->findBy(array('role'=>'guest'));
        //skip activation emails when reinstalling
        if(count($guestUsers) != 0) {
            set_option('guest_user_skip_activation_email', true);
            foreach($guestUsers as $user) {
                $user->active = true;
                $user->save();
            }
            set_option('guest_user_skip_activation_email', false);
        }     
        
        set_option('guest_user_login_text', __('Login'));
        set_option('guest_user_register_text', __('Register'));
    }

    public function hookUninstall($args)
    {
        //deactivate the guest users
        $guestUsers = $this->_db->getTable('User')->findBy(array('role'=>'guest'));
        foreach($guestUsers as $user) {
            $user->active = false;
            $user->save();
        }
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
        queue_css_file('guest-user');        
        queue_js_file('guest-user');
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

    public function hookBeforeSaveUser($args)
    {
        if(get_option('guest_user_skip_activation_email')) {
            return;
        }
        $post = $args['post'];
        $record = $args['record'];
        //compare the active status being set with what's actually in the database
        if($record->exists()) {
            $dbUser = get_db()->getTable('User')->find($record->id);
            if($record->role == 'guest' && $record->active && !$dbUser->active) {
                try {
                    $this->_sendMadeActiveEmail($record);
                } catch (Exception $e) {
                    _log($e);
                }
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
        $loginLabel = get_option('guest_user_login_text') ? get_option('guest_user_login_text') : __('Login');
        $registerLabel = get_option('guest_user_register_text') ? get_option('guest_user_register_text') : __('Register'); 
        $navLinks = array(
                'guest-user-login' => array(
                    'id' => 'guest-user-login',
                    'label' => $loginLabel,
                    'uri' => url('guest-user/user/login')
                ),
                
                'guest-user-register' => array(
                    'id' => 'guest-user-register', 
                    'label' => $registerLabel,
                    'uri' => url('guest-user/user/register'),
                    )
                
                );
        return $navLinks;

        
    }
    

    public function filterGuestUserWidgets($widgets)
    {
        $widget = array('label'=> __('My Account'));
        $passwordUrl = url('guest-user/user/change-password');
        $accountUrl = url('guest-user/user/update-account');
        $html = "<ul>";
        $html .= "<li><a href='$accountUrl'>" . __("Update account info and password") . "</a></li>";
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
        $subject = __("Your %s account", $siteTitle);
        $body = __("An admin has made your account active. You can now log in to %s with your password", "<a href='" . WEB_ROOT . "'>$siteTitle</a>");
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
