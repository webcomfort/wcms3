/************************************************
 ------- Contacts
 *************************************************/
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
 ------- Replace all SVG images with inline SVG
 *************************************************/
jQuery('.svg').each(function(){
    var $cont = jQuery(this);
    var contID = $cont.attr('id');
    var contClass = $cont.attr('class');
    var svgURL = $cont.attr('data-src');

    jQuery.get(svgURL, function(data) {
        // Get the SVG tag, ignore the rest
        var $svg = jQuery(data).find('svg');

        // Add replaced tag's ID to the new SVG
        if(typeof contID !== 'undefined') {
            $svg = $svg.attr('id', contID);
        }
        // Add replaced tag's classes to the new SVG
        if(typeof contClass !== 'undefined') {
            $svg = $svg.attr('class', contClass+' replaced-svg');
        }

        // Remove any invalid XML tags as per http://validator.w3.org
        $svg = $svg.removeAttr('xmlns:a');

        // Replace image with new SVG
        $cont.replaceWith($svg);

    }, 'xml');

});
