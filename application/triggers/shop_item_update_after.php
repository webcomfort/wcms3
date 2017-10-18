<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка картинок, включений и индексация
 *
 * @action	update
 * @mode	after
 */

$id = $this->rec;
$articles = '';
$this->CI->load->library('trigger');
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

// Изображения
if ($_FILES['pic']['tmp_name'] != '')
{
	$this->CI->load->library('image_lib');
    $dimensions = $this->CI->config->item('cms_shop_images');

    $this->CI->image_lib->src_img_convert($this->CI->config->item('cms_shop_dir'), $id);

    foreach ($dimensions as $key => $value)
    {
	    $this->CI->image_lib->thumb_create($this->CI->config->item('cms_shop_dir'), $id, $key, $value['width'], $value['height']);
	}
}

// ------------------------------------------------------------------------

// Заносим данные в таблицу пересечений

// Удаляем старые записи
$this->CI->db->delete('w_shop_items_cats', array('item_id' => $id));

// Вносим новые записи
if(is_array($this->CI->input->post('item_cats_'.$id, TRUE))) {
    foreach ($this->CI->input->post('item_cats_'.$id, TRUE) as $value) {
        $data = array(
            'sic_id' => '',
            'item_id' => $id,
            'cat_id' => trim($value)
        );

        $this->CI->db->insert('w_shop_items_cats', $data);
    }
}

// ------------------------------------------------------------------------

// Пустой пункт в корзину для статей
$data = array(
    'id'			=> '',
    'pid'			=> 0,
    'description'	=> 'Изменение статей товара '.$newvals['item_name'],
    'updated'		=> date('Y-m-d H:i:s'),
    'user'			=> $this->CI->cms_user->get_user_id(),
    'host'			=> $this->CI->input->ip_address(),
    'operation'		=> 'update',
    'tab'			=> 'w_shop_items',
    'rowkey'		=> $id,
    'col'			=> '',
    'files'		    => '',
    'oldval'		=> '',
    'newval'		=> ''
);
$this->CI->db->insert('w_changelog', $data);
$last_basket_element = $this->CI->trigger->get_last_basket_element();

// --------------------------------------------------------------------
// Статьи

$this->CI->db->select('article_id');
$this->CI->db->where('article_pid', $id);
$this->CI->db->where('article_pid_type', 'shop');
$query = $this->CI->db->get('w_pages_articles');
$total = $query->num_rows();
$i = 0;

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    if (preg_match("/^page_article_order_([1-9][0-9]*)$/", $key, $matches))
    {
        $this->CI->db->select('article_id');
        $this->CI->db->where('article_pid', $id);
        $this->CI->db->where('article_pid_type', 'shop');
        $this->CI->db->where('article_order', $value);
        $query = $this->CI->db->get('w_pages_articles');

        if ($query->num_rows() > 0)
        {
            $row = $query->row();

            $this->CI->trigger->change_relative ($row->article_id, $last_basket_element, 'w_pages_articles', 'article_id', 'article_text', 'Изменение статей товара ', $oldvals['item_name']);

            $data = array(
                'article_order'     => $value,
                'article_bg_id'     => $this->CI->input->post('page_article_bg_'.$matches[1]),
                'article_view_id'   => $this->CI->input->post('page_article_view_'.$matches[1]),
                'article_place_id'  => $this->CI->input->post('page_article_place_'.$matches[1]),
                'article_text'      => $this->CI->input->post('page_article_'.$matches[1], false)
            );
            $this->CI->db->where('article_id', $row->article_id);
            $this->CI->db->update('w_pages_articles', $data);
        }
        else
        {
            $data = array(
                'article_id' 		=> '',
                'article_pid'	    => $id,
                'article_pid_type'  => 'shop',
                'article_order' 	=> $value,
                'article_bg_id'     => $this->CI->input->post('page_article_bg_'.$matches[1]),
                'article_view_id'   => $this->CI->input->post('page_article_view_'.$matches[1]),
                'article_place_id'  => $this->CI->input->post('page_article_place_'.$matches[1]),
                'article_text' 		=> $this->CI->input->post('page_article_'.$matches[1], false)
            );

            $this->CI->db->insert('w_pages_articles', $data);
        }

        // Индексирование статей
        if($this->CI->config->item('cms_site_indexing'))
        {
            $articles .= $this->CI->input->post('page_article_'.$matches[1]);
        }

        $i++;
    }
}

// Если общее число статей уменьшилось
if ($total > $i) {
    for ($j = $i+1; $j <= $total; $j++) {
        // Удаление лишних статей
        $query = $this->CI->db->get_where('w_pages_articles', array('article_pid' => $id, 'article_pid_type' => 'shop'));

        if ($query->num_rows() > 0)
        {
            foreach ($query->result() as $row)
            {
                if($row->article_order == $j) $this->CI->trigger->delete_relative($row->article_id, $last_basket_element, 'w_pages_articles', 'article_id', 'Статья', '');
            }
        }
    }
}

// ------------------------------------------------------------------------

// Индексирование статей
if($this->CI->config->item('cms_site_indexing'))
{
    $this->CI->load->library('search');
    $this->CI->load->helper('text');

    $shop_page = $this->CI->Cms_shop->get_shop_page();
    $url = '/'.$shop_page.'/item/'.$newvals['item_url'];
    $old_url = '/'.$shop_page.'/item/'.$oldvals['item_url'];

    if ($url != $old_url || $newvals['item_active'] == 0) $this->CI->search->index_delete($old_url);

    if($newvals['item_active']) {
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

// ------------------------------------------------------------------------
// Мог измениться тип объекта или в старый тип могли быть добавлены новые поля,
// поэтому UPDATE в данном случае применить сложно, проще просто стереть старые
// данные и заново внести их в таблицу
$fields_inserted = array();

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
        if(!$this->CI->input->post('item_field_'.$row->field_id, TRUE) && $row->field_type_back != 8){
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