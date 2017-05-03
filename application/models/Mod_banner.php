<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Модуль вывода баннеров
 */

class Mod_banner extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Отдаем баннеры
     *
     * @access	private
     * @param   array
     * @return	string
     */
    function get_output($params = array())
    {
        $id      = $params[0];
        $places  = $this->config->item('cms_banners_places');
        $views   = $this->config->item('cms_banners_views');
        $list    = $places[$id]['list'];
        $view    = $places[$id]['view'];
        $class   = ($places[$id]['class']) ? $places[$id]['class'] : '';
        $banners = array();

        $this->db->where('banner_place_id', $id);
        $this->db->where('banner_active', 1);
        $this->db->where('banner_lang_id', LANG);

        if($list)
        {
            $this->db->order_by("banner_sort", "asc");
            $query = $this->db->get('w_banners');
        }
        else
        {
            $this->db->order_by("banner_sort", "random");
            $this->db->limit(1);

            $this->db->cache_off();
            $query = $this->db->get('w_banners');
            $this->db->cache_on();
        }

        if ($query->num_rows() > 0)
		{
		    $i = 0;
            foreach ($query->result() as $row)
            {
                $banner = array(
                    'id'        => $row->banner_id,
                    'code'      => $row->banner_code,
                    'url'       => '/mod_banner/p_click/'.$row->banner_id,
                    'img'       => $this->_get_banner_code($row->banner_id, $row->banner_link),
                    'blank'     => ($row->banner_blank == 1) ? true : false,
                    'i'         => $i
                );

                if ($views[$row->banner_view_id]['view']) $banners[$row->banner_id] = $this->load->view('site/'.$views[$row->banner_view_id]['view'], array('banner'=>$banner), true);

                $i++;
            }

            $data = array(
                'banners'   => $banners,
                'class'     => $class
            );

            if ($view) return $this->load->view('site/'.$view, $data, true);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Код баннера и проверка файла
     *
     * @access  private
     * @param   int
     * @param   string
     * @return  string
     */
    function _get_banner_code($banner_id, $link)
    {
        $url = $this->config->item('cms_banners_dir');
        $path = FCPATH.substr($url, 1);

        if ($handle = opendir($path))
        {
            while (($file = readdir($handle)) !== false)
            {
                if (preg_match ("/^".$banner_id."\.([[:alnum:]])*$/", $file))
                {
                    $pieces     = explode(".", $file);
                    $extension  = strtolower($pieces[count($pieces)-1]);
                    $params     = getimagesize($path.$file);

                    if ($extension != 'swf')
                    {
                        return '<img src="'.$url.$file.'" ' . $params[3] . '>';
                    }
                    else
                    {
                        return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="'.$params[0].'" height="'.$params[1].'"><param name="movie" value="'.$url.$file.'?link1='.$link.'" /><param name="quality" value="high" /><embed src="'.$url.$file.'?link1='.$link.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$params[0].'" height="'.$params[1].'"></embed></object>';
                    }
                }
            }
        }
    }

    /**
     * Подсчет кликов и переход по ссылке
     *
     * @access  public
     * @return  void
     */
    function p_click ()
    {
        if($this->uri->segment(3) && preg_int ($this->uri->segment(3)))
        {
            $id = $this->uri->segment(3);

            $query = $this->db->get_where('w_banners', array('banner_id' => $id), 1);

            if ($query->num_rows() > 0)
            {
                $row = $query->row();

                $data = array( 'banner_click' => $row->banner_click + 1 );
                $this->db->where('banner_id', $id);
                $this->db->update('w_banners', $data);

                header ('Location: '.$row->banner_link);
            }
        }
    }
}