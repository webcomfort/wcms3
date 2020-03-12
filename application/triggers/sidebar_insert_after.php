<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка
 *
 * @action	insert
 * @mode	after
 */

$this->CI->db->select_max('sidebar_id', 'id');
$query = $this->CI->db->get('w_sidebar');
$row = $query->row();
$id = $row->id;
$this->CI->db->cache_delete_all();

// Права доступа к элементам
$this->CI->cms_user->insert_item_rights($id, 'sidebar');
