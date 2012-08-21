

jQuery('document').ready(function() {
    p1 = jQuery('#new_password');
    p2 = jQuery('#new_password_confirm');
    confirmP = jQuery('p#confirm');
    p2.keyup(function() {
        if(p2.val() != p1.val() ) {
            confirmP.html("Passwords don't match!") ;
        } else {
            confirmP.html("Passwords match") ;
        }
    });
});




