<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Вспомогательные функции для подключения модулей
 */

class Cms_module extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Вызываем модуль
     *
     * @access  public
     * @param   int
     * @return  int
     */
    function get_output($params = array())
    {
        $id = (isset($params[0])) ? $params[0] : false;

        $query = $this->db->get_where('w_cms_modules', array('module_id' => $id));

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            $model = basename($row->module_file, '.php');
            $this->load->model($model);
            return $this->$model->get_output(array());
        }
        else
        {
            return '';
        }
    }
}