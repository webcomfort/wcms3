<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Версия Webcomfort CMS = версия CodeIgniter
|--------------------------------------------------------------------------
*/
$config['cms_version'] = '3.1.11';

/*
|--------------------------------------------------------------------------
| Email администратора (для инсталляции)
| Email администратора (для инсталляции)
|--------------------------------------------------------------------------
*/
$config['cms_admin_email'] = 'info@webcomfort.ru';

/*
|--------------------------------------------------------------------------
| Капча (текущая подходит для тестового домена wcms3.loc).
| Без рабочих ключей вы не сможете авторизоваться в системе.
|--------------------------------------------------------------------------
*/
$config['cms_recaptcha_sitekey'] = '6Ld4GScUAAAAAIXhm1AE0pbI5UsgVAOnOiBfIM_0';
$config['cms_recaptcha_secret']  = '6Ld4GScUAAAAAB3j3M2MkYp4GHAgbJ8h9sCwmKHW';

/*
|--------------------------------------------------------------------------
| Профилирование
|--------------------------------------------------------------------------
*/
$config['cms_site_profiling'] = false;
$config['cms_admin_profiling'] = false;
$config['cms_code_compression'] = false;

/*
|--------------------------------------------------------------------------
| Индексирование
|--------------------------------------------------------------------------
*/
$config['cms_site_indexing'] = true;
$config['cms_site_reindexing'] = array(
	'adm_pages'     => 'Переиндексация страниц',
	'adm_news'      => 'Переиндексация новостей',
	'adm_shop_item' => 'Переиндексация каталога'
);
$config['cms_menu_indexing'] = array(1);

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

// Webp support
$config['cms_webp'] = true;

// Фоны
$config['cms_bg_dir'] = '/public/upload/backgrounds/';

// Меню
$config['cms_site_crumbs'] = 1;
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

// Макеты страниц
$config['cms_site_views'] = array(
    '1' => array(
        'file'      => 'page_default',
        'name'      => 'Макет для регулярной страницы с меню второго уровня и последними новостями',
        'header'    => 'page_header',
        'footer'    => 'page_footer'
    ),
    '3' => array(
        'file'      => 'page_default_wide',
        'name'      => 'Макет для регулярной страницы на всю ширину',
        'header'    => 'page_header',
        'footer'    => 'page_footer'
    ),
    '2' => array(
        'file'      => 'page_index',
        'name'      => 'Макет для главной страницы',
        'header'    => 'page_header',
        'footer'    => 'page_footer'
    )
);

$views_default = array(
	'1' => array(
		'file'      => 'article_default',
		'name'      => 'Статья с темным текстом на светлом фоне'
	),
	'2' => array(
		'file'      => 'article_default_white',
		'name'      => 'Статья с белым текстом на темном фоне'
	)
);

// Статьи
$config['cms_articles'] = array(
	'pages' => array(
		'trigger' => 'PME_data_page_view_id',
		'values'  => array(
			'1' => array(
				'places' => array(
					'0' => array(
						'name'  => 'Основной блок',
						'views' => $views_default
					),
					'1' => array(
						'name'  => 'Сайдбар',
						'views' => $views_default
					)
				)
			),
			'2' => array(
				'places' => array(
					'0' => array(
						'name'  => 'Основной блок',
						'views' => $views_default
					),
					'1' => array(
						'name'  => 'Сайдбар',
						'views' => $views_default
					)
				)
			),
			'3' => array(
				'places' => array(
					'0' => array(
						'name'  => 'Основной блок',
						'views' => array(
							'1' => array(
								'file'      => 'article_wide',
								'name'      => 'Статья с темным текстом на светлом фоне, текст на всю ширину'
							),
							'2' => array(
								'file'      => 'article_wide_white',
								'name'      => 'Статья с белым текстом на темном фоне, белый текст на всю ширину'
							),
							'3' => array(
								'file'      => 'article_container',
								'name'      => 'Статья с темным текстом на светлом фоне, текст в контейнере'
							),
							'4' => array(
								'file'      => 'article_container_white',
								'name'      => 'Статья с белым текстом на темном фоне, белый текст в контейнере'
							)
						)
					)
				)
			)
		)
	),
	'news' => array(
		'trigger' => false,
		'values'  => array(
			'1' => array(
				'places' => array(
					'0' => array(
						'name'  => 'Основной блок',
						'views' => $views_default
					)
				)
			)
		)
	),
	'shop' => array(
		'trigger' => false,
		'values'  => array(
			'1' => array(
				'places' => array(
					'0' => array(
						'name'  => 'Основной блок',
						'views' => $views_default
					)
				)
			)
		)
	)
);

$config['cms_inserts'] = array(
	'1' => array(
		'name'          => 'Галерея',
		'adm_model'     => 'Adm_gallery_photos',
		'adm_function'  => 'get_insert_ui'
	),
);

// Подключения
$config['cms_site_inclusions'] = array(
    '1' => array(
        'label'         => '',
    	'file'          => 'cms_module',
        'name'          => 'Модуль',
        'help'          => 'Выберите из списка модуль для подключения.',
        'table'         => 'w_cms_modules',
        'key'           => 'module_id',
        'filter'        => '$filters = "module_active = \'1\' AND module_type = \'1\'";',
        'description'   => 'module_name',
        'orderby'       => 'module_sort',
        'where'         => array('pages', 'news'),
	    'tags'          => false,
    ),
    '2' => array(
	    'label'         => 'news',
    	'file'          => 'mod_news',
        'name'          => 'Новостная рубрика',
        'help'          => 'Выберите из списка новостную рубрику для подключения.',
        'table'         => 'w_news_categories',
        'key'           => 'news_cat_id',
        'filter'        => '$filters = "";',
        'description'   => 'news_cat_name',
        'orderby'       => 'news_cat_name',
        'where'         => array('pages'),
        'add_code'      => '<a class="btn btn-primary btn-inc" href="#" role="button" data-toggle="modal" data-target="#NewsModal" id="add_news_button">Создать</a>',
        'modal_code'    => 'admin/inc_news',
        'adm_model'     => 'Adm_news',
	    'tags'          => array(
	    	'model' => 'Cms_news',
		    'method'=> 'get_tag_items'
	    )
    ),
    '3' => array(
	    'label'         => 'gallery',
    	'file'          => 'mod_gallery',
        'name'          => 'Галерея',
        'help'          => 'Выберите из списка галерею для подключения.',
        'table'         => 'w_galleries',
        'key'           => 'gallery_id',
        'filter'        => '$filters = "gallery_lang_id=\'{$this->session->userdata(\'w_alang\')}\'";',
        'description'   => 'gallery_name',
        'orderby'       => 'gallery_name',
        'where'         => array('pages', 'news', 'shop'),
        'add_code'      => '<a class="btn btn-primary btn-inc" href="#" role="button" data-toggle="modal" data-target="#GalleryModal3" id="add_gallery_button_3">Создать</a>',
        'modal_code'    => 'admin/inc_gallery',
        'adm_model'     => 'Adm_gallery_photos',
	    'tags'          => false,
    ),
    '4' => array(
	    'label'         => 'shop',
    	'file'          => 'mod_catalog',
	    'name'          => 'Категория каталога',
	    'help'          => 'Выберите из списка категорию для подключения.',
	    'table'         => 'w_shop_categories',
	    'key'           => 'cat_id',
	    'filter'        => '$filters = "cat_lang_id=\'{$this->session->userdata(\'w_alang\')}\'";',
	    'description'   => 'cat_name',
	    'orderby'       => 'cat_pid, cat_sort',
	    'where'         => array('pages'),
	    'tags'          => array(
		    'model' => 'Cms_shop',
		    'method'=> 'get_tag_items'
	    )
    ),
	'5' => array(
		'label'         => '',
		'file'          => 'cms_sidebar',
		'name'          => 'Сайдбар',
		'help'          => 'Выберите из списка сайдбар для подключения.',
		'table'         => 'w_sidebar',
		'key'           => 'sidebar_id',
		'filter'        => '$filters = "sidebar_lang_id=\'{$this->session->userdata(\'w_alang\')}\'";',
		'description'   => 'sidebar_name',
		'orderby'       => 'sidebar_name',
		'where'         => array('pages'),
		'tags'          => false,
	),
);

/*
|--------------------------------------------------------------------------
| Баннеры
|--------------------------------------------------------------------------
*/
$config['cms_banners_dir'] = '/public/upload/special/';
$config['cms_banners_views'] = array(
    '1' => array(
        'name'      => 'По умолчанию',
        'view'      => 'banner_default' // макет баннера
    )
);
$config['cms_banners_places'] = array(
    '1' => array(
        'name'      => 'Первый баннер под большим слайдером на главной',
        'list'      => false,
        'class'     => 'img-fluid',
        'view'      => 'banner_xxx' // макет списка
    )
);

/*
|--------------------------------------------------------------------------
| Галереи и фотографии
|--------------------------------------------------------------------------
*/
$config['cms_gallery_dir'] = '/public/upload/gallery/';
$config['cms_gallery_sizes'] = array(
	'_thumb' => array(
		'width'     => 150,
		'height'    => 0,
		'class'     => '',
		'rel'       => ''
	),
	'_big' => array(
		'width'     => 750,
		'height'    => 400,
		'class'     => 'd-block w-100',
		'rel'       => ''
	)
);
$config['cms_gallery_views'] = array(
    '1' => array(
        'file'  => 'gallery_index',
        'name'  => 'Слайдер'
    )
);

/*
|--------------------------------------------------------------------------
| Новости
|--------------------------------------------------------------------------
*/
$config['cms_news_limit'] = 3;
$config['cms_news_dir'] = '/public/upload/news/';
$config['cms_news_images'] = array(
    '_thumb' => array(
        'width'     => 300,
        'height'    => 0
    ),
    '_big' => array(
        'width'     => 1140,
        'height'    => 250
    )
);
$config['cms_news_views'] = array(
    '1' => array(
        'file'  => 'news_list_default',
        'name'  => 'Базовый макет ленты новостей'
    )
);

/*
|--------------------------------------------------------------------------
| Каталог
|--------------------------------------------------------------------------
*/
$config['cms_shop_dir'] = '/public/upload/shop/';
$config['cms_shop_cat_dir'] = '/public/upload/shopcat/';
$config['cms_shop_images'] = array(
    '_thumb' => array(
        'width'     => 200,
        'height'    => 0
    ),
    '_big' => array(
        'width'     => 800,
        'height'    => 0
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
        'admin'     => true, // доступ в админ
        'active'    => true, // активна ли группа
	    'files'     => true, // доступ к файлам всех пользователей
        'items'     => true  // доступ к записям всех пользователей
    ),
    '2' => array(
	    'name'      => 'Редакторы',
	    'admin'     => true,
	    'active'    => true,
	    'files'     => false,
	    'items'     => false
    ),
    '3' => array(
        'name'      => 'Пользователи',
        'admin'     => false,
        'active'    => true,
        'files'     => false,
        'items'     => false
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

/*
|--------------------------------------------------------------------------
| Сайдбар
|--------------------------------------------------------------------------
*/

$banner_vals = array();
foreach ($config['cms_banners_places'] as $key => $value) $banner_vals[$key] = $value['name'];

$config['cms_widgets'] = array(
	'Mod_sidetext.php' => array(
		'widget_param_1' => array(
			'name'          => 'Текст',
			'select'        => 'T',
			'addcss'        => 'htmleditor',
			'options'       => 'ACPDV',
			'maxlen'        => 65535,
			'textarea'      => array(
				'rows'      => 5,
				'cols'      => 66
			),
			'required'      => false,
			'escape'        => false,
			'help'          => 'Введите в это текст для вывода в сайдбар.'
		)
	),
	'Mod_news_latest.php' => array(
		'widget_param_1' => array(
			'name'     => 'Лента новостей',
			'select'   => 'D',
			'options'  => 'ACPDV',
			'values'   => array (
				'table' => 'w_news_categories',
				'column' => 'news_cat_id',
				'description' => 'news_cat_name',
				'orderby' => 'news_cat_name'),
			'required' => true,
			'sort'     => true,
			'help'     => 'Выберите из списка ленту.'
		),
		'widget_param_2' => array(
			'name'          => 'Количество',
			'options'       => 'ACPDV',
			'select'        => 'T',
			'maxlen'        => 65535,
			'required'      => true,
			'help'          => 'Введите целое число.'
		),
	),
	'Mod_banner.php' => array(
		'widget_param_1' => array(
			'name'          => 'Баннер',
			'select'        => 'D',
			'options'       => 'ACPDV',
			'values2'       => $banner_vals,
			'required'      => true,
			'help'          => 'Выберите категорию баннеров для вывода'
		)
	),
);
