<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Обработка картинок, включений и индексация
 *
 * @action	update
 * @mode	after
 */

$id = $this->rec;
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

// Удаляем старые записи
$this->CI->db->delete('w_news_categories_cross', array('news_id' => $id));

// Вносим новые записи
if(is_array($this->CI->input->post('news_rubrics_'.$id, TRUE))) {
    foreach ($this->CI->input->post('news_rubrics_'.$id, TRUE) as $value) {
        $data = array(
            'ncc_id'		=> '',
            'news_id' 		=> $id,
            'news_cat_id'	=> trim($value)
        );

        $this->CI->db->insert('w_news_categories_cross', $data);
    }
}

// ------------------------------------------------------------------------

// Индексирование текстов
if($this->CI->config->item('cms_site_indexing'))
{
	$inclusions = $this->CI->config->item('cms_site_inclusions');

    foreach ($inclusions as $key => $value)
    {
        if($value['file'] == 'mod_news') $inclusion_key = $key;
    }

    if(isset($inclusion_key))
    {
	    $this->CI->db->select('w_pages.page_url AS url');
		$this->CI->db->from('w_includes');
		$this->CI->db->join('w_pages', 'w_includes.obj_id = w_pages.page_id');
		$this->CI->db->where('inc_id', $inclusion_key);
		$this->CI->db->where('inc_value', $newvals['news_main_cat']);
		$this->CI->db->where('inc_type', 'pages');
		$this->CI->db->limit(1);

		$query = $this->CI->db->get();

		if ($query->num_rows() > 0)
		{
			$row = $query->row();

			$this->CI->load->library('search');
			$this->CI->load->helper('text');

			// ------------------------------------------------------------------------

			$this->CI->db->cache_delete($row->url, $oldvals['news_url']);

			// ------------------------------------------------------------------------

			$url 			= '/'.$row->url.'/'.$newvals['news_url'];
			$title 			= $newvals['news_name'];
			$article_words 	= text2words(html_entity_decode($newvals['news_content']));
			$title_words 	= text2words($title);
			$short 			= word_limiter($article_words, 50);
			$lang_array 	= $this->CI->config->item('cms_lang');
			$lang			= $lang_array[$this->CI->session->userdata('w_alang')]['search'];

			$words_array = $this->CI->search->index_prepare($article_words . ' ' . $title_words, $lang);
			$this->CI->search->index_insert($url, $title, $short, $words_array);
		}
	}
}

// ------------------------------------------------------------------------

// Подключения
$this->CI->Cms_inclusions->admin_inclusions_insert_update($id, 'news');