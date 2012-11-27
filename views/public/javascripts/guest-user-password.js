

jQuery('document').ready(function() {
    p1 = jQuery('#new_password');
    p2 = jQuery('#new_password_confirm');
    confirmEl = jQuery('#new_password_confirm-label label');
    p2.keyup(function() {
        if(p2.val().length == 0) {
            confirmEl.html("Password again for match");
            confirmEl.attr('style', '');
            return
        }
        if(p2.val() != p1.val() ) {
            confirmEl.html("Passwords don't match!") ;
            confirmEl.attr('style', 'color: red');
        } else {
            confirmEl.html("Passwords match") ;
            confirmEl.attr('style', '');
        }
    });
});




