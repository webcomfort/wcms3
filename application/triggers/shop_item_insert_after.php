<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка текстов, включений и индексация
 *
 * @action	insert
 * @mode	after
 */

$this->CI->db->select_max('item_id', 'id');
$query = $this->CI->db->get('w_shop_items');
$row = $query->row();
$id = $row->id;
// $id родителя записи при операции копирования
if($this->CI->input->post('PME_sys_savecopy', TRUE)) {
    $field_base = 'item_cats';
    foreach ($this->CI->input->post() as $k => $v) {
        if (preg_match_all('/^'.$field_base.'_([0-9]*)$/', $k, $matches)) {
            $pid = $matches[1][0];
        }
    }
}
else $pid = 0;
$fields_inserted = array();
$this->CI->db->cache_delete_all();

// Права доступа к элементам
$this->CI->cms_user->insert_item_rights($id, 'shop');

// ------------------------------------------------------------------------

// Изображения
$files = $this->CI->input->post('pic_files', true);
if (is_array($files))
{
	$this->CI->load->library( 'image_lib' );
	foreach ($files as $value) {
		$this->CI->image_lib->src_file_move ($value, $this->CI->config->item( 'cms_shop_dir' ), $id, false, true, $this->CI->config->item( 'cms_shop_images' ), true);
	}
}

// ------------------------------------------------------------------------

// Заносим данные в таблицу пересечений с категориями
if(is_array($this->CI->input->post('item_cats_'.$pid, TRUE))) {
    foreach ($this->CI->input->post('item_cats_'.$pid, TRUE) as $value) {
        $data = array(
            'sic_id' => '',
            'item_id' => $id,
            'cat_id' => trim($value)
        );

        $this->CI->db->insert('w_shop_items_cats', $data);
    }
}

// ------------------------------------------------------------------------

$this->CI->db->where('article_pid', $id);
$this->CI->db->where('article_pid_type', 'shop');
$this->CI->db->delete('w_pages_articles');

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
    // Статьи

    if (preg_match("/^page_article_order_([1-9][0-9]*)$/", $key, $matches))
    {
        $bg     = $this->CI->input->post('page_article_bg_'.$matches[1]);
	    $view   = $this->CI->input->post('page_article_view_'.$matches[1]);
        $place  = $this->CI->input->post('page_article_place_'.$matches[1]);

    	$data = array(
            'article_id' 		=> '',
            'article_pid'	    => $id,
            'article_pid_type'  => 'shop',
            'article_order' 	=> $value,
            'article_bg_id'     => ($bg) ? $bg : '',
            'article_view_id'   => ($view) ? $view : '',
            'article_place_id'  => ($place) ? $place : '',
            'article_text' 		=> $this->CI->input->post('page_article_'.$matches[1], false)
        );

        $this->CI->db->insert('w_pages_articles', $data);

        // Индексирование статей
        if($this->CI->config->item('cms_site_indexing'))
        {
            $articles .= $this->CI->input->post('page_article_'.$matches[1]);
        }
    }
}


// ------------------------------------------------------------------------

// Индексирование текстов
if($this->CI->config->item('cms_site_indexing'))
{
    $this->CI->load->library('search');
    $this->CI->load->helper('text');

    if($newvals['item_active']) {
        $shop_page = $this->CI->Cms_shop->get_shop_page();
        $url = '/' . $shop_page . '/item/' . $newvals['item_url'];
        $title = $newvals['item_name'];
        $article_words = text2words(html_entity_decode($articles));
        $title_words = text2words($title);
        $short = word_limiter($article_words, 50);
        $lang_array = $this->CI->config->item('cms_lang');
        $lang = $lang_array[$this->CI->session->userdata('w_alang')]['search'];

        $words_array = $this->CI->search->index_prepare($article_words . ' ' . $title_words, $lang);
        $this->CI->search->index_insert($url, $title, $short, $words_array);
    }
}

// ------------------------------------------------------------------------

// Подключения
$this->CI->Cms_inclusions->admin_inclusions_insert_update($id, 'shop');
// Теги
$this->CI->Cms_tags->admin_tags_insert_update($id, 'shop');

// ------------------------------------------------------------------------
// Поля

// Удаляем старые записи
$this->CI->db->where('item_id', $id);
$this->CI->db->delete('w_shop_items_params');

// В том случае, если для этого типа есть поля, но они отсутствуют в массиве POST (пустые массивы), вносим пустое значение
$this->CI->db->select('tf.field_id, field_type_back')
    ->from('w_shop_types_fields AS tf')
    ->join('w_shop_fields AS f', 'tf.field_id = f.field_id')
    ->where('type_id', $this->CI->input->post('PME_data_item_type_id', TRUE));
$query  = $this->CI->db->get();
if ($query->num_rows() > 0) {
    foreach ($query->result() as $row) {
	    if(!$this->CI->input->post('item_field_'.$row->field_id, TRUE) && $this->CI->input->post('item_field_'.$row->field_id, TRUE) != 0 && $row->field_type_back != 8){
            $data = array(
                'par_id' => '',
                'item_id' => $id,
                'field_id' => $row->field_id,
                'par_value' => ''
            );
            $this->CI->db->insert('w_shop_items_params', $data);
        }
    }
}

// Заносим данные в таблицу значений
foreach ($this->CI->input->post() as $k => $v) {

    // если поле соответствует шаблону
    if (preg_match_all('/^item_field_([a-zA-z0-9_]*)$/', $k, $matches)) {

        $pieces = explode("_", $matches[1][0]);
        $field_id = trim($pieces[0]);

        // Если поле составное
        if(count($pieces) > 1) {
            // если это дата
            if ($this->CI->input->post('item_field_' . $pieces[0] . '_year', TRUE) && $this->CI->input->post('item_field_' . $pieces[0] . '_mon', TRUE) && $this->CI->input->post('item_field_' . $pieces[0] . '_day', TRUE)) {
                $value = trim(intval($this->CI->input->post('item_field_' . $pieces[0] . '_year', TRUE))) . '-' . sprintf("%02d", trim(intval($this->CI->input->post('item_field_' . $pieces[0] . '_mon', TRUE)))) . '-' . sprintf("%02d", trim(intval($this->CI->input->post('item_field_' . $pieces[0] . '_day', TRUE))));
            }
        } else {
            if(is_array($v)){
                $value = implode (",", $v);
            } else {
                $value = $v;
            }
        }

        if(!in_array($field_id, $fields_inserted)) {
            $data = array(
                'par_id' => '',
                'item_id' => $id,
                'field_id' => $field_id,
                'par_value' => $value
            );

            $this->CI->db->insert('w_shop_items_params', $data);
            $fields_inserted[] = $field_id;
        }
    }

}
