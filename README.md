# Guest User


## About


The Guest User plugin creates a role called 'guest', and provides configuration options for a login and registration screen. Guest users become registered users in Omeka, but have no other privileges to the admin side of your Omeka installation. GuestUser is thus intended to be a common plugin that other plugins needing a guest user use as a dependency.


## Configuration


### Long about message 
Use this to provide text explaining what Guest Users can or cannot do. This should be updated as you enable additional plugins that make use of the Guest Users. It will display on the guest user registration page.

### Short about message
This will appear when a non-logged in user hovers over the Log in or Register links. It should be a shorter teaser for the long about message.

### Allow registration without approval
If checked, Guest Users will be active as soon as they confirm their registration token

### Dashboard label
The label used on the Guest Users 'Dashboard' page. If you don't like the dashboard metaphor, change this.

### Login link text
The text to display as the login link

### Register link text
The text to display as the registration link

### Logged in text
The text that replaces the login/register links. Leave empty to show the user's username



## Using as a dependency 


Guest User provides two filters for plugins to add additional context and information to the user.

### guest_user_links

The guest_user_links filter allows you to programmatically add links to the hover-over links that a logged in user will see. By default, GuestUser provides a logout link, and a link to the user's dashboard. If your plugin provides pages that display more information, you should use this filter to create a new link.

```php

    public function filterGuestUserLinks($links)
    {
        $url = uri('guest-user/user/me');
        $logoutUrl = uri('users/logout');
        $links[] = "<a href='$logoutUrl'>Logout</a>";
        $links[] = "<a href='$url'>My Dashboard</a>";
        return $links;
    }

```


### guest_user_widgets

This filter lets you add a widget to the user's dashboard containing a small amount of HTML.

Widgets should be an array with two keys: the label of the widget, and the content to display.


```php

    public function filterGuestUserWidgets($widgets)
    {
        $widget = array('label'=>'My Account');
        $passwordUrl = uri('guest-user/user/change-password');
        $html = "<ul>";
        $html .= "<li><a href='$passwordUrl'>Change Password</a></li>";
        $html .= "</ul>";
        $widget['content'] = $html;
        $widgets[] = $widget;
        return $widgets;
    }


```