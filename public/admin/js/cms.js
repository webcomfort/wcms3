// Equal heights
!function(a){a.fn.equalHeights=function(){var b=0,c=a(this);return c.each(function(){var c=a(this).innerHeight();c>b&&(b=c)}),c.css("height",b)},a("[data-equal]").each(function(){var b=a(this),c=b.data("equal");b.find(c).equalHeights()})}(jQuery);

// Login form submit
function onSubmit(token) {
    document.getElementById("login_form").submit();
}

// Hash function
function hashCode (str){
    var hash = 0;
    if (str.length == 0) return hash;
    for (i = 0; i < str.length; i++) {
        char = str.charCodeAt(i);
        hash = ((hash<<5)-hash)+char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
}

// Random function
function getRandom(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}

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

    if ($(".select2_icon")[0]){
        $(".select2_icon").select2({
            language: "ru",
            escapeMarkup: function (markup) { return markup; },
            templateResult: formatItems_icon,
            templateSelection: formatItemsSelection_icon
        });
    }

    //JsTree
    $(".jstree").jstree().bind('select_node.jstree', function(e,data) {
        var href = data.node.a_attr.href;
        var target = data.node.a_attr.target;
        if(href !== '#'){
            if (target === '_blank') window.open(href,target);
            else window.open(href,'_self');
        }
    });

    // ------------------------------------------------------------------------
    //                               Articles
    // ------------------------------------------------------------------------
    function firstAndLast(container, div, button) {
        if (!container) {
            return false;
        }
        else {
            var n = div.length;
            container.find('button:disabled').prop('disabled', false);
            if(n === 1) container.find('button.'+button+'-button-remove:first').prop('disabled', true);
            container.find('button.'+button+'-button-up:first').prop('disabled', true);
            container.find('button.'+button+'-button-down:last').prop('disabled', true);
        }
    }

    function getMaximum(container) {
        var maximum = null;

        $(container).each(function() {
            var value = parseFloat($(this).data('id'));
            maximum = (value > maximum) ? value : maximum;
        });

        return maximum;
    }

    function recalcOrder(container) {
        var i = 1;

        $(container).each(function() {
            $(this).val(i);
            i++;
        });
    }

    firstAndLast($('#articles_area'), $('.article-div'), 'article');

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
            firstAndLast(grandparent, $('.article-div'), 'article');
            recalcOrder('.page_article_order');
        }
        else if ($(this).hasClass('article-button-down')) {
            CKEDITOR.instances[editor].destroy();
            parent.insertAfter(parent.next('.article-div'));
            CKEDITOR.replace(editor);
            firstAndLast(grandparent, $('.article-div'), 'article');
            recalcOrder('.page_article_order');
        }
    });

	$(document).on('change', '.insert-select', function() {
        var parent = $(this).closest('.article-div');
        var id = parent.data('id');
        var type = $(this).val();

        if(type !== 0){
            $.ajax({
                method: "GET",
                url: "/cms_inserts/p_get_insert_ui/",
                data: { aid: id, tid: type }
            }).done(function(result) {
                $('#insert_area_'+id).html(result);
                $('#gallery_insert_select_'+id).select2();
            });
        } else {
            $('#insert_area_'+id).empty();
        }
    });

    $(document).on('click', '.article-button-plus', function(e) {
        e.preventDefault();
        var grandparent = $('#articles_area');
        var parent = $(this).closest('.article-div');
        var id = parent.data('id');
        var maxId = getMaximum('.article-div');
        var nextId = maxId + 1;
        var trigger = $('#page_article_trigger').val();
        var type = $('#page_article_type').val();

        $.ajax({
            method: "GET",
            url: "/cms_articles/p_get_html/",
            data: { id: nextId, type: type, trigger: trigger }
        }).done(function(result) {
            var json = $.parseJSON(result);
            var article = json.div +
                json.selects +
                json.buttons +
                json.insert +
                json.hidden +
                json.textarea +
                '</div>';

            $(article).insertBefore(parent);
            CKEDITOR.replace('page_article_'+nextId);

            //Selects
            $("select[name=page_article_view_"+nextId+"]").select2({ language: "ru" });
            $("select[name=page_article_place_"+nextId+"]").select2({ language: "ru" });
			$("select[name=insert_type_"+nextId+"]").select2({ language: "ru" });

            if ($("select[name=page_article_bg_"+nextId+"]")[0]){
                $("select[name=page_article_bg_"+nextId+"]").select2({
                    language: "ru",
                    escapeMarkup: function (markup) { return markup; },
                    templateResult: formatItems_icon,
                    templateSelection: formatItemsSelection_icon
                });
            }

            firstAndLast(grandparent, $('.article-div'), 'article');
            recalcOrder('.page_article_order');
        });
    });

    $(document).on('click', '.article-button-remove', function(e) {
        e.preventDefault();
        var grandparent = $('#articles_area');
        var parent = $(this).closest('.article-div');
        parent.remove();
        firstAndLast(grandparent, $('.article-div'), 'article');
        recalcOrder('.page_article_order');
    });

    // ------------------------------------------------------------------------
    //                               Fields
    // ------------------------------------------------------------------------

    firstAndLast($('#fields_area'), $('.field-div'), 'field');

    $(document).on('click', '.field-button-move', function(e) {
        e.preventDefault();

        var parent = $(this).closest('.field-div');
        var id = parent.data('id');
        var grandparent = $('#fields_area');

        if ($(this).hasClass('field-button-up')) {
            parent.insertBefore(parent.prev('.field-div'));
            firstAndLast(grandparent, $('.field-div'), 'field');
            recalcOrder('.field_order');
        }
        else if ($(this).hasClass('field-button-down')) {
            parent.insertAfter(parent.next('.field-div'));
            firstAndLast(grandparent, $('.field-div'), 'field');
            recalcOrder('.field_order');
        }
    });

    $('.field_active').change(function() {
        var id = $(this).data( "id" );
        if($(this).is(':checked')) {
            $('#field-content-'+id).removeClass( "field-content-hidden" ).addClass( "field-content-visible" );
        } else {
            $('#field-content-'+id).removeClass( "field-content-visible" ).addClass( "field-content-hidden" );
        }
    });
});
