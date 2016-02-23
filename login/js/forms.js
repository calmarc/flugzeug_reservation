
function regformhash(form, uid, email, password, conf) {
    // Check each field has a value
    if (uid.value == '' || email.value == '' || password.value == '' || conf.value == '') {
        alert('Bitte alle Felder ausfüllen.');
        return false;
    }
    
    // Check that the password is sufficiently long (min 6 chars)
    // The check is duplicated below, but this is included to give more
    // specific guidance to the user
    if (password.value.length < 4) {
        alert('Passwörter müssen mindestens 4 Zeichen lang sein.');
        form.password.focus();
        return false;
    }

	// Check password and confirmation are the same
    if (password.value != conf.value) {
        alert('Die Passwörter stimmen nicht überein.');
        form.password.focus();
        return false;
    }

        
    // Finally submit the form. 
    form.submit();
    return true;
}
