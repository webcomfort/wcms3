<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Версия Webcomfort CMS = версия CodeIgniter
|--------------------------------------------------------------------------
*/
$config['cms_version'] = '3.1.3';

/*
|--------------------------------------------------------------------------
| Профилирование
|--------------------------------------------------------------------------
*/
$config['cms_site_profiling'] = false;
$config['cms_admin_profiling'] = false;

/*
|--------------------------------------------------------------------------
| Индексирование
|--------------------------------------------------------------------------
*/
$config['cms_site_indexing'] = false;

/*
|--------------------------------------------------------------------------
| Языки сайта
|--------------------------------------------------------------------------
*/
$config['cms_lang'] = array(
    '1' => array(
        'folder'    => 'russian',
        'name'      => 'Русский',
        'search'    => 'ru_RU'
    ),
    '2' => array(
        'folder'    => 'english',
        'name'      => 'English',
        'search'    => 'en_EN'
    )
);

/*
|--------------------------------------------------------------------------
| Компоненты сайта
|--------------------------------------------------------------------------
*/

// Меню
$config['cms_site_menues'] = array(
    '1' => array(
        'map'    => true,
        'name'   => 'Основное меню сайта'
    ),
    '2' => array(
        'map'    => false,
        'name'   => 'Служебные страницы'
    )
);

// Макеты
$config['cms_site_views'] = array(
    '1' => array(
        'file'      => 'page_default',
        'name'      => 'Макет для регулярной страницы с меню второго уровня и последними новостями',
        'text'      => 1,
        'header'    => 'page_header',
        'footer'    => 'page_footer'
    ),
    '3' => array(
        'file'      => 'page_default_wide',
        'name'      => 'Макет для регулярной страницы на всю ширину',
        'text'      => 1,
        'header'    => 'page_header',
        'footer'    => 'page_footer'
    ),
    '2' => array(
        'file'      => 'page_index',
        'name'      => 'Макет для главной страницы',
        'text'      => 3,
        'header'    => 'page_header',
        'footer'    => 'page_footer'
    )
);

// Подключения
$config['cms_site_inclusions'] = array(
    '1' => array(
        'file'          => 'cms_module',
        'name'          => 'Модуль',
        'help'          => 'Выберите из списка модуль для отображения в центральной части страницы.',
        'table'         => 'w_cms_modules',
        'key'           => 'module_id',
        'filter'        => '$filters = "module_active = \'1\' AND module_type = \'1\'";',
        'description'   => 'module_name',
        'orderby'       => 'module_sort',
        'where'         => array('pages', 'news')
    ),
    '2' => array(
        'file'          => 'mod_news',
        'name'          => 'Новостная рубрика',
        'help'          => 'Выберите из списка новостную рубрику для отображения в центральной части страницы.',
        'table'         => 'w_news_categories',
        'key'           => 'news_cat_id',
        'filter'        => '$filters = "";',
        'description'   => 'news_cat_name',
        'orderby'       => 'news_cat_name',
        'where'         => array('pages'),
        'add_code'      => '<a class="btn btn-primary btn-inc" href="#" role="button" data-toggle="modal" data-target="#NewsModal">Создать</a>',
        'modal_code'    => 'admin/inc_news'
    ),
    '3' => array(
        'file'          => 'mod_gallery',
        'name'          => 'Галерея',
        'help'          => 'Выберите из списка галерею для отображения в центральной части страницы.',
        'table'         => 'w_galleries',
        'key'           => 'gallery_id',
        'filter'        => '$filters = "gallery_lang_id=\'{$this->session->userdata(\'w_alang\')}\'";',
        'description'   => 'gallery_name',
        'orderby'       => 'gallery_name',
        'where'         => array('pages', 'news'),
        'add_code'      => '<a class="btn btn-primary btn-inc" href="#" role="button" data-toggle="modal" data-target="#GalleryModal">Создать</a>',
        'modal_code'    => 'admin/inc_gallery'
    )
);

/*
|--------------------------------------------------------------------------
| Управление пользователями
|--------------------------------------------------------------------------
*/
$config['cms_user_ip']      = true; // проверять по ip
$config['cms_user_agent']   = true; // проверять по user_agent

// Группы
$config['cms_user_groups'] = array(
    '1' => array(
        'name'      => 'Администраторы',
        'admin'     => true,
        'active'    => true
    ),
    '2' => array(
        'name'      => 'Пользователи',
        'admin'     => false,
        'active'    => true
    )
);

/*
|--------------------------------------------------------------------------
| Компоненты администрирования
|--------------------------------------------------------------------------
*/

// Макеты
$config['cms_admin_views'] = array(
    '1' => array(
        'file'  => 'page_default',
        'name'  => 'Базовый макет страницы администрирования'
    )
);