<!-- Modal -->
<div class="modal fade" id="BgModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Добавить фон</h4>
      </div>
      <div class="modal-body">
          <div id="bg_alert"></div>
          <?php echo form_open_multipart('', array('id' => 'BgAddForm')); ?>
          <input type="hidden" name="article_num" id="article_num" value="">
          <div class="form-group">
              <label for="bg_name">Название фона</label>
              <input type="text" class="form-control" id="bg_name" name="bg_name" placeholder="Введите название фона" required>
          </div>
          <div class="form-group">
              <label for="bg_file">Загрузите фон</label><br>
              <div class="dropzonearea" id="bgDropzone"><div class="dz-default dz-message"><span>Перетащите сюда файлы для загрузки</span></div></div>
              <div id="bg_hidden"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" id="BgSaveForm">Сохранить</button>
      </div>
    </div>
  </div>
</div>
<script>
    jQuery(document).ready(function() {

        var bgDropzone = new Dropzone("div#bgDropzone", {
            url: "/cms_myedit/p_drop_upload/",
            paramName: "file",
            maxFiles: 1,
            acceptedFiles: "image/*",
            parallelUploads: 1,
            chunking: true,
            addRemoveLinks: true,
            chunkSize: 3145728,
            retryChunks: true,
            retryChunksLimit: 3,
            renameFile: function(file){
                var fname = file.name;
                var ext = fname.slice((Math.max(0, fname.lastIndexOf(".")) || Infinity) + 1);
                return getRandom(1,1000)+"_"+hashCode(file.name)+"."+ext;
            },
            init: function() {
                this.on("sending", function (file, xhr, formData) {
                    formData.append("csrf_wcms_token", $("input[name=csrf_wcms_token]").val());
                    formData.append("module", "Adm_backgrounds.php");
                    formData.append("file_name", file.upload.filename);
                });
                this.on("success", function (file, response) {
                    $("#bg_hidden").append($('<input type="hidden" ' +
                        'name="pic_files[]" ' +
                        'value="' + file.upload.filename + '">'));
                });
                this.on("removedfile", function (file) {
                    var filename;
                    if (typeof file.upload === "undefined"){
                        filename = file.name;
                    } else {
                        filename = file.upload.filename;
                    }
                    $.ajax({
                        method: "POST",
                        url: "/cms_myedit/p_drop_delete/",
                        data: {
                            "csrf_wcms_token": $("input[name=csrf_wcms_token]").val(),
                            "module": "Adm_backgrounds.php",
                            "file_name": filename
                        }
                    });
                    $("input[value='"+filename+"']").remove();
                });
            }
        });

        $(document).on("click", ".add_bg_button", function () {
            var articleId = $(this).data('num');
            $(".modal-body #article_num").val( articleId );
        });

        $('#BgSaveForm').click(function(){

            var formData = new FormData(document.getElementById('BgAddForm'));

            $.ajax({
                url: "/adm_backgrounds/p_save_bg",
                type: "POST",
                data:  formData,
                contentType: false,
                cache: false,
                processData:false,
                success: function(result){
                    var json = $.parseJSON(result);
                    if(json.result == '1') {
                        $.post('/adm_backgrounds/p_return_bg', {'csrf_wcms_token': $("input[name=csrf_wcms_token]").val(), 'bg_id': json.bg_id, 'article_num': json.article_num}, function (result) {
                            var json = $.parseJSON(result);
                            $('#BgModal').modal('hide');
                            $("#BgAddForm")[0].reset();
                            bgDropzone.removeAllFiles();
                            $('select[name=page_article_bg_'+json.article_num+']').empty().append(json.select);
                        });
                    } else {
                        $("#bg_alert").html(json.alert);
                    }
                },
                error: function(){

                }
            });
        });
    });
</script>