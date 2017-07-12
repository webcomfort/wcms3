<!-- Modal -->
<div class="modal fade" id="GalleryModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Добавить галерею</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open('', array('id' => 'GalleryAddForm')); ?>
        <div id="GalleryAddBody">У вас недостаточно прав для этой операции.</div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" id="GallerySaveForm">Сохранить</button>
      </div>
    </div>
  </div>
</div>
<script>
    jQuery(document).ready(function() {
        $.post('/adm_gallery_photos/p_add_gal', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val() }, function(result){
            $('#GalleryAddBody').empty();
            $('#GalleryAddBody').append(result);
        });
        $('#GallerySaveForm').click(function(){
            var formData = new FormData(document.getElementById('GalleryAddForm'));

            $.ajax({
                url: "/adm_gallery_photos/p_save_gal",
                type: "POST",
                data:  formData,
                contentType: false,
                cache: false,
                processData:false,
                success: function(result){
                    $('#GalleryAddBody').empty();
                    $('#GalleryAddBody').append(result);
                    $.post('/adm_gallery_photos/p_return_gal', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val() }, function(result){
                        $('#PME_data_<?php echo $ajax_select; ?>').empty().append(result);
                    });
                },
                error: function(){

                }
            });
        });
        <?php
        if(isset($inc_admin_page)){
        ?>
        set_gal_link();
        $("#PME_data_<?php echo $ajax_select; ?>").change(function () {
            set_gal_link();
        });

        function set_gal_link() {
            var gal_val = $("#PME_data_<?php echo $ajax_select; ?>").val();
            if (gal_val != 0) {
                $(".gal_link_button").remove();
                $("#add_gallery_button").after("<a class=\"gal_link_button btn btn-default btn-inc\" href=\"/admin/<?php echo $inc_admin_page; ?>/gallery/"+gal_val+"\" target='_blank'>Перейти к редактированию</a>");
            } else {
                $(".gal_link_button").remove();
            }
        }
        <?php
        }
        ?>
    });
</script>