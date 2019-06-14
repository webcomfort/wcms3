<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для вставок
 */

class Cms_inserts extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

	/**
	 * UI для вставки в статью
	 *
	 * @access  public
	 * @return  string
	 */
	function p_get_insert_ui()
	{
		if ($this->cms_user->get_group_admin())
		{
			$article_id = $this->input->get('aid', TRUE);
			$type_id = $this->input->get('tid', TRUE);
			$data  = $this->config->item('cms_inserts');
			if(isset($data[$type_id])){
				$adm_model = $data[$type_id]['adm_model'];
				$adm_function = $data[$type_id]['adm_function'];
				$this->load->model($adm_model);
				echo $this->$adm_model->$adm_function($article_id);
			}
		}
	}
}