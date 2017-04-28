<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода сквозных блоков
 */

class Mod_cross_blocks extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем блок
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        $label = $params[0];

        $query = $this->db->get_where('w_pages_cross_blocks', array('cross_block_label' => $label, 'cross_block_active' => 1, 'cross_block_lang_id' => LANG), 1);

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            return $row->cross_block_content;
        }
    }
}