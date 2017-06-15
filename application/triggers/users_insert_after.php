<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Установка прав для нового пользователя, генерация пароля
 *
 * @action	insert
 * @mode	after
 */

// Id пользователя
$this->CI->db->select_max('user_id','id');
$query = $this->CI->db->get('w_user');
$row = $query->row();
$id = $row->id;

// Установка прав

$this->CI->db->select('module_id, module_name');
$this->CI->db->from('w_cms_modules');
$this->CI->db->where('module_active', 1);
$this->CI->db->where('module_type', 2);
$this->CI->db->order_by('module_sort', 'asc');
$query = $this->CI->db->get();

if ($query->num_rows() > 0)
{
  foreach ($query->result() as $row)
  {
    if($this->CI->input->post('PME_data_mod'.$row->module_id) && is_array($this->CI->input->post('PME_data_mod'.$row->module_id)))
    {
      $view   = (in_array("V", $this->CI->input->post('PME_data_mod'.$row->module_id))) ? 1 : 0;
      $add    = (in_array("A", $this->CI->input->post('PME_data_mod'.$row->module_id))) ? 1 : 0;
      $edit   = (in_array("C", $this->CI->input->post('PME_data_mod'.$row->module_id))) ? 1 : 0;
      $copy   = (in_array("P", $this->CI->input->post('PME_data_mod'.$row->module_id))) ? 1 : 0;
      $delete = (in_array("D", $this->CI->input->post('PME_data_mod'.$row->module_id))) ? 1 : 0;
	  $active = (in_array("Y", $this->CI->input->post('PME_data_mod'.$row->module_id))) ? 1 : 0;

      if($view || $add || $edit || $copy || $delete || $active)
      {
        $data = array(
           'rule_id'        => '',
           'rule_user_id'   => $id,
           'rule_model_id'  => $row->module_id,
           'rule_view'      => $view,
           'rule_add'       => $add,
           'rule_edit'      => $edit,
           'rule_copy'      => $copy,
           'rule_delete'    => $delete,
           'rule_active'    => $active
        );

        $this->CI->db->insert('w_user_rules', $data);
      }
    }
  }
}

// ------------------------------------------------------------------------

// Генерируем хэш и отправляем письмо с возможностью задать/изменить пароль
$this->CI->cms_user->remember_confirmation($newvals['user_email']);