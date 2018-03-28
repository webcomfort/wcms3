<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Версия Webcomfort CMS = версия CodeIgniter
|--------------------------------------------------------------------------
*/
$config['cms_version'] = '3.1.8';

/*
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| Индексирование
|--------------------------------------------------------------------------
*/
$config['cms_site_indexing'] = true;

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

// Фоны
$config['cms_bg_dir'] = '/public/upload/backgrounds/';

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

// Статьи
$config['cms_articles'] = array(
	'pages' => array(
		'trigger' => 'PME_data_page_view_id',
		'values'  => array(
			'1' => array(
				'places' => array(
					'0' => array(
						'name'  => 'Основной блок',
						'views' => array(
							'1' => array(
								'file'      => 'article_default',
								'name'      => 'Статья без отступов и фона'
							),
							'2' => array(
								'file'      => 'article_default_white',
								'name'      => 'Статья с белым текстом на темном фоне'
							)
						)
					),
					'1' => array(
						'name'  => 'Сайдбар',
						'views' => array(
							'1' => array(
								'file'      => 'article_default',
								'name'      => 'Статья без отступов и фона'
							),
							'2' => array(
								'file'      => 'article_default_white',
								'name'      => 'Статья с белым текстом на темном фоне'
							)
						)
					)
				)
			),
			'2' => array(
				'places' => array(
					'0' => array(
						'name'  => 'Основной блок',
						'views' => array(
							'1' => array(
								'file'      => 'article_default',
								'name'      => 'Статья без отступов и фона'
							),
							'2' => array(
								'file'      => 'article_default_white',
								'name'      => 'Статья с белым текстом на темном фоне'
							)
						)
					),
					'1' => array(
						'name'  => 'Сайдбар',
						'views' => array(
							'1' => array(
								'file'      => 'article_default',
								'name'      => 'Статья без отступов и фона'
							),
							'2' => array(
								'file'      => 'article_default_white',
								'name'      => 'Статья с белым текстом на темном фоне'
							)
						)
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
								'name'      => 'Статья c фоном, текст на всю ширину'
							),
							'2' => array(
								'file'      => 'article_wide_white',
								'name'      => 'Статья c фоном, белый текст на всю ширину'
							),
							'3' => array(
								'file'      => 'article_container',
								'name'      => 'Статья c фоном, текст в контейнере'
							),
							'4' => array(
								'file'      => 'article_container_white',
								'name'      => 'Статья c фоном, белый текст в контейнере'
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
						'views' => array(
							'1' => array(
								'file'      => 'article_default',
								'name'      => 'Статья без отступов и фона'
							),
							'2' => array(
								'file'      => 'article_default_white',
								'name'      => 'Статья с белым текстом на темном фоне'
							)
						)
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
						'views' => array(
							'1' => array(
								'file'      => 'article_default',
								'name'      => 'Статья без отступов и фона'
							),
							'2' => array(
								'file'      => 'article_default_white',
								'name'      => 'Статья с белым текстом на темном фоне'
							)
						)
					)
				)
			)
		)
	)
);

// Подключения
$config['cms_site_inclusions'] = array(
    '1' => array(
        'file'          => 'cms_module',
        'name'          => 'Модуль',
        'help'          => 'Выберите из списка модуль для подключения.',
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
        'help'          => 'Выберите из списка новостную рубрику для подключения.',
        'table'         => 'w_news_categories',
        'key'           => 'news_cat_id',
        'filter'        => '$filters = "";',
        'description'   => 'news_cat_name',
        'orderby'       => 'news_cat_name',
        'where'         => array('pages'),
        'add_code'      => '<a class="btn btn-primary btn-inc" href="#" role="button" data-toggle="modal" data-target="#NewsModal" id="add_news_button">Создать</a>',
        'modal_code'    => 'admin/inc_news',
        'adm_model'     => 'Adm_news'
    ),
    '3' => array(
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
        'adm_model'     => 'Adm_gallery_photos'
    )
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
        'class'     => false,
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
$config['cms_news_limit'] = 1;
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