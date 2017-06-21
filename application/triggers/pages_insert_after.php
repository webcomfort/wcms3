<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка текстов, включений и индексация
 *
 * @action	insert
 * @mode	after
 */

$this->CI->db->select_max('page_id', 'id');
$query = $this->CI->db->get('w_pages');
$row = $query->row();
$id = $row->id;
$this->CI->db->cache_delete_all();

// ------------------------------------------------------------------------

foreach ($this->CI->input->post(NULL, FALSE) as $key => $value)
{
    unset($data);

    // --------------------------------------------------------------------
	// Статьи

    if (preg_match("/^page_article_order_([1-9][0-9]*)$/", $key, $matches))
	{
        $this->CI->db->where('article_pid', $id);
        $this->CI->db->where('article_pid_type', 'pages');
        $this->CI->db->delete('w_pages_articles');

        $data = array(
            'article_id' 		=> '',
            'article_pid'	    => $id,
            'article_pid_type'  => 'pages',
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

// Подключения
$this->CI->Cms_inclusions->admin_inclusions_insert_update($id, 'pages');

// Индексирование статей
if($this->CI->config->item('cms_site_indexing'))
{
    $this->CI->load->library('search');
    $this->CI->load->helper('text');

    $url 			= ($newvals['page_status'] == '4') ? $url = '/' : '/'.$newvals['page_url'];
    $title 			= $newvals['page_name'];
    $article_words 	= text2words(html_entity_decode($articles));
    $title_words 	= text2words($title);
    $short 			= word_limiter($article_words, 50);
    $lang_array 	= $this->CI->config->item('cms_lang');
    $lang			= $lang_array[$this->CI->session->userdata('w_alang')]['search'];

    if ($newvals['page_status'] == '4' || $newvals['page_status'] == '3' || $newvals['page_url'] != $oldvals['page_url']) $this->CI->search->index_delete('/'.$oldvals['page_url']);

    $words_array = $this->CI->search->index_prepare($article_words . ' ' . $title_words, $lang);
    if ($newvals['page_status'] != '3') $this->CI->search->index_insert($url, $title, $short, $words_array);
}