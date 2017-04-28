<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Карта сайта
 */

class Mod_site_map extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Собираем карту
     *
     * @access	public
     * @return	string
     */
    function get_output($params)
    {
        $menues = $this->config->item('cms_site_menues');
        $output = '';
        
        foreach ($menues as $key => $value) {
            if ($value['map']) $output .= $this->_get_list($key);
        }
        
        return $output;
    }

    // ------------------------------------------------------------------------
    
    /**
     * Делаем выборку меню
     *
     * @access	private
     * @return	string
     */
    function _get_list($menu_id)
    {
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
            
			return get_ul_menu ($forest, 'page_id', 'page_pid', 'page_name', 'page_url', 'page_link_title', $link = '/', 'page_status', 3, $top, 'active', false, '');
        }
    }

    // ------------------------------------------------------------------------
}