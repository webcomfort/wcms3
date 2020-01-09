<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Меню второго уровня
 */

class Mod_menu_second extends CI_Model {

    function __construct()
    {
	    $this->load->model('Cms_page');
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
        $ul_class   = $params[1];
        $statuses   = array(1, 3, 4);

        if ($menu_id == MENU_ID)
        {
            $this->db->select('page_id, page_pid, page_name, page_url, page_status, page_redirect');
            $this->db->from('w_pages');
            $this->db->where('page_menu_id =', $menu_id);
            $this->db->where('page_lang_id =', LANG);
            $this->db->where_in('page_status', $statuses);
            $this->db->order_by('page_sort', 'asc');
            $query = $this->db->get();

            if ($query->num_rows() > 0)
    		{
                if($top = $this->tree->get_top ())
                {
                    $forest =& $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), $top);
                }
                else
                {
                    $forest =& $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), 0);
                    $this->tree->set_tree($forest);
                    $this->tree->set_top ($forest, 'page_id', 'page_pid', PAGE_ID);
                    $top = $this->tree->get_top ();

                    $forest =& $this->tree->get_tree('page_id', 'page_pid', $query->result_array(), $top);
                }

			    $query_top = $this->db->get_where('w_pages', array('page_id' => $top), 1, 0);
			    if ($query_top->num_rows() > 0) {
			    	$row_top = $query_top->row();
			    	$link = '/'.$row_top->page_url;
			    } else {
				    $link = '/';
			    }

                $menu = get_ul_menu ($forest, 'page_id', 'page_pid', 'page_name', 'page_url', 'page_link_title', $link, 'page_status', 3, PAGE_ID, 'active', $ul_class);
				
                if($menu == '<ul></ul>' || $menu == '<ul class="'.$ul_class.'"></ul>' || $menu == '') return '';
                else return $menu;
            }
            else
            {
                return '';
            }
        }
        {
            return '';
        }
    }

    /**
     * Ищем верхний элемент
     *
     * @access  private
     * @param   array
     * @param   string
     * @param   string
     * @param   int
     * @return  void
     */
    function _get_top ($forest, $id_name, $parent_name, $active_id)
    {
        if (is_array($forest))
        {
            foreach ($forest as $tree)
            {
                if ($tree[$id_name] == $active_id)
                {
                    if ($tree[$parent_name] != 0)
                    {
                        $this->_get_top($this->forest, $id_name, $parent_name, $tree[$parent_name]);
                    }
                    else
                    {
                        $this->top = $tree[$id_name];
                    }
                }
                else
                {
                    if (isset($tree['nodes'])) $this->_get_top($tree['nodes'], $id_name, $parent_name, $active_id);
                }
            }
        }
    }
}
