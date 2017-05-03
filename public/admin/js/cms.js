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

    // ------------------------------------------------------------------------
    //                               Articles
    // ------------------------------------------------------------------------
    function firstAndLast(container) {
        if (!container) {
            return false;
        }
        else {
            container.find('button:disabled').prop('disabled', false);
            container.find('button.article-button-up:first').prop('disabled', true);
            container.find('button.article-button-down:last').prop('disabled', true);
        }
    }

    function getMaximum() {
        var maximum = null;

        $('.article-div').each(function() {
            var value = parseFloat($(this).data('id'));
            maximum = (value > maximum) ? value : maximum;
        });

        return maximum;
    }

    function recalcOrder() {
        var i = 1;

        $('.page_article_order').each(function() {
            $(this).val(i);
            i++;
        });
    }

    firstAndLast($('#articles_area'));

    $(document).on('click', '.article-button-move', function(e) {
        e.preventDefault();

        var parent = $(this).closest('.article-div');
        var id = parent.data('id');
        var grandparent = $('#articles_area');
        var editor = 'page_article_'+id;

        if ($(this).hasClass('article-button-up')) {
            CKEDITOR.instances[editor].destroy();
            parent.insertBefore(parent.prev('.article-div'));
            CKEDITOR.replace(editor);
            firstAndLast(grandparent);
            recalcOrder();
        }
        else if ($(this).hasClass('article-button-down')) {
            CKEDITOR.instances[editor].destroy();
            parent.insertAfter(parent.next('.article-div'));
            CKEDITOR.replace(editor);
            firstAndLast(grandparent);
            recalcOrder();
        }
    });

    $(document).on('click', '.article-button-plus', function(e) {
        e.preventDefault();
        var grandparent = $('#articles_area');
        var parent = $(this).closest('.article-div');
        var id = parent.data('id');
        var maxId = getMaximum();
        var nextId = maxId + 1;

        $.ajax({
            method: "GET",
            url: "/cms_articles/p_get_html/",
            data: { id: nextId }
        }).done(function(result) {
            var json = $.parseJSON(result);
            var article = json.div +
                json.selects +
                json.buttons +
                json.hidden +
                json.textarea +
                '</div>';

            $(article).insertBefore(parent);
            CKEDITOR.replace('page_article_'+nextId);
            firstAndLast(grandparent);
            recalcOrder();
        });
    });

    $(document).on('click', '.article-button-remove', function(e) {
        e.preventDefault();
        var grandparent = $('#articles_area');
        var parent = $(this).closest('.article-div');
        parent.remove();
        firstAndLast(grandparent);
        recalcOrder();
    });

});