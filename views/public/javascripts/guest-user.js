
jQuery('document').ready(function() {
    jQuery('div#guest-user-user').hover(
            function() {jQuery('div#guest-user-dropdown-bar').show();},
            function() {jQuery('div#guest-user-dropdown-bar').hide();}
        );

    jQuery('span#guest-user-register').hover(
            function() {jQuery('div#guest-user-dropdown-bar').show();},
            function() {jQuery('div#guest-user-dropdown-bar').hide();}
        );


});