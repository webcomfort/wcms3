<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода текста в сайдбар
 */

class Mod_sidetext extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем баннеры
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        return $params[0];
    }
}
