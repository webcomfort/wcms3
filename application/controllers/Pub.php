<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Контроллер внешних вызовов */

class Pub extends CI_Controller {

    function index()
    {
        // Функции работы с пользователями и сессии
        $this->load->model('cms_user');
        $this->load->library('session');

        if (
			preg_match("/^[-a-zA-Z0-9_]*$/",$this->uri->segment(1)) &&
			preg_match("/^p_([-a-zA-Z0-9_])*$/",$this->uri->segment(2))
		)
        {
			$this->load->model($this->uri->segment(1));
            echo $this->{$this->uri->segment(1)}->{$this->uri->segment(2)}();
		}
    }
}