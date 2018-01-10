<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка текстов, включений и индексация
 *
 * @action	update
 * @mode	after
 */

$this->CI->load->library('trigger');
$id = $this->rec;
$this->CI->db->cache_delete_all();
$changed = array();

// ------------------------------------------------------------------------

// Пустой пункт в корзину
$data = array(
    'id'			=> '',
    'pid'			=> 0,
    'description'	=> 'Изменение параметров полей типа '.$newvals['type_name'],
    'updated'		=> date('Y-m-d H:i:s'),
    'user'			=> $this->CI->cms_user->get_user_id(),
    'host'			=> $this->CI->input->ip_address(),
    'operation'		=> 'update',
    'tab'			=> 'w_shop_types',
    'rowkey'		=> $id,
    'col'			=> '',
    'files'		    => '',
    'oldval'		=> '',
    'newval'		=> ''
);
$this->CI->db->insert('w_changelog', $data);
$last_basket_element = $this->CI->trigger->get_last_basket_element();

// --------------------------------------------------------------------

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
	// Поля

    if (preg_match("/^field_([0-9]*)$/", $key, $matches))
	{
        $changed[] = $matches[1];
		$type = $this->CI->input->post('field_type_'.$matches[1]);

	    $this->CI->db->select('tf.tf_id, f.field_name');
        $this->CI->db->from('w_shop_types_fields AS tf');
        $this->CI->db->join('w_shop_fields AS f', 'f.field_id = tf.field_id', 'left');
        $this->CI->db->where('type_id', $id);
        $this->CI->db->where('tf.field_id', $matches[1]);
        $query = $this->CI->db->get();

		if ($query->num_rows() > 0)
		{
			$row = $query->row();

            $this->CI->trigger->change_relative ($row->tf_id, $last_basket_element, 'w_shop_types_fields', 'tf_id', 'field_values', 'Изменение значений поля '.$row->field_name.' типа ', $oldvals['type_name']);
            $this->CI->trigger->change_relative ($row->tf_id, $last_basket_element, 'w_shop_types_fields', 'tf_id', 'field_default_values', 'Изменение значений по умолчанию поля '.$row->field_name.' типа ', $oldvals['type_name']);

			if($type) {
				$values         = implode( ",", $this->CI->input->post( 'field_values_' . $matches[1] ) );
				$default_values = implode( ",", $this->CI->input->post( 'field_default_values_' . $matches[1] ) );
			} else {
				$values         = '';
				$default_values = $this->CI->input->post( 'field_default_values_' . $matches[1] );
			}

            $data = array(
	            'field_values'          => $values,
	            'field_default_values'  => $default_values,
                'field_filter'          => $this->CI->input->post('field_filter_'.$matches[1]),
                'field_modification'    => $this->CI->input->post('field_modification_'.$matches[1]),
                'field_table'           => $this->CI->input->post('field_table_'.$matches[1]),
                'field_order'           => $this->CI->input->post('field_order_'.$matches[1])
            );
            $this->CI->db->where('tf_id', $row->tf_id);
            $this->CI->db->update('w_shop_types_fields', $data);
		}
		else
		{
			if($type) {
				$values         = implode( ",", $this->CI->input->post( 'field_values_' . $matches[1] ) );
				$default_values = implode( ",", $this->CI->input->post( 'field_default_values_' . $matches[1] ) );
			} else {
				$values         = '';
				$default_values = $this->CI->input->post( 'field_default_values_' . $matches[1] );
			}

			$data = array(
                'tf_id'                 => '',
                'type_id'               => $id,
                'field_id'              => $matches[1],
                'field_values'          => $values,
                'field_default_values'  => $default_values,
                'field_filter'          => ($this->CI->input->post('field_filter_'.$matches[1])) ? 1 : 0,
                'field_modification'    => ($this->CI->input->post('field_modification_'.$matches[1])) ? 1 : 0,
                'field_table'           => ($this->CI->input->post('field_table_'.$matches[1])) ? 1 : 0,
                'field_order'           => $this->CI->input->post('field_order_'.$matches[1])
            );

            $this->CI->db->insert('w_shop_types_fields', $data);
		}

		$i++;
	}
}

// Удаление лишних полей
$this->CI->db->select('tf_id');
$this->CI->db->from('w_shop_types_fields');
$this->CI->db->where('type_id', $id);
if(count($changed) > 0) $this->CI->db->where_not_in('field_id', $changed);
$query = $this->CI->db->get();

if ($query->num_rows() > 0)
{
    foreach ($query->result() as $row)
    {
        $this->CI->trigger->delete_relative($row->tf_id, $last_basket_element, 'w_shop_types_fields', 'tf_id', 'Поле', '');
    }
}