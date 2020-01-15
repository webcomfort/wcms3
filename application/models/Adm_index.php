<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Управление
 */

class Adm_index extends CI_Model {

    function __construct()
    {
        if($this->input->post('PME_sys_rec', TRUE) === '0' || $this->input->post('PME_sys_savecopy', TRUE) || $this->input->post('PME_sys_savedelete', TRUE)) header ('Location: /admin/'.$this->uri->segment(2));
	    if($this->input->post('PME_sys_morechange', TRUE)) {
		    header ('Location: /admin/'.$this->uri->segment(2).'/?PME_sys_operation=PME_op_Change&PME_sys_rec='.$this->input->post('PME_sys_rec', TRUE).(($this->input->post('PME_sys_cur_tab', TRUE)) ? '&PME_sys_cur_tab='.$this->input->post('PME_sys_cur_tab', TRUE) : ''));
	    }
        $this->load->model('Cms_utils');
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
	 * Функция, отдающая дополнительные параметры в <head>
	 *
	 * @access	public
	 * @return	string
	 */
    function get_meta()
    {
    	$meta = '
<style>
.process {
	display: inline-block;
	padding: 0 1em;
	margin-left: 10px;
	line-height: 34px;
}
</style>
<script>
$(document).ready(function() {
    var load = \'<img src="/public/admin/img/load.gif"> \';
    $(".reindex").on("click", function(e) {
        e.preventDefault();
        
        var module = $(this).data("module");
        $.post("/"+module+"/p_reindex", { '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
            $(".process_"+module).removeClass("label-default").addClass("label-success");
	        $(".process_"+module).html("Индексация завершена");
	        console.log(result);
		});
    });
});
</script>
';
        return $meta;
    }

    // ------------------------------------------------------------------------

    /**
	 * Функция, отдающая фильтры
	 *
	 * @access	public
	 * @return	string
	 */
    function get_filters()
    {
	    if($this->config->item('cms_site_indexing')){
		    $filters = '<div class="row">';
	    	foreach ($this->config->item('cms_site_reindexing') as $key => $value){
			    $filters .= '<div class="col-xs-12"><div class="p20 ui-block"><button data-module="'.$key.'" class="btn btn-primary reindex">'.$value.'</button><span class="label label-default process process_'.$key.'"></span></div></div>';
		    }
		    $filters .= '</div>';
		    return $filters;
	    }
    }

    // ------------------------------------------------------------------------

    /**
	 * Функция, отдающая основной интерфейс
	 *
	 * @access	public
	 * @return	string
	 */
    function get_output()
    {
        return '';
    }
}