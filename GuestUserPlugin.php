<?php
class GuestUser extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install',
        'define_acl',
        'public_theme_page_header',
        'public_theme_header',
        'admin_theme_header',
        'config',
        'config_form',
        'before_save_form_user'
    );

    protected $_filters = array(
        'guest_user_widgets',
        'guest_user_links'
    );

    public function setUp()
    {
        parent::setUp();
        require_once(GUEST_USER_PLUGIN_DIR . '/libraries/GuestUser_ControllerPlugin.php');
        Zend_Controller_Front::getInstance()->registerPlugin(new GuestUser_ControllerPlugin);
    }

    public function hookInstall()
    {
        $db = get_db();
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

        $db->exec($sql);
    }


    public function hookDefineAcl($acl)
    {
        $acl->addRole(new Zend_Acl_Role('guest'), null);
    }

    public function hookConfig()
    {
        set_option('guest_users_open', $_POST['guest_users_open']);
        set_option('guest_users_capabilities', $_POST['guest_users_capabilities']);
    }

    public function hookConfigForm()
    {
        include 'config_form.php';
    }

    public function hookAdminThemeHeader($request)
    {
        if($request->getControllerName() == 'plugins' && $request->getParam('name') == 'GuestUser') {
            queue_js('tiny_mce/tiny_mce');
            $js = "if (typeof(Omeka) !== 'undefined'){
                Omeka.wysiwyg();
            };";
            queue_js_string($js);
        }

    }
    public function hookPublicThemeHeader($request)
    {
        queue_css('guest-user');
        queue_js('guest-user');
        if($request->getModuleName() == 'guest-user') {
            queue_js('guest-user-password');
        }

    }

    public function hookPublicThemePageHeader($request)
    {
        $html = "<div id='guest-user-bar'>";
        $user = current_user();
        if($user) {

            $links = array();
            $links = apply_filters('guest_user_links', $links);
            $userText = get_option('guest_user_user_text');
            if($userText == '') {
                $userText = $user->username;
            }
            $html.= "<p id='guest-user-user'>$userText</p>";
            $html .= "<div id='guest-user-dropdown-bar'>";
            $html .= "<ul>";
            foreach($links as $link) {
                $html .= "<li>$link</li>";
            }

            $html .= "</ul>";
            $html .= "</div>";
        } else {
            $registerUrl = uri('guest-user/user/register');
            $loginUrl = uri('users/login');
            $html.= "<p><span id='guest-user-login'><a href='$loginUrl'>" . get_option('guest_user_login_text') . "</a></span>";
            $html .= " / <span id='guest-user-register'><a href='$registerUrl'>" . get_option('guest_user_register_text') . "</a></span></p>";
        }
        $html .= "</div>";
        echo $html;
    }

    public function hookBeforeSaveFormUser($record, $post)
    {
        if (! $record->active && ($record->role == 'guest') && ($post['active'] == 1)) {
            try {
                $this->sendMadeActiveEmail($record);
            } catch (Exception $e) {
                _log($e);
            }
        }
    }

    public function filterGuestUserLinks($links)
    {
        $url = uri('guest-user/user/me');
        $logoutUrl = uri('users/logout');
        $links[] = "<a href='$logoutUrl'>Logout</a>";
        $links[] = "<a href='$url'>My Dashboard</a>";

        return $links;
    }

    public function filterGuestUserWidgets($widgets)
    {
        $html = "<h2>My Account</h2>";
        $passwordUrl = uri('guest-user/user/change-password');
        $html .= "<ul>";
        $html .= "<li><a href='$passwordUrl'>Change Password</a></li>";
        $html .= "</ul>";
        $widgets[] = $html;
        return $widgets;
    }

    private function sendMadeActiveEmail($record)
    {
        $entity = $record->getEntity();
        $siteTitle  = get_option('site_title');
        $subject = "Your $siteTitle account";
        $body = "An admin has made your account active. You can now log in with your password";
        $from = get_option('administrator_email');
        $mail = new Zend_Mail();
        $mail->setBodyText($body);
        $mail->setFrom($from, "$siteTitle Administrator");
        $mail->addTo($entity->email, $entity->getName());
        $mail->setSubject($subject);
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->send();
        _log($body);
    }
}

?>
