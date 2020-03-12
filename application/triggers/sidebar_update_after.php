<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка
 *
 * @action	update
 * @mode	after
 */

$id = $this->rec;
$this->CI->db->cache_delete_all();

// Права доступа к элементам
$this->CI->cms_user->update_item_rights($id, 'sidebar');
