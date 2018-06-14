/************************************************
 ------- Contacts
 *************************************************/
$( document ).ready(function() {
    $('#contacts_form').submit(function (event) {
        event.preventDefault();
        grecaptcha.reset();
        grecaptcha.execute();
    });
});

function onSubmit(token) {
    var formData = $('#contacts_form').serialize();
    $.ajax({
        method: "POST",
        url: "/mod_contacts/p_send/",
        data: formData
    })
    .done(function( result ) {
        var json = $.parseJSON(result);
        $("#contacts_error").html(json.error);
        if(json.error_code == '2') {
            $("#contacts_form")[0].reset();
        }
    })
    .fail(function() {
        alert('SYSTEM ERROR, TRY LATER AGAIN');
    });
}


/************************************************
 ------- Tags
 *************************************************/
$( document ).ready(function() {
    $( ".all-tags" ).click(function() {
        $( ".tag-container" ).removeClass( "d-none" );
        $( ".all-tags" ).hide();
    });
});