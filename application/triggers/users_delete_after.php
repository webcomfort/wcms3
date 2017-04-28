<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Удаление прав для данного пользователя и отправка уведомления об удалении
 *
 * @action	delete
 * @mode	after
 */

// Удаление прав

$query = $this->CI->db->get_where('w_user_rules', array('rule_user_id' => $this->rec));

if ($query->num_rows() > 0)
{
    $this->CI->load->library('trigger');
    $last_basket_element = $this->CI->trigger->get_last_basket_element();

    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->rule_id, $last_basket_element, 'w_user_rules', 'rule_id', 'Право управления модулем', '');
    }
}

// Отправка уведомления
$this->CI->cms_user->send_delete_message($oldvals['user_email']);