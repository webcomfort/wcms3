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
#process {
	display: inline-block;
	padding: 0 1em;
	margin-left: 10px;
	line-height: 34px;
}
</style>
<script>
$(document).ready(function() {
    var reindex_modules = '.json_encode ($this->config->item('cms_site_reindexing')).';
    var load = \'<img src="/public/admin/img/load.gif"> \';
    $(document).on("click", "#reindex", function(e) {
        e.preventDefault();
        
        $.post("/adm_index/p_reindex", { '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
            Object.keys(reindex_modules).forEach(function(key){
			    var item = reindex_modules[key];
			    $("#process").removeClass("label-success").addClass("label-default");
			    $("#process").html(load + item);
			    $.post("/"+key+"/p_reindex", { '.$this->security->get_csrf_token_name().': "'.$this->security->get_csrf_hash().'" }, function(result){
	                $("#process").removeClass("label-default").addClass("label-success");
			        $("#process").html("Индексация завершена");
				});
			});
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
		    $filters = '
        <div class="row">
            <div class="col-xs-12"><div class="p20 ui-block"><button id="reindex" class="btn btn-primary">Переиндексировать</button><span class="label label-default" id="process"></span></div></div>
        </div>
        ';
		    return $filters;
	    }
    }

    // ------------------------------------------------------------------------

	/**
	 * Функция для переиндексации (внешний вызов)
	 *
	 * @access  public
	 * @return  string
	 */
	function p_reindex() {
		$rights = $this->cms_user->get_user_rights();
		if ( is_array($rights) && ($rights[basename(__FILE__)]['edit'] || $rights[basename(__FILE__)]['copy'] || $rights[basename(__FILE__)]['add']) )
		{
			$this->db->query('TRUNCATE TABLE w_indexing_index');
			$this->db->query('TRUNCATE TABLE w_indexing_link');
			$this->db->query('TRUNCATE TABLE w_indexing_word');
			echo 'Индекс очищен!';
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