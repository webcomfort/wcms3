<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Администраторское меню
 */

class Mod_admin_menu extends CI_Model {

    private $page_array = array();

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Собираем административное меню
     *
     * @access	private
     * @return	string
     */
    function get_output($params)
    {
        $this->db->select('cms_page_id, cms_page_pid, cms_page_name, cms_page_model_id, cms_page_status');
        $this->db->from('w_cms_pages');
        $this->db->where('cms_page_status =', 1);
        $this->db->or_where('cms_page_status =', 3);
        $this->db->order_by('cms_page_pid', 'asc');
        $this->db->order_by('cms_page_sort', 'asc');
        $query = $this->db->get();

        if ($query->num_rows() > 0)
		{
            $this->_check_pages($query->result_array(), $this->cms_user->get_user_modules());
            $this->page_array = array_unique($this->page_array);

            $forest = $this->tree->get_tree('cms_page_id', 'cms_page_pid', $query->result_array(), 0);

            return get_bootstrap_menu ($forest, 'cms_page_id', 'cms_page_pid', 'cms_page_name', 'cms_page_id', '', $link = '/admin/', 'cms_page_status', 3, $this->uri->segment(2), 'active', '', 0, $this->page_array);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Массив страниц, оставляем только те id, на которые у пользователя есть права
     *
     * @access	private
     * @param	array
     * @param	array
     * @return	array
     */
    function _check_pages($result, $modules)
    {
        $target = array();

        foreach ($result as $row)
        {
            if(array_key_exists($row['cms_page_model_id'], $modules))
            {
                $this->page_array[] = $row['cms_page_id'];
                if ($row['cms_page_pid'] != 0) $this->_get_parent_pages($result, $row['cms_page_pid']);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Получаем массив из id родительских страниц, на которые у пользователя есть права
     *
     * @access	private
     * @param	array
     * @param	int
     * @param	array
     * @return	array
     */
    function _get_parent_pages($result, $pid, $target = array())
    {
        reset($result);

        foreach ($result as $row)
        {
            if($row['cms_page_id'] == $pid)
            {
                $this->page_array[] = $row['cms_page_id'];
                if ($row['cms_page_pid'] != 0) $this->_get_parent_pages($result, $row['cms_page_pid'], $target);
            }
        }
    }
}