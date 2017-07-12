<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CRUD модуль - вспомогательные функции
 */

class Cms_utils extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
	 * Поиск номера страницы с подключенным модулем
	 *
	 * @access	public
     * @param   string
	 * @return	int
	 */
	function get_admin_page_id($model)
	{
        $this->db->select('p.cms_page_id AS id');
        $this->db->from('w_cms_modules AS m');
        $this->db->join('w_cms_pages AS p', 'm.module_id = p.cms_page_model_id', 'left');
        $this->db->where('m.module_file', trim($model).'.php');
        $query = $this->db->get();
        if ($query->num_rows() > 0){
            $row = $query->row();
            return $row->id;
        } else {
            return 0;
        }
	}

    // ------------------------------------------------------------------------
}