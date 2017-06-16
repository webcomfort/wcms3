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
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

// Изображения
if ($_FILES['pic']['tmp_name'] != '')
{
	$this->CI->load->library('image_lib');
	$dimensions = $this->CI->config->item('cms_news_images');

    $this->CI->image_lib->src_img_convert($this->CI->config->item('cms_news_dir'), $id);

    foreach ($dimensions as $key => $value)
    {
	    $this->CI->image_lib->thumb_create($this->CI->config->item('cms_news_dir'), $id, $key, $value['width'], $value['height']);
	}
}

// ------------------------------------------------------------------------

// Заносим данные в таблицу пересечений
if(is_array($this->CI->input->post('news_rubrics_0', TRUE))) {
    foreach ($this->CI->input->post('news_rubrics_0', TRUE) as $value) {
        $data = array(
            'ncc_id'		=> '',
            'news_id' 		=> $id,
            'news_cat_id'	=> trim($value)
        );

        $this->CI->db->insert('w_news_categories_cross', $data);
    }
}

// ------------------------------------------------------------------------

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
    // Статьи

    if (preg_match("/^page_article_order_([1-9][0-9]*)$/", $key, $matches))
    {
        $this->CI->db->where('article_pid', $id);
        $this->CI->db->where('article_pid_type', 'news');
        $this->CI->db->delete('w_pages_articles');

        $data = array(
            'article_id' 		=> '',
            'article_pid'	    => $id,
            'article_pid_type'  => 'news',
            'article_order' 	=> $value,
            'article_bg_id'     => $this->CI->input->post('page_article_bg_'.$matches[1]),
            'article_view_id'   => $this->CI->input->post('page_article_view_'.$matches[1]),
            'article_place_id'  => $this->CI->input->post('page_article_place_'.$matches[1]),
            'article_text' 		=> $this->CI->input->post('page_article_'.$matches[1])
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

    $url 			= '/post/'.$newvals['news_url'];
    $title 			= $newvals['news_name'];
    $article_words 	= text2words(html_entity_decode($articles));
    $title_words 	= text2words($title);
    $short 			= word_limiter($article_words, 50);
    $lang_array 	= $this->CI->config->item('cms_lang');
    $lang			= $lang_array[$this->CI->session->userdata('w_alang')]['search'];

    $words_array = $this->CI->search->index_prepare($article_words . ' ' . $title_words, $lang);
    $this->CI->search->index_insert($url, $title, $short, $words_array);
}

// ------------------------------------------------------------------------

// Подключения
$this->CI->Cms_inclusions->admin_inclusions_insert_update($id, 'news');