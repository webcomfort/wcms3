<!-- Modal -->
<div class="modal fade" id="GalleryInsModal<?php echo $ins_id; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Добавить галерею</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open('', array('id' => 'GalleryInsForm'.$ins_id)); ?>
        <div id="GalleryInsBody<?php echo $ins_id; ?>">У вас недостаточно прав для этой операции.</div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" id="GallerySaveInsForm<?php echo $ins_id; ?>">Сохранить</button>
      </div>
    </div>
  </div>
</div>
<script>
    jQuery(document).ready(function() {
        $.post('/adm_gallery_photos/p_add_gal', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val(), 'inc_id' : '<?php echo $ins_id; ?>' }, function(result){
            $('#GalleryInsBody<?php echo $ins_id; ?>').empty();
            $('#GalleryInsBody<?php echo $ins_id; ?>').append(result);
        });
        $('#GallerySaveInsForm<?php echo $ins_id; ?>').click(function(){
            var formData = new FormData(document.getElementById('GalleryInsForm<?php echo $ins_id; ?>'));

            $.ajax({
                url: "/adm_gallery_photos/p_save_gal",
                type: "POST",
                data:  formData,
                contentType: false,
                cache: false,
                processData:false,
                success: function(result){
                    $('#GalleryInsBody<?php echo $ins_id; ?>').empty();
                    $('#GalleryInsBody<?php echo $ins_id; ?>').append(result);
                    $.post('/adm_gallery_photos/p_return_gal', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val() }, function(result){
                        $('#gallery_insert_select_<?php echo $ins_id; ?>').empty().append(result);
                    });
                }
            });
        });

        $(document).on('click', '.btn-ins', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var gal = $('#gallery_insert_select_<?php echo $ins_id; ?>').val();
            if(gal !== '0') CKEDITOR.instances['page_article_'+id].insertHtml('{@module mod_gallery '+gal+'@}');
        });
    });
</script>