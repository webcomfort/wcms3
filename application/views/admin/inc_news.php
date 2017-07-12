<!-- Modal -->
<div class="modal fade" id="NewsModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Добавить рубрику</h4>
      </div>
      <div class="modal-body">
        <?php echo form_open('', array('id' => 'NewsAddForm')); ?>
        <div id="NewsAddBody">У вас недостаточно прав для этой операции.</div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" id="NewsSaveForm">Сохранить</button>
      </div>
    </div>
  </div>
</div>
<script>
    jQuery(document).ready(function() {
        $.post('/adm_news_categories/p_add_cat', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val() }, function(result){
            $('#NewsAddBody').empty();
            $('#NewsAddBody').append(result);
        });
        $('#NewsSaveForm').click(function(){
            var NewsFormData = $('#NewsAddForm').serializeArray();
            NewsFormData.push({name: 'csrf_wcms_token', value: $("input[name=csrf_wcms_token]").val()});
            $.post('/adm_news_categories/p_save_cat', NewsFormData, function(result){
                $('#NewsAddBody').empty();
                $('#NewsAddBody').append(result);
                $.post('/adm_news_categories/p_return_cat', { 'csrf_wcms_token' : $("input[name=csrf_wcms_token]").val() }, function(result){
                    $('#PME_data_<?php echo $ajax_select; ?>').empty().append(result);
                });
            });
        });

        <?php
        if(isset($inc_admin_page)){
        ?>
        set_news_link();
        $("#PME_data_<?php echo $ajax_select; ?>").change(function () {
            set_news_link();
        });

        function set_news_link() {
            var news_val = $("#PME_data_<?php echo $ajax_select; ?>").val();
            if (news_val != 0) {
                $(".news_link_button").remove();
                $("#add_news_button").after("<a class=\"news_link_button btn btn-default btn-inc\" href=\"/admin/<?php echo $inc_admin_page; ?>/category/"+news_val+"\" target='_blank'>Перейти к редактированию</a>");
            } else {
                $(".news_link_button").remove();
            }
        }
        <?php
        }
        ?>
    });
</script>