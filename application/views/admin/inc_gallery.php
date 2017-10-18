<!-- Modal -->
<div class="modal fade" id="GalleryModal<?php echo $inc_id; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Добавить галерею</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open('', array('id' => 'GalleryAddForm'.$inc_id)); ?>
        <div id="GalleryAddBody<?php echo $inc_id; ?>">У вас недостаточно прав для этой операции.</div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" id="GallerySaveForm<?php echo $inc_id; ?>">Сохранить</button>
      </div>
    </div>
  </div>
</div>
<script>
    jQuery(document).ready(function() {
        $.post('/adm_gallery_photos/p_add_gal', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val(), 'inc_id' : '<?php echo $inc_id; ?>' }, function(result){
            $('#GalleryAddBody<?php echo $inc_id; ?>').empty();
            $('#GalleryAddBody<?php echo $inc_id; ?>').append(result);
        });
        $('#GallerySaveForm<?php echo $inc_id; ?>').click(function(){
            var formData = new FormData(document.getElementById('GalleryAddForm<?php echo $inc_id; ?>'));

            $.ajax({
                url: "/adm_gallery_photos/p_save_gal",
                type: "POST",
                data:  formData,
                contentType: false,
                cache: false,
                processData:false,
                success: function(result){
                    $('#GalleryAddBody<?php echo $inc_id; ?>').empty();
                    $('#GalleryAddBody<?php echo $inc_id; ?>').append(result);
                    $.post('/adm_gallery_photos/p_return_gal', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val() }, function(result){
                        $('#PME_data_<?php echo $ajax_select; ?>').empty().append(result);
                        set_gal_<?php echo $ajax_select; ?>();
                    });
                },
                error: function(){

                }
            });
        });
        <?php
        if(isset($inc_admin_page)){
        ?>
        set_gal_<?php echo $ajax_select; ?>();
        $("#PME_data_<?php echo $ajax_select; ?>").change(function () {
            set_gal_<?php echo $ajax_select; ?>();
        });

        function set_gal_<?php echo $ajax_select; ?>() {
            var gal_val = $("#PME_data_<?php echo $ajax_select; ?>").val();
            if (gal_val != 0) {
                $(".gal_link_<?php echo $ajax_select; ?>_button").remove();
                $("#add_gallery_button_<?php echo $inc_id; ?>").after("<a class=\"gal_link_<?php echo $ajax_select; ?>_button btn btn-default btn-inc\" href=\"/admin/<?php echo $inc_admin_page; ?>/gallery/"+gal_val+"\" target='_blank'>Перейти к редактированию</a>");
            } else {
                $(".gal_link_<?php echo $ajax_select; ?>_button").remove();
            }
        }
        <?php
        }
        ?>
    });
</script>