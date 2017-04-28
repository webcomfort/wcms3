$(document).ready(function () {

    // Bootstrap tooltip activation
    $('[rel=tooltip]').tooltip();

    // Calendar activation
    var today = new Date() ;
    var hour = today.getHours();
    hour = (hour>10?'':'0')+hour;
    var min = today.getMinutes();
    min = (min>10?'':'0')+min;
    var curTime = hour+':'+min;
    $( '.datepicker' ).each( function(index){
        datepicker_default_val = $(this).val();
        $( this ).datepicker( { changeMonth: true, changeYear: true } );
        $( this ).datepicker( 'option', 'dateFormat', 'yy-mm-dd'+' '+curTime );
        $( this ).datepicker( $.datepicker.regional[ 'ru' ] );
        $( this ).val(datepicker_default_val);
    });

    // CKeditor
    $('.htmleditor').each(function(index){
        CKEDITOR.replace( this, { customConfig: '/public/admin/third_party/ckeditor/config.js' });
    });

    //Selects
    $(".select2").select2({
        language: "ru"
    });
});