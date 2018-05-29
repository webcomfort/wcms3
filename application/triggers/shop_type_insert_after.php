<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка текстов, включений и индексация
 *
 * @action	insert
 * @mode	after
 */

$this->CI->db->select_max('type_id', 'id');
$query = $this->CI->db->get('w_shop_types');
$row = $query->row();
$id = $row->id;
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
    // Поля

    if (preg_match("/^field_([0-9]*)$/", $key, $matches))
    {
	    $type = $this->CI->input->post('field_type_'.$matches[1]);

	    if($type) {
		    $values         = (is_array($this->CI->input->post( 'field_values_' . $matches[1] ))) ? implode( ",", $this->CI->input->post( 'field_values_' . $matches[1] ) ) : '';
		    $default_values = (is_array($this->CI->input->post( 'field_default_values_' . $matches[1] ))) ? implode( ",", $this->CI->input->post( 'field_default_values_' . $matches[1] ) ) : '';
	    } else {
		    $values         = '';
		    $default_values = $this->CI->input->post( 'field_default_values_' . $matches[1] );
	    }

    	$data = array(
            'tf_id'                 => '',
            'type_id'               => $id,
            'field_id'              => $matches[1],
            'field_values'          => $values,
            'field_default_values'  => (is_array($default_values)) ? $default_values : '',
            'field_filter'          => ($this->CI->input->post('field_filter_'.$matches[1])) ? 1 : 0,
            'field_modification'    => ($this->CI->input->post('field_modification_'.$matches[1])) ? 1 : 0,
            'field_table'           => ($this->CI->input->post('field_table_'.$matches[1])) ? 1 : 0,
            'field_order'           => $this->CI->input->post('field_order_'.$matches[1])
        );

        $this->CI->db->insert('w_shop_types_fields', $data);

        $i++;
    }
}