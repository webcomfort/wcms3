<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка картинок, включений и индексация
 *
 * @action	insert
 * @mode	after
 */

$this->CI->db->select_max('news_id', 'id');
$query = $this->CI->db->get('w_news');
$row = $query->row();
$id = $row->id;
// $id родителя записи при операции копирования
if($this->CI->input->post('PME_sys_savecopy', TRUE)) {
    $field_base = 'news_rubrics';
    foreach ($this->CI->input->post() as $k => $v) {
        if (preg_match_all('/^'.$field_base.'_([0-9]*)$/', $k, $matches)) {
            $pid = $matches[1][0];
        }
    }
}
else $pid = 0;
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

// Изображения
$files = $this->CI->input->post('pic_files', true);
if (is_array($files))
{
	$this->CI->load->library( 'image_lib' );
	foreach ($files as $value) {
		$this->CI->image_lib->src_file_move ($value, $this->CI->config->item( 'cms_news_dir' ), $id, false, true, $this->CI->config->item('cms_news_images'), true);
	}
}

// ------------------------------------------------------------------------

// Заносим данные в таблицу пересечений
if(is_array($this->CI->input->post('news_rubrics_'.$pid, TRUE))) {
    foreach ($this->CI->input->post('news_rubrics_'.$pid, TRUE) as $value) {
        $data = array(
            'ncc_id'		=> '',
            'news_id' 		=> $id,
            'news_cat_id'	=> trim($value)
        );

        $this->CI->db->insert('w_news_categories_cross', $data);
    }
}

// ------------------------------------------------------------------------

$this->CI->db->where('article_pid', $id);
$this->CI->db->where('article_pid_type', 'news');
$this->CI->db->delete('w_pages_articles');

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
    // Статьи

    if (preg_match("/^page_article_order_([1-9][0-9]*)$/", $key, $matches))
    {
        $data = array(
            'article_id' 		=> '',
            'article_pid'	    => $id,
            'article_pid_type'  => 'news',
            'article_order' 	=> $value,
            'article_bg_id'     => $this->CI->input->post('page_article_bg_'.$matches[1]),
            'article_view_id'   => $this->CI->input->post('page_article_view_'.$matches[1]),
            'article_place_id'  => $this->CI->input->post('page_article_place_'.$matches[1]),
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

// Индексирование статей
if($this->CI->config->item('cms_site_indexing') && $newvals['news_active'])
{
    $this->CI->load->library('search');
    $this->CI->load->helper('text');

    if($newvals['news_active']) {
        $url = '/post/' . $newvals['news_url'];
        $title = $newvals['news_name'];
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
$this->CI->Cms_inclusions->admin_inclusions_insert_update($id, 'news');
// Теги
$this->CI->Cms_tags->admin_tags_insert_update($id, 'news');