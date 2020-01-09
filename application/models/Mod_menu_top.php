<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Меню верхнего уровня
 */

class Mod_menu_top extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем меню
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        $menu_id    = $params[0];
        $statuses   = array(1, 3, 4);

        $this->db->select('page_id, page_pid, page_name, page_url, page_status, page_redirect, page_link_title');
        $this->db->from('w_pages');
        $this->db->where('page_menu_id =', $menu_id);
        $this->db->where('page_lang_id =', LANG);
        $this->db->where_in('page_status', $statuses);
        $this->db->order_by('page_sort', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0)
		{
            $forest =& $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), 0);
            $this->tree->set_tree ($forest);
            $this->tree->set_top ($forest, 'page_id', 'page_pid', PAGE_ID);
            $top = $this->tree->get_top ();

            if($this->cms_user->get_user_id()){
	            $max = max(array_column($forest, 'page_id'));
	            array_push($forest,
		            array (
			            'page_id' => $max+1,
			            'page_pid' => 0,
			            'page_name' => lang('global_exit'),
			            'page_url' => '-/exit',
			            'page_status' => 1,
			            'page_redirect' => '',
			            'page_link_title' => lang('global_exit')
		            )
	            );
            }

            return get_bootstrap4_menu ($forest, 'page_id', 'page_pid', 'page_name', 'page_url', 'page_link_title', $link = '/', 'page_status', 3, $top, 'active', '', 0, false);
        }
    }
}
