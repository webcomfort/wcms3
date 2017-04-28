SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `w_backgrounds`;
CREATE TABLE `w_backgrounds` (
  `bg_id` int(11) NOT NULL,
  `bg_name` varchar(255) NOT NULL,
  `bg_active` tinyint(1) NOT NULL DEFAULT '1',
  `bg_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

DROP TABLE IF EXISTS `w_banners`;
CREATE TABLE `w_banners` (
  `banner_id` int(11) NOT NULL,
  `banner_place_id` int(11) NOT NULL,
  `banner_name` varchar(255) NOT NULL,
  `banner_active` tinyint(1) NOT NULL DEFAULT '1',
  `banner_blank` tinyint(1) NOT NULL DEFAULT '0',
  `banner_code` text NOT NULL,
  `banner_link` varchar(255) NOT NULL,
  `banner_view_id` int(11) NOT NULL,
  `banner_sort` int(11) NOT NULL,
  `banner_click` int(11) NOT NULL,
  `banner_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

INSERT INTO `w_banners` (`banner_id`, `banner_place_id`, `banner_name`, `banner_active`, `banner_blank`, `banner_code`, `banner_link`, `banner_view_id`, `banner_sort`, `banner_click`, `banner_lang_id`) VALUES
(1, 1, 'Баннер', 1, 1, '', 'http://yandex.ru', 1, 1369929973, 2, 1);

DROP TABLE IF EXISTS `w_changelog`;
CREATE TABLE `w_changelog` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `updated` datetime NOT NULL,
  `user` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `operation` varchar(255) NOT NULL,
  `tab` varchar(255) NOT NULL,
  `rowkey` varchar(255) NOT NULL,
  `col` varchar(255) NOT NULL,
  `files` blob NOT NULL,
  `oldval` blob NOT NULL,
  `newval` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `w_cms_configs`;
CREATE TABLE `w_cms_configs` (
  `config_id` int(11) NOT NULL,
  `config_name` varchar(255) NOT NULL,
  `config_label` varchar(255) NOT NULL,
  `config_value` varchar(255) NOT NULL,
  `config_module_label` varchar(255) NOT NULL,
  `config_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_cms_configs` (`config_id`, `config_name`, `config_label`, `config_value`, `config_module_label`, `config_lang_id`) VALUES
(1, 'Форма для контактов - адрес электронной почты для отправки письма', 'contacts_email', 'info@webcomfort.ru', 'mod_contacts.php', 1),
(2, 'Код для активации каптчи', 'recaptcha', '6LfSMgoUAAAAACLlSi-k77La5nTA__0uWzXE9Rri', '', 1);

DROP TABLE IF EXISTS `w_cms_modules`;
CREATE TABLE `w_cms_modules` (
  `module_id` int(11) NOT NULL,
  `module_name` varchar(255) NOT NULL,
  `module_file` varchar(255) NOT NULL,
  `module_type` tinyint(1) NOT NULL,
  `module_active` tinyint(1) NOT NULL,
  `module_sort` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_cms_modules` (`module_id`, `module_name`, `module_file`, `module_type`, `module_active`, `module_sort`) VALUES
(1, 'Меню администрирования', 'Adm_admin_pages.php', 2, 1, 1369826732),
(2, 'Управление пользователями', 'Adm_users.php', 2, 1, 1369826733),
(3, 'Корзина', 'Adm_changelog.php', 2, 1, 1369826734),
(4, 'Страницы', 'Adm_pages.php', 2, 1, 1369826735),
(5, 'Баннеры', 'Adm_banners.php', 2, 1, 1369826736),
(6, 'Сквозные блоки', 'Adm_cross_blocks.php', 2, 1, 1369826737),
(7, 'Настройки сайта', 'Adm_site_configs.php', 2, 1, 1369826738),
(8, 'Новости', 'Adm_news.php', 2, 1, 1369826739),
(9, 'Новостные рубрики', 'Adm_news_categories.php', 2, 1, 1369826740),
(10, 'Фотографии', 'Adm_gallery_photos.php', 2, 1, 1369826741),
(11, 'Галереи', 'Adm_gallery.php', 2, 1, 1369826742),
(12, 'Контактная форма', 'Mod_contacts.php', 1, 1, 1369826750),
(13, 'Результаты поиска по сайту (служебный)', 'Mod_search.php', 1, 1, 1378837186),
(14, 'Модули', 'Adm_modules.php', 2, 1, 1369826752),
(15, 'Карта сайта (служебный)', 'Mod_site_map.php', 1, 1, 1369826751),
(16, 'Вывод поста/новости (служебный)', 'Mod_news.php', 1, 1, 1491916603);

DROP TABLE IF EXISTS `w_cms_pages`;
CREATE TABLE `w_cms_pages` (
  `cms_page_id` int(11) NOT NULL,
  `cms_page_pid` int(11) NOT NULL DEFAULT '0',
  `cms_page_name` varchar(255) NOT NULL,
  `cms_page_model_id` int(11) NOT NULL,
  `cms_page_view_id` int(11) NOT NULL,
  `cms_page_sort` int(11) NOT NULL,
  `cms_page_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_cms_pages` (`cms_page_id`, `cms_page_pid`, `cms_page_name`, `cms_page_model_id`, `cms_page_view_id`, `cms_page_sort`, `cms_page_status`) VALUES
(1, 0, 'Администратор', 0, 0, 1369665049, 3),
(2, 1, 'Меню администрирования', 1, 1, 1371473563, 1),
(3, 0, 'Пользователи', 2, 1, 1368050509, 1),
(4, 0, 'Корзина', 3, 1, 1368736763, 1),
(5, 0, 'Сайт', 4, 1, 1364596270, 3),
(6, 5, 'Страницы', 4, 1, 1368918785, 1),
(7, 5, 'Баннеры', 5, 1, 1369044778, 1),
(8, 5, 'Сквозные блоки', 6, 1, 1369144591, 1),
(9, 0, 'Настройки сайта', 7, 1, 1368918747, 1),
(10, 0, 'Новости и фото', 8, 1, 1367927286, 3),
(11, 10, 'Новости', 8, 1, 1369665078, 1),
(12, 11, 'Рубрики', 9, 1, 1369729521, 1),
(13, 10, 'Фото', 10, 1, 1369811296, 1),
(14, 13, 'Галереи', 11, 1, 1369811312, 1),
(15, 1, 'Модули', 14, 1, 1369146092, 1);

DROP TABLE IF EXISTS `w_galleries`;
CREATE TABLE `w_galleries` (
  `gallery_id` int(11) NOT NULL,
  `gallery_name` varchar(255) NOT NULL,
  `gallery_view_id` int(11) NOT NULL,
  `gallery_active` tinyint(1) NOT NULL DEFAULT '1',
  `gallery_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

INSERT INTO `w_galleries` (`gallery_id`, `gallery_name`, `gallery_view_id`, `gallery_active`, `gallery_lang_id`) VALUES
(1, 'Для главной', 1, 1, 1),
(2, '111', 1, 1, 2);

DROP TABLE IF EXISTS `w_gallery_photos`;
CREATE TABLE `w_gallery_photos` (
  `photo_id` int(11) NOT NULL,
  `photo_gallery_id` int(11) NOT NULL,
  `photo_name` varchar(255) NOT NULL,
  `photo_sort` int(11) NOT NULL,
  `photo_active` tinyint(1) NOT NULL DEFAULT '1',
  `photo_link` varchar(255) NOT NULL,
  `photo_text` text NOT NULL,
  `photo_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_gallery_photos` (`photo_id`, `photo_gallery_id`, `photo_name`, `photo_sort`, `photo_active`, `photo_link`, `photo_text`, `photo_lang_id`) VALUES
(1, 1, 'Бом-бом', 1369898832, 1, 'http://yandex.ru', '<h4>Почему откровенна бизнес-модельs</h4>\r\n\r\n<p>Анализ зарубежного опыта не так уж очевиден. Формирование имиджа порождает побочный PR-эффект, используя опыт предыдущих кампаний. Размещение, конечно, развивает потребительский рынок, учитывая современные тенденции. Ценовая стратегия неверно усиливает социометрический медиаплан, повышая конкуренцию. Продукт наиболее полно восстанавливает фактор коммуникации, оптимизируя бюджеты.</p>\r\n', 1),
(2, 1, 'Туц-туц', 1369898836, 1, '', '', 1),
(3, 1, 'Тыщ-тыщ', 1369898839, 1, '', '', 1),
(4, 2, '111', 1490022698, 1, '', '', 2),
(5, 2, '111', 1490022700, 1, '', '', 2),
(6, 2, '111', 1490022701, 1, '', '', 2),
(7, 1, 'Для главной', 1493290900, 1, '', '', 1),
(8, 1, 'Для главной', 1493290902, 1, '', '', 1),
(9, 1, 'Фигня', 1493290905, 1, '', '', 1);

DROP TABLE IF EXISTS `w_includes`;
CREATE TABLE `w_includes` (
  `i_id` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `inc_id` int(11) NOT NULL,
  `inc_value` int(11) NOT NULL,
  `inc_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_includes` (`i_id`, `obj_id`, `inc_id`, `inc_value`, `inc_type`) VALUES
(1, 3, 1, 12, 'news'),
(2, 3, 3, 0, 'news'),
(7, 1, 1, 0, 'pages'),
(8, 1, 2, 0, 'pages'),
(9, 1, 3, 1, 'pages'),
(13, 3, 1, 0, 'pages'),
(14, 3, 2, 0, 'pages'),
(15, 3, 3, 0, 'pages'),
(16, 4, 1, 0, 'pages'),
(17, 4, 2, 1, 'pages'),
(18, 4, 3, 0, 'pages'),
(19, 5, 1, 12, 'pages'),
(20, 5, 2, 0, 'pages'),
(21, 5, 3, 0, 'pages'),
(22, 6, 1, 13, 'pages'),
(23, 6, 2, 0, 'pages'),
(24, 6, 3, 0, 'pages'),
(25, 7, 1, 0, 'pages'),
(26, 7, 2, 0, 'pages'),
(27, 7, 3, 0, 'pages'),
(31, 9, 1, 15, 'pages'),
(32, 9, 2, 0, 'pages'),
(33, 9, 3, 0, 'pages'),
(34, 10, 1, 0, 'pages'),
(35, 10, 2, 0, 'pages'),
(36, 10, 3, 1, 'pages'),
(51, 8, 1, 0, 'pages'),
(52, 8, 2, 0, 'pages'),
(53, 8, 3, 0, 'pages'),
(54, 12, 1, 0, 'pages'),
(55, 12, 2, 0, 'pages'),
(56, 12, 3, 0, 'pages'),
(57, 4, 1, 12, 'news'),
(58, 4, 3, 0, 'news'),
(59, 11, 1, 0, 'pages'),
(60, 11, 2, 0, 'pages'),
(61, 11, 3, 0, 'pages'),
(62, 13, 1, 0, 'pages'),
(63, 13, 2, 0, 'pages'),
(64, 13, 3, 0, 'pages'),
(65, 14, 1, 16, 'pages'),
(66, 14, 2, 0, 'pages'),
(67, 14, 3, 0, 'pages');

DROP TABLE IF EXISTS `w_indexing_index`;
CREATE TABLE `w_indexing_index` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `link` int(11) NOT NULL DEFAULT '0',
  `word` int(11) NOT NULL DEFAULT '0',
  `times` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `w_indexing_link`;
CREATE TABLE `w_indexing_link` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `short` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `w_indexing_word`;
CREATE TABLE `w_indexing_word` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `word` varchar(30) NOT NULL DEFAULT '',
  `sound` char(4) NOT NULL DEFAULT 'A000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `w_news`;
CREATE TABLE `w_news` (
  `news_id` int(11) NOT NULL,
  `news_name` varchar(255) NOT NULL,
  `news_date` datetime NOT NULL,
  `news_cut` text NOT NULL,
  `news_content` text NOT NULL,
  `news_url` varchar(255) NOT NULL,
  `news_active` tinyint(1) NOT NULL DEFAULT '1',
  `news_meta_title` text NOT NULL,
  `news_meta_keywords` text NOT NULL,
  `news_meta_description` text NOT NULL,
  `news_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_news` (`news_id`, `news_name`, `news_date`, `news_cut`, `news_content`, `news_url`, `news_active`, `news_meta_title`, `news_meta_keywords`, `news_meta_description`, `news_lang_id`) VALUES
(3, 'Нишевый проект как рекламная кампанияя', '2017-03-10 14:41:00', '<p>Как-то предсказывают футурологи повышение жизненных стандартов спонтанно раскручивает портрет потребителя, осознав маркетинг как часть производства. Социальный статус версифицирован. Маркетинг концентрирует поведенческий таргетинг, расширяя долю рынка. Медийный канал, не меняя концепции, изложенной выше, экономит сублимированный рекламный бриф, осознав маркетинг как часть производства. Метод изучения рынка транслирует ребрендинг, размещаясь во всех медиа. Российская специфика пока плохо создает социометрический фактор коммуникации, отвоевывая рыночный сегмент.</p>\r\n\r\n<p><img alt="" height="675" src="/public/userfiles/bigstock_Terracotta_Roof_Tiles_Pattern_4515704.jpg" width="900" /></p>\r\n', '<p>Как предсказывают футурологи повышение жизненных стандартов спонтанно раскручивает портрет потребителя, осознав маркетинг как часть производства. Социальный статус версифицирован. Маркетинг концентрирует поведенческий таргетинг, расширяя долю рынка. Медийный канал, не меняя концепции, изложенной выше, экономит сублимированный рекламный бриф, осознав маркетинг как часть производства. Метод изучения рынка транслирует ребрендинг, размещаясь во всех медиа. Российская специфика пока плохо создает социометрический фактор коммуникации, отвоевывая рыночный сегмент.</p>\r\n\r\n<p>Такое понимание ситуации восходит к Эл Райс, при этом продуктовый ассортимент развивает фирменный стиль, не считаясь с затратами. Повышение жизненных стандартов поразительно. Интересно отметить, что молодежная аудитория откровенно цинична. А вот по мнению аналитиков анализ зарубежного опыта правомочен.</p>\r\n\r\n<p>Наряду с этим, фактор коммуникации основан на тщательном анализе. Рекламная поддержка однообразно тормозит конструктивный процесс стратегического планирования, повышая конкуренцию. Такое понимание ситуации восходит к Эл Райс, при этом точечное воздействие существенно консолидирует коллективный product placement, повышая конкуренцию. Продвижение проекта ускоряет комплексный отраслевой стандарт, осознавая социальную ответственность бизнеса. Потребление трансформирует конструктивный процесс стратегического планирования, невзирая на действия конкурентов.</p>\r\n', 'nishevij_proekt_kak_reklamnaya_kampaniya_2', 1, 'Нишевый проект как рекламная кампания', '', '', 1),
(4, 'Нишевый проект как рекламная кампанияя', '2017-03-10 14:42:00', '<p>Как-то предсказывают футурологи повышение жизненных стандартов спонтанно раскручивает портрет потребителя, осознав маркетинг как часть производства. Социальный статус версифицирован. Маркетинг концентрирует поведенческий таргетинг, расширяя долю рынка. Медийный канал, не меняя концепции, изложенной выше, экономит сублимированный рекламный бриф, осознав маркетинг как часть производства. Метод изучения рынка транслирует ребрендинг, размещаясь во всех медиа. Российская специфика пока плохо создает социометрический фактор коммуникации, отвоевывая рыночный сегмент.</p>\r\n\r\n<p><img alt="" height="675" src="/public/userfiles/bigstock_Terracotta_Roof_Tiles_Pattern_4515704.jpg" width="900" /></p>\r\n', '<p>Как предсказывают футурологи повышение жизненных стандартов спонтанно раскручивает портрет потребителя, осознав маркетинг как часть производства. Социальный статус версифицирован. Маркетинг концентрирует поведенческий таргетинг, расширяя долю рынка. Медийный канал, не меняя концепции, изложенной выше, экономит сублимированный рекламный бриф, осознав маркетинг как часть производства. Метод изучения рынка транслирует ребрендинг, размещаясь во всех медиа. Российская специфика пока плохо создает социометрический фактор коммуникации, отвоевывая рыночный сегмент.</p>\r\n\r\n<p>Такое понимание ситуации восходит к Эл Райс, при этом продуктовый ассортимент развивает фирменный стиль, не считаясь с затратами. Повышение жизненных стандартов поразительно. Интересно отметить, что молодежная аудитория откровенно цинична. А вот по мнению аналитиков анализ зарубежного опыта правомочен.</p>\r\n\r\n<p>Наряду с этим, фактор коммуникации основан на тщательном анализе. Рекламная поддержка однообразно тормозит конструктивный процесс стратегического планирования, повышая конкуренцию. Такое понимание ситуации восходит к Эл Райс, при этом точечное воздействие существенно консолидирует коллективный product placement, повышая конкуренцию. Продвижение проекта ускоряет комплексный отраслевой стандарт, осознавая социальную ответственность бизнеса. Потребление трансформирует конструктивный процесс стратегического планирования, невзирая на действия конкурентов.</p>\r\n', 'nishevij_proekt_kak_reklamnaya_kampaniya', 1, 'Нишевый проект как рекламная кампания', '', '', 1);

DROP TABLE IF EXISTS `w_news_categories`;
CREATE TABLE `w_news_categories` (
  `news_cat_id` int(11) NOT NULL,
  `news_cat_name` varchar(255) NOT NULL,
  `news_cat_view_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_news_categories` (`news_cat_id`, `news_cat_name`, `news_cat_view_id`) VALUES
(1, 'Новости с полей', 1),
(2, 'Блог', 1);

DROP TABLE IF EXISTS `w_news_categories_cross`;
CREATE TABLE `w_news_categories_cross` (
  `ncc_id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `news_cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_news_categories_cross` (`ncc_id`, `news_id`, `news_cat_id`) VALUES
(184, 4, 1),
(185, 3, 1),
(186, 3, 2);

DROP TABLE IF EXISTS `w_pages`;
CREATE TABLE `w_pages` (
  `page_id` int(11) NOT NULL,
  `page_pid` int(11) NOT NULL DEFAULT '0',
  `page_menu_id` int(11) NOT NULL,
  `page_name` text NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `page_meta_title` text NOT NULL,
  `page_link_title` text NOT NULL,
  `page_meta_keywords` text NOT NULL,
  `page_meta_description` text NOT NULL,
  `page_meta_additional` text NOT NULL,
  `page_footer_additional` text NOT NULL,
  `page_url_segments` int(11) NOT NULL DEFAULT '0',
  `page_view_id` int(11) NOT NULL,
  `page_status` int(11) NOT NULL DEFAULT '2',
  `page_sort` int(11) NOT NULL,
  `page_redirect` varchar(255) NOT NULL,
  `page_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

INSERT INTO `w_pages` (`page_id`, `page_pid`, `page_menu_id`, `page_name`, `page_url`, `page_meta_title`, `page_link_title`, `page_meta_keywords`, `page_meta_description`, `page_meta_additional`, `page_footer_additional`, `page_url_segments`, `page_view_id`, `page_status`, `page_sort`, `page_redirect`, `page_lang_id`) VALUES
(1, 0, 1, 'Главная', 'index', 'Главная страница', 'Заголовок ссылки', '', '', '', '', 0, 2, 1, 1369826732, '', 1),
(2, 0, 1, 'Еще страница', 'eshe_stranitca', 'Еще страница', 'Заголовок ссылки меню', '', '', '', '', 0, 1, 3, 1369837532, '', 1),
(3, 0, 2, '404', '404', '404', '', '', '', '', '', 0, 3, 1, 1369999590, '', 1),
(4, 0, 1, 'Новости', 'novosti', 'Новости', '', '', 'Все новости и акции компании', '', '', 0, 3, 1, 1370004591, '', 1),
(5, 0, 1, 'Контакты', 'kontakti', 'Контакты', '', '', '', '', '', 0, 1, 1, 1370231468, '', 1),
(6, 0, 2, 'Результаты поиска', 'search', 'Результаты поиска', '', '', '', '', '', 0, 3, 1, 1370236551, '', 1),
(7, 2, 1, 'Подстраница', 'podstranitca', 'Подстраница', '', '', '', '', '', 0, 1, 1, 1370238176, '', 1),
(8, 7, 1, 'Подподстраница', 'podpodstranitca', 'Подподстраница', 'Подподстраница', '', '', '', '', 0, 1, 1, 1370238804, '', 1),
(9, 0, 2, 'Карта сайта', 'sitemap', 'Карта сайта', '', '', '', '', '', 0, 1, 1, 1378837091, '', 1),
(10, 2, 1, 'Подстраница 2', 'podstranitca2', 'Подстраница', 'Подстраница 2', '', '', '', '', 0, 1, 1, 1383245192, '', 1),
(11, 10, 1, 'Подподстраница 2', 'podpodstranitca2', 'Подподстраница', 'Подподстраница 2', '', '', '', '', 0, 1, 1, 1383245208, '', 1),
(12, 0, 1, 'ываыва', 'ivaiva', 'ываыва', 'ываыва', '', '', '', '', 0, 1, 2, 1445833265, '', 1),
(13, 11, 1, 'Подподстраница 3', 'podpodstranitca_3', 'Подподстраница 3', 'Подподстраница 3', '', '', '', '', 0, 1, 2, 1490282929, '', 1),
(14, 0, 2, 'Пост', 'post', 'Пост', 'Пост', '', '', '', '', 0, 3, 2, 1491916568, '', 1);

DROP TABLE IF EXISTS `w_pages_articles`;
CREATE TABLE `w_pages_articles` (
  `article_id` int(11) NOT NULL,
  `article_pid` int(11) NOT NULL,
  `article_pid_type` varchar(255) NOT NULL,
  `article_order` int(11) NOT NULL,
  `article_bg_id` int(11) NOT NULL,
  `article_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_pages_articles` (`article_id`, `article_pid`, `article_pid_type`, `article_order`, `article_bg_id`, `article_text`) VALUES
(1, 1, 'pages', 1, 0, '<h3>Социометрические инвестиционные продуктыs</h3>\r\n\r\n<p>Такое понимание ситуации восходит к Эл Райс, при этом социальный статус концентрирует баинг и селлинг, полагаясь на инсайдерскую информацию. Интересно отметить, что пул лояльных изданий довольно неоднозначен. Размещение, следовательно, редко соответствует рыночным ожиданиям. Рекламная заставка специфицирует инвестиционный продукт, повышая конкуренцию. Повторный контакт детерминирует эмпирический медиаплан, осознав маркетинг как часть производства. Надо сказать, что побочный PR-эффект нейтрализует эксклюзивный социальный статус, полагаясь на инсайдерскую информацию.</p>\r\n\r\n<p><img alt="" height="375" src="/public/userfiles/bigstock_Terracotta_Roof_Tiles_Pattern_4515704.jpg" width="500" /></p>\r\n'),
(2, 1, 'pages', 2, 0, '<h4>Конструктивный продукт</h4>\r\n\r\n<p>Целевая аудитория переворачивает комплексный анализ ситуации, учитывая результат предыдущих медиа-кампаний. Визуализация концепии синхронизирует мониторинг активности, отвоевывая свою долю рынка. Как предсказывают футурологи клиентский спрос притягивает обществвенный фирменный стиль, учитывая современные тенденции. Традиционный канал развивает диктат потребителя, полагаясь на инсайдерскую информацию. Маркетинговая коммуникация все еще интересна для многих. Исходя из структуры пирамиды Маслоу, перераспределение бюджета индуктивно концентрирует связанный клиентский спрос, отвоевывая рыночный сегмент.</p>\r\n'),
(3, 1, 'pages', 3, 0, '<h4>Из ряда вон выходящий имидж</h4>\r\n\r\n<p>А вот по мнению аналитиков основная стадия проведения рыночного исследования вырождена. Перераспределение бюджета создает конвергентный направленный маркетинг, признавая определенные рыночные тенденции. Продуктовый ассортимент традиционно индуцирует конструктивный BTL, полагаясь на инсайдерскую информацию. Такое понимание ситуации восходит к Эл Райс, при этом инструмент маркетинга определяет бренд, расширяя долю рынка. Рекламная площадка концентрирует рекламоноситель, повышая конкуренцию.</p>\r\n'),
(4, 2, 'pages', 1, 0, '<h2>Рыноный контент: конкурент или медиа</h2>\r\n\r\n<p>Управление брендом восстанавливает популярный имидж, опираясь на опыт западных коллег. Рейт-карта, безусловно, слабо охватывает креативный охват аудитории, используя опыт предыдущих кампаний. Маркетингово-ориентированное издание продуцирует социометрический медиабизнес, опираясь на опыт западных коллег. Медиапланирование, не меняя концепции, изложенной выше, директивно упорядочивает диктат потребителя, полагаясь на инсайдерскую информацию. Стимулирование коммьюнити транслирует традиционный канал, отвоевывая свою долю рынка. Отсюда естественно следует, что ценовая стратегия интегрирована.</p>\r\n\r\n<p>Российская специфика уравновешивает потребительский рекламоноситель, признавая определенные рыночные тенденции. Бизнес-модель программирует системный анализ, оптимизируя бюджеты. Взаимодействие корпорации и клиента усиливает направленный маркетинг, учитывая современные тенденции. Инструмент маркетинга конкурентоспособен.</p>\r\n\r\n<p>Жизненный цикл продукции экономит обществвенный пак-шот, повышая конкуренцию. Коммуникация, анализируя результаты рекламной кампании, раскручивает межличностный креатив, повышая конкуренцию. Согласно ставшей уже классической работе Филипа Котлера, воздействие на потребителя настроено позитивно. Один из признанных классиков маркетинга Ф.Котлер определяет это так: ретроконверсия национального наследия многопланово стабилизирует рейтинг, используя опыт предыдущих кампаний. Создание приверженного покупателя, безусловно, ускоряет комплексный BTL, опираясь на опыт западных коллег.</p>\r\n\r\n<div>{@module mod_gallery 1@}</div>\r\n'),
(5, 3, 'pages', 1, 0, '<p>Страница не найдена совсем</p>\r\n'),
(6, 4, 'pages', 1, 0, ''),
(7, 5, 'pages', 1, 0, '<h3>Наши координаты</h3>\r\n\r\n<p><strong>Twitter, Inc.</strong><br />\r\n795 Folsom Ave, Suite 600<br />\r\nSan Francisco, CA 94107<br />\r\nP: (123) 456-7890</p>\r\n\r\n<p><strong>Full Name</strong><br />\r\n<a href="mailto:#">first.last@example.com</a></p>\r\n'),
(8, 6, 'pages', 1, 0, ''),
(9, 7, 'pages', 1, 0, '<p>Маркетинговая активность, вопреки мнению П.Друкера, трансформирует конструктивный инструмент маркетинга, осознав маркетинг как часть производства. Интересно отметить, что VIP-мероприятие спонтанно программирует медиабизнес, отвоевывая свою долю рынка. Пак-шот, пренебрегая деталями, усиливает сублимированный рекламный клаттер, размещаясь во всех медиа. Управление брендом позитивно тормозит общественный департамент маркетинга и продаж, полагаясь на инсайдерскую информацию. Бизнес-план, суммируя приведенные примеры, версифицирован.</p>\r\n\r\n<p>Рыночная ситуация непосредственно изменяет побочный PR-эффект, отвоевывая рыночный сегмент. Метод изучения рынка сбалансирован. А вот по мнению аналитиков внутрифирменная реклама откровенна. Ассортиментная политика предприятия специфицирует product placement, признавая определенные рыночные тенденции.</p>\r\n\r\n<p>Идеология выстраивания бренда изоморфна времени. Идеология выстраивания бренда, отбрасывая подробности, неестественно ускоряет потребительский поведенческий таргетинг, полагаясь на инсайдерскую информацию. Контент вполне вероятен. Косвенная реклама, следовательно, переворачивает фирменный фирменный стиль, оптимизируя бюджеты. Емкость рынка решительно притягивает продвигаемый маркетинг, опираясь на опыт западных коллег.</p>\r\n\r\n<div><span>{@module mod_banner 1@}</span></div>\r\n'),
(11, 9, 'pages', 1, 0, ''),
(12, 10, 'pages', 1, 0, '<p>Маркетинговая активность, вопреки мнению П.Друкера, трансформирует конструктивный инструмент маркетинга, осознав маркетинг как часть производства. Интересно отметить, что VIP-мероприятие спонтанно программирует медиабизнес, отвоевывая свою долю рынка. Пак-шот, пренебрегая деталями, усиливает сублимированный рекламный клаттер, размещаясь во всех медиа. Управление брендом позитивно тормозит общественный департамент маркетинга и продаж, полагаясь на инсайдерскую информацию. Бизнес-план, суммируя приведенные примеры, версифицирован.</p>\r\n\r\n<p>Рыночная ситуация непосредственно изменяет побочный PR-эффект, отвоевывая рыночный сегмент. Метод изучения рынка сбалансирован. А вот по мнению аналитиков внутрифирменная реклама откровенна. Ассортиментная политика предприятия специфицирует product placement, признавая определенные рыночные тенденции.</p>\r\n\r\n<p>Идеология выстраивания бренда изоморфна времени. Идеология выстраивания бренда, отбрасывая подробности, неестественно ускоряет потребительский поведенческий таргетинг, полагаясь на инсайдерскую информацию. Контент вполне вероятен. Косвенная реклама, следовательно, переворачивает фирменный фирменный стиль, оптимизируя бюджеты. Емкость рынка решительно притягивает продвигаемый маркетинг, опираясь на опыт западных коллег.</p>\r\n'),
(13, 11, 'pages', 1, 0, '<p>Маркетинговая активность, вопреки мнению П.Друкера, трансформирует конструктивный инструмент маркетинга, осознав маркетинг как часть производства. Интересно отметить, что VIP-мероприятие спонтанно программирует медиабизнес, отвоевывая свою долю рынка. Пак-шот, пренебрегая деталями, усиливает сублимированный рекламный клаттер, размещаясь во всех медиа. Управление брендом позитивно тормозит общественный департамент маркетинга и продаж, полагаясь на инсайдерскую информацию. Бизнес-план, суммируя приведенные примеры, версифицирован.</p>\r\n\r\n<p>Рыночная ситуация непосредственно изменяет побочный PR-эффект, отвоевывая рыночный сегмент. Метод изучения рынка сбалансирован. А вот по мнению аналитиков внутрифирменная реклама откровенна. Ассортиментная политика предприятия специфицирует product placement, признавая определенные рыночные тенденции.</p>\r\n\r\n<p>Идеология выстраивания бренда изоморфна времени. Идеология выстраивания бренда, отбрасывая подробности, неестественно ускоряет потребительский поведенческий таргетинг, полагаясь на инсайдерскую информацию. Контент вполне вероятен. Косвенная реклама, следовательно, переворачивает фирменный фирменный стиль, оптимизируя бюджеты. Емкость рынка решительно притягивает продвигаемый маркетинг, опираясь на опыт западных коллег.</p>\r\n'),
(14, 8, 'pages', 1, 0, '<p>dgdfgdfg123</p>\r\n'),
(15, 12, 'pages', 1, 0, ''),
(16, 13, 'pages', 1, 0, ''),
(17, 14, 'pages', 1, 0, '');

DROP TABLE IF EXISTS `w_pages_cross_blocks`;
CREATE TABLE `w_pages_cross_blocks` (
  `cross_block_id` int(11) NOT NULL,
  `cross_block_name` varchar(255) NOT NULL,
  `cross_block_label` varchar(255) NOT NULL,
  `cross_block_content` text NOT NULL,
  `cross_block_active` tinyint(1) NOT NULL DEFAULT '1',
  `cross_block_lang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_pages_cross_blocks` (`cross_block_id`, `cross_block_name`, `cross_block_label`, `cross_block_content`, `cross_block_active`, `cross_block_lang_id`) VALUES
(1, 'Копирайты внизу страницы', 'copy', '<p>© 2007-2013 Webcomfort</p>\r\n', 1, 1);

DROP TABLE IF EXISTS `w_sessions`;
CREATE TABLE `w_sessions` (
  `id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_sessions` (`id`, `ip_address`, `timestamp`, `data`) VALUES
('04tgrolhcgsq19r5doknvsofnb8p2gum', '192.168.10.1', 1493375745, '__ci_last_regenerate|i:1493375745;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('08ka85isbds6nagfae5oarp5i7s35f6u', '127.0.0.1', 1490022464, '__ci_last_regenerate|i:1490022168;w_alang|s:1:"2";w_alang_f|s:7:"english";changelog_filter|s:6:"update";user_filter|i:1;news_filter|i:1;photo_filter|b:0;'),
('0en6a4n7el7hqd8c7p8cl5pp8be2cvmp', '192.168.10.1', 1493384762, '__ci_last_regenerate|i:1493384762;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('0ha9u99qq12nckk6tjo53qrnqh47l4j6', '127.0.0.1', 1490699075, '__ci_last_regenerate|i:1490699075;w_alang|i:1;w_alang_f|s:7:"russian";banner_filter|i:1;'),
('0siqrjuffmgdenu0tc8nmu81sa1uqhaf', '192.168.10.1', 1493386727, '__ci_last_regenerate|i:1493386727;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('18debie85lqd1c5e55da9mm5e12c56o7', '127.0.0.1', 1491916698, '__ci_last_regenerate|i:1491916401;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|s:1:"2";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;modules_filter|i:1;news_filter|N;'),
('1rhv0oj44bgg2lccm3t1lbm6hca79u6u', '127.0.0.1', 1490024097, '__ci_last_regenerate|i:1490024066;w_alang|s:1:"1";w_alang_f|s:7:"russian";changelog_filter|s:6:"update";user_filter|i:1;news_filter|i:1;photo_filter|i:2;page_filter|i:1;banner_filter|i:1;w_pages_parent|i:0;cross_blocks_filter|i:2;modules_filter|s:1:"1";'),
('2it82cg4hhbfnvr8osb3pjkmgr7kstp2', '192.168.10.1', 1493304382, '__ci_last_regenerate|i:1493304382;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('2siitrb6j6lk23pc8lh09ae3emotp54h', '127.0.0.1', 1491908133, '__ci_last_regenerate|i:1491907834;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|s:1:"1";'),
('2vvno0vond5mh4rsf2e9r2iof18g7dks', '192.168.10.1', 1493307397, '__ci_last_regenerate|i:1493307397;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('37rm4gqiq26ud8br6igrf81730usnqam', '192.168.10.1', 1493383978, '__ci_last_regenerate|i:1493383978;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('3e4l8gdpfsjchkfp7edttruug4tgaisb', '192.168.10.1', 1493211623, '__ci_last_regenerate|i:1493211594;w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|i:1;banner_filter|i:1;cross_blocks_filter|i:2;w_pages_parent|i:0;news_filter|N;'),
('3id7o04ab5mb9kv8vfe2ml8grfhk6pvf', '127.0.0.1', 1489753734, '__ci_last_regenerate|i:1489753434;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('3lhpda57gcvn68ljd6vids4u453qc68t', '192.168.10.1', 1493302923, '__ci_last_regenerate|i:1493302923;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('3nf694f1fsotu3eftb3qm51e5ink0o8h', '127.0.0.1', 1490342655, '__ci_last_regenerate|i:1490342541;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('43ecu7ntmmmrutn1jpnjl35rtmmpjqbr', '127.0.0.1', 1490610087, '__ci_last_regenerate|i:1490609897;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;news_filter|i:1;cross_blocks_filter|i:2;banner_filter|i:1;'),
('46alvq2cbpia13cuuos0o4dmulmh9g8r', '127.0.0.1', 1490695083, '__ci_last_regenerate|i:1490695079;w_alang|i:1;w_alang_f|s:7:"russian";banner_filter|i:1;'),
('46fean0ovfj6b60ro5vivd5ju9isqnum', '192.168.10.1', 1493307823, '__ci_last_regenerate|i:1493307823;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('46hoph3drus901c4ngd46sketorc7a0m', '127.0.0.1', 1491903236, '__ci_last_regenerate|i:1491903232;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('53lbk8skra7rpo5n3ci3n9ishovn347j', '127.0.0.1', 1491909018, '__ci_last_regenerate|i:1491909018;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|s:1:"1";'),
('54i6cn5rrbhcssnmr79cgbrviqou1big', '127.0.0.1', 1491904904, '__ci_last_regenerate|i:1491904769;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|i:1;'),
('55nqrhm0jckijn28pjvqaevb5lmi89l7', '127.0.0.1', 1490022978, '__ci_last_regenerate|i:1490022801;w_alang|s:1:"1";w_alang_f|s:7:"russian";changelog_filter|s:6:"update";user_filter|i:1;news_filter|i:1;photo_filter|i:2;page_filter|i:1;banner_filter|i:1;w_pages_parent|i:0;cross_blocks_filter|i:2;modules_filter|s:1:"2";'),
('587afegiubs4nqgueab6rseg77vujaqm', '127.0.0.1', 1490718309, '__ci_last_regenerate|i:1490718309;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";'),
('5m67v9j54617hbbg6ue6srl3rsvhall0', '127.0.0.1', 1490003904, '__ci_last_regenerate|i:1490003868;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";'),
('5v3sjua00v73ktq8bk8h48h65l4fgii0', '127.0.0.1', 1490014541, '__ci_last_regenerate|i:1490014522;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";page_filter|i:1;w_pages_parent|i:0;'),
('6pkuq90acj1qic6tvkeken4timcfttaq', '127.0.0.1', 1489758764, '__ci_last_regenerate|i:1489758597;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('6raenqqq1rft6ap0h06f1s2e1eobep20', '127.0.0.1', 1489753746, '__ci_last_regenerate|i:1489753741;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('6ujvnm0963l9bufikp83t3vh9075tji3', '127.0.0.1', 1489754616, '__ci_last_regenerate|i:1489754432;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('74grqvabssbiu26ldughv4vu4rvu9mbj', '192.168.10.1', 1493290137, '__ci_last_regenerate|i:1493290137;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;photo_filter|i:1;'),
('7ere9fr6dg2ul6dcuqnjudjgkfop3p1c', '127.0.0.1', 1490022735, '__ci_last_regenerate|i:1490022476;w_alang|s:1:"1";w_alang_f|s:7:"russian";changelog_filter|s:6:"update";user_filter|i:1;news_filter|i:1;photo_filter|i:2;page_filter|i:1;w_pages_parent|s:1:"1";'),
('7res0t6u8cehguqi8m49no7nga4s52uf', '192.168.10.1', 1493308137, '__ci_last_regenerate|i:1493308137;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('827dv6f2s6mlvkhggelle0gtjak99us5', '192.168.10.1', 1493301565, '__ci_last_regenerate|i:1493301565;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('8kk63rlsk2edflr0249kprnelc9fvcp5', '127.0.0.1', 1489755273, '__ci_last_regenerate|i:1489755000;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|s:1:"2";banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('9bkke0c8tv77a2bpfo94dpdfbqlribk7', '127.0.0.1', 1490283741, '__ci_last_regenerate|i:1490283699;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('9itcvr3q4es0bcon3kf7lglnhgg8vr2k', '127.0.0.1', 1491911331, '__ci_last_regenerate|i:1491911103;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|s:1:"0";'),
('9np12vji7jbkdmss1p76c2kep1iv9nna', '192.168.10.1', 1493292277, '__ci_last_regenerate|i:1493292277;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;photo_filter|i:1;changelog_filter|s:6:"delete";'),
('a0ov7ufdl019087g9c7miri1qn2vv0bb', '127.0.0.1', 1489758559, '__ci_last_regenerate|i:1489758296;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('a25tgi9qtmjrnnprbksh8mjd9s4qs5mg', '127.0.0.1', 1490867976, '__ci_last_regenerate|i:1490867976;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";'),
('aau5ke2ol2hc8o5qb5al369nkdol67ku', '127.0.0.1', 1489764336, '__ci_last_regenerate|i:1489764011;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('ahi9ga9eors5r915paj8rue26fm406pf', '192.168.10.1', 1493290886, '__ci_last_regenerate|i:1493290886;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;photo_filter|i:1;'),
('ap9rgih4nog1cpp2lrpde6h86pcvtt43', '127.0.0.1', 1491903952, '__ci_last_regenerate|i:1491903655;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;photo_filter|i:1;'),
('ave19rarhghgjrvltac4rsaim1o4cfpp', '127.0.0.1', 1490281673, '__ci_last_regenerate|i:1490281407;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('avtbidje6p4dt6akcgljgpq4ghgml1j7', '192.168.10.1', 1493389563, '__ci_last_regenerate|i:1493389279;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('b4ciht6fjgvd1p9edbuvoh7tf1tkn0vt', '192.168.10.1', 1493210163, '__ci_last_regenerate|i:1493210163;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;'),
('b5opaft5ic4ui6is9titt6r4el116gsi', '192.168.10.1', 1493289766, '__ci_last_regenerate|i:1493289766;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;photo_filter|i:1;'),
('bic6017jo7ciiplc64l9e6ekn8tivlls', '127.0.0.1', 1491911021, '__ci_last_regenerate|i:1491910794;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|s:9:"999999999";'),
('bmmjhulbmqe10njjsbfmlc48bug34fnc', '192.168.10.1', 1493389279, '__ci_last_regenerate|i:1493389279;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('br5f064mldibp7sic05deqnh270qumnl', '127.0.0.1', 1491907626, '__ci_last_regenerate|i:1491907368;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|i:1;'),
('btom06snvslnij2157g9h5accheng20e', '192.168.10.1', 1493208672, '__ci_last_regenerate|i:1493208672;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('butnqdm4pplhngv6dsb412gf3ma465fu', '127.0.0.1', 1489763644, '__ci_last_regenerate|i:1489763494;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('c0a8cdsqbseo678a9bq4s6ujjarcje7d', '127.0.0.1', 1489764512, '__ci_last_regenerate|i:1489764345;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;news_filter|i:1;photo_filter|s:1:"1";'),
('cj3e709ncnc18nner2fg73lfg6d9gg6j', '192.168.10.1', 1493305584, '__ci_last_regenerate|i:1493305584;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('csa943fq1opnkssb84eo6ntpae2rlls4', '127.0.0.1', 1491912378, '__ci_last_regenerate|i:1491912279;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('d360okt5omgm7cqfu6r21488if9lk0fr', '192.168.10.1', 1493379573, '__ci_last_regenerate|i:1493379573;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('d94prkobfqasp7o1ug40t2nvuh0249ai', '192.168.10.1', 1493373455, '__ci_last_regenerate|i:1493373455;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('db2mt5qk26g0it3ut2ju5e43bmp1cuh7', '192.168.10.1', 1493291675, '__ci_last_regenerate|i:1493291675;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;photo_filter|i:1;changelog_filter|s:6:"delete";'),
('df6v9a0t03pv53b8bpmfq8mpvem0vrrm', '127.0.0.1', 1489752857, '__ci_last_regenerate|i:1489752626;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('diukriisvfcl8bql2d6b97k3jcq88m72', '127.0.0.1', 1490014517, '__ci_last_regenerate|i:1490014517;'),
('dl46renjemin993t3u15f1lb4la2jem6', '127.0.0.1', 1490281890, '__ci_last_regenerate|i:1490281771;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('dlbi5chd49i64asfncg1khi18qjk51vo', '192.168.10.1', 1493297615, '__ci_last_regenerate|i:1493297615;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('dmcjoqenuois92sofrt306a68pieh1nq', '127.0.0.1', 1491914187, '__ci_last_regenerate|i:1491914187;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('dv6m8odfcfv42iud9m3h2d2upkb4phv6', '127.0.0.1', 1490094377, '__ci_last_regenerate|i:1490094337;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('e5h1tjh8221ks7lcjl40rih8ba64fhhk', '127.0.0.1', 1491913982, '__ci_last_regenerate|i:1491913770;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('egiam1k676js8uq7gmipvsbpte3fki8e', '192.168.10.1', 1493211594, '__ci_last_regenerate|i:1493211594;w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|i:1;banner_filter|i:1;cross_blocks_filter|i:2;w_pages_parent|i:0;news_filter|N;'),
('eiklc375sr5tr4rsb3dth869cqjeh8qh', '192.168.10.1', 1493305944, '__ci_last_regenerate|i:1493305944;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('em79j82fj0kvm4rv635cm9ba3argjpa3', '192.168.10.1', 1493308457, '__ci_last_regenerate|i:1493308457;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('faovfkvo7t2j8e0b4sh4lbe65le44f1e', '192.168.10.1', 1493306971, '__ci_last_regenerate|i:1493306971;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('fdn26q7iauoabfa8mbjtt6sikjnqknsc', '192.168.10.1', 1493375297, '__ci_last_regenerate|i:1493375297;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('fk16mnsofmeb8fd1dme19sgdqgqieg81', '192.168.10.1', 1493303294, '__ci_last_regenerate|i:1493303294;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('fmhm0gm17h3nrijbt36oorimes0ilva7', '127.0.0.1', 1490283378, '__ci_last_regenerate|i:1490283373;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('fp1c631r3l4ct2r397l7ehhr6da7m0ee', '127.0.0.1', 1491912181, '__ci_last_regenerate|i:1491911898;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('g175usppjebppkh9ssp8mtrl63cp4niq', '127.0.0.1', 1490282680, '__ci_last_regenerate|i:1490282393;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('g29k9b4bkf0nkqhshrggbur47arngda8', '192.168.10.1', 1493306519, '__ci_last_regenerate|i:1493306519;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('g5d0jk4b9tpctvm6k1oh3hlfms7u046s', '192.168.10.1', 1493383666, '__ci_last_regenerate|i:1493383666;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('gaig4tugesj8mvpi4iovm76v1r855bcv', '127.0.0.1', 1490691674, '__ci_last_regenerate|i:1490691674;w_alang|i:1;w_alang_f|s:7:"russian";banner_filter|i:1;'),
('h87hmij1ibhp0m4148vm4fkl19qpio93', '127.0.0.1', 1490777110, '__ci_last_regenerate|i:1490777110;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";'),
('hkk6u08le8lbap5naqghrnjj66uakr6l', '127.0.0.1', 1491915648, '__ci_last_regenerate|i:1491915529;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('hmr4ps6cki6j8ncjdp8frlavr77vqljd', '127.0.0.1', 1491908732, '__ci_last_regenerate|i:1491908611;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|s:1:"1";'),
('jfqcm04cs8m8a1ngig48hevakrlbqf93', '127.0.0.1', 1489753356, '__ci_last_regenerate|i:1489753117;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('jq9cj8lnrg3e17v1dju9ne8l5so5q05a', '127.0.0.1', 1490282935, '__ci_last_regenerate|i:1490282698;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('jumtmfd3ivkpu5m5cnfrnci4ghq58ni3', '127.0.0.1', 1489763026, '__ci_last_regenerate|i:1489762804;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('k3di5i41qguggqqniop80n2qtk13dcg2', '192.168.10.1', 1493305140, '__ci_last_regenerate|i:1493305140;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('k6b5vpapktmqi0hf5lhutq9iacj65drh', '127.0.0.1', 1490091570, '__ci_last_regenerate|i:1490091560;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('l6oooo6fpf8rilk286ujncu9cdj7fjb9', '127.0.0.1', 1490602274, '__ci_last_regenerate|i:1490602249;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('l7jdqb6iv0ujk0qa1bl9oqci09ffoeou', '192.168.10.1', 1493382749, '__ci_last_regenerate|i:1493382749;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('l855hph3o727cp53ca0ju13f6j287c3j', '192.168.10.1', 1493298241, '__ci_last_regenerate|i:1493298241;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('lhjscsqv38rr7qre5q69i7prn9nid0of', '127.0.0.1', 1491911659, '__ci_last_regenerate|i:1491911491;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|s:1:"0";'),
('lk0087hu5divcc49o08n8kvd7sjdkgs7', '127.0.0.1', 1490951317, '__ci_last_regenerate|i:1490951317;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";'),
('lt6kqbnhr131v304nc0gi1a6fr5pu0b1', '192.168.10.1', 1493302602, '__ci_last_regenerate|i:1493302602;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('m20dcvupm9m71kbduevhs50jqrsn2aqe', '127.0.0.1', 1491915294, '__ci_last_regenerate|i:1491915042;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('m2trovomatgprsppmsr8n4b2lbrimdom', '127.0.0.1', 1491209371, '__ci_last_regenerate|i:1491209371;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";'),
('m523demch1d542h7d741tpofh5ittvs1', '127.0.0.1', 1489760196, '__ci_last_regenerate|i:1489760196;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('me9lvr7j5vvfntfrtrvgt8pklu1hmn9b', '127.0.0.1', 1491908225, '__ci_last_regenerate|i:1491908208;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|s:1:"1";'),
('mf4qbeccp9i9e7l7dan9dm7u2uveof5s', '127.0.0.1', 1491913507, '__ci_last_regenerate|i:1491913300;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('mlh93igghn7np4ap50d5g8vomedpspvp', '192.168.10.1', 1493374962, '__ci_last_regenerate|i:1493374962;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('mlp3a92u4n42adcqe4r4alvlr3iidu1m', '192.168.10.1', 1493379019, '__ci_last_regenerate|i:1493379019;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('mmlbna6opa131oaf01b64lahp7ldsu8m', '192.168.10.1', 1493292667, '__ci_last_regenerate|i:1493292667;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;photo_filter|i:1;changelog_filter|s:6:"delete";'),
('murbsjpqt5erg2euqsltlevnaksacamg', '127.0.0.1', 1490702137, '__ci_last_regenerate|i:1490702059;w_alang|i:1;w_alang_f|s:7:"russian";banner_filter|i:1;page_filter|i:1;w_pages_parent|i:0;cross_blocks_filter|i:2;changelog_filter|s:6:"delete";'),
('nhss2msgjrmhvt2lco7bgr6shnrndku5', '192.168.10.1', 1493299367, '__ci_last_regenerate|i:1493299367;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('nqpla26e2ng6bpg0evlkrmvl01i48b1k', '127.0.0.1', 1489752583, '__ci_last_regenerate|i:1489752319;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('ntkqt5atq1fe66o52pqp5rhnf3df8qoc', '127.0.0.1', 1490084951, '__ci_last_regenerate|i:1490084951;'),
('o2ks9ngaiukat8getfeear8ruml3rfs1', '192.168.10.1', 1493380449, '__ci_last_regenerate|i:1493380449;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('o7b7v71jhjd9a088t911rgl0eo1kn783', '192.168.10.1', 1493387772, '__ci_last_regenerate|i:1493387772;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('of1vrveubie11nbrqnd2s7no64ftfa7q', '127.0.0.1', 1491904360, '__ci_last_regenerate|i:1491904209;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;photo_filter|i:1;'),
('onuemvsv8frgd5snevk31j8ro32ns017', '127.0.0.1', 1489755811, '__ci_last_regenerate|i:1489755645;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|s:1:"2";banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('oqgub6ub7qvoth8079m3fq4of9di7h19', '192.168.10.1', 1493301943, '__ci_last_regenerate|i:1493301943;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('p64p5hs85slnbhphp83ekmq6r0tign9o', '192.168.10.1', 1493373125, '__ci_last_regenerate|i:1493373125;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('pn8qg1peb12kgo6fnvbisd3kr78jjlv4', '192.168.10.1', 1493302253, '__ci_last_regenerate|i:1493302253;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('r2sf6c76rgd9eolht3a1n1t49ebl4q43', '127.0.0.1', 1490605428, '__ci_last_regenerate|i:1490605427;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('rd1q1rjr0pj7hnctjcb0ek3c8j0b7a03', '127.0.0.1', 1489756464, '__ci_last_regenerate|i:1489756325;search|s:14:"Нишевый";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('rql7kssm34vqkff681325jsdlnetslra', '127.0.0.1', 1489758229, '__ci_last_regenerate|i:1489757936;search|s:14:"Нишевый";w_alang|s:1:"2";w_alang_f|s:7:"english";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('s1hl8s47l40crhb0v9b44k08akl0c1ol', '127.0.0.1', 1490084953, '__ci_last_regenerate|i:1490084953;'),
('sa3f45dndf9ds36de39c7vgd9ioi3lot', '127.0.0.1', 1489752191, '__ci_last_regenerate|i:1489751997;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('scjkfc1pe3ur6k2k8t97cp3oqtl5s772', '127.0.0.1', 1491912868, '__ci_last_regenerate|i:1491912632;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('sn8rh1rr5597g56fbisoe0cr12r8v6m7', '192.168.10.1', 1493308457, '__ci_last_regenerate|i:1493308457;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('su0d8vou8bquo4vl57vv0tg0u0nqnvd9', '192.168.10.1', 1493372810, '__ci_last_regenerate|i:1493372810;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('t0hdbbhakquq38dveiu3bjtrf1k1tol9', '127.0.0.1', 1491916914, '__ci_last_regenerate|i:1491916718;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;modules_filter|i:1;news_filter|N;photo_filter|i:1;search|s:20:"футурологи";'),
('t72hc4pj0a48lso421gbfledh15a0b1h', '127.0.0.1', 1489755569, '__ci_last_regenerate|i:1489755318;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|s:1:"2";banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('t8vp67nek13jm5901me5ej5fbqkiikis', '127.0.0.1', 1490023363, '__ci_last_regenerate|i:1490023291;w_alang|s:1:"1";w_alang_f|s:7:"russian";changelog_filter|s:6:"update";user_filter|i:1;news_filter|i:1;photo_filter|i:2;page_filter|i:1;banner_filter|i:1;w_pages_parent|i:0;cross_blocks_filter|i:2;modules_filter|s:1:"2";'),
('tnshnm5h5qmmhq5tic426oudgc3dcblv', '127.0.0.1', 1489756293, '__ci_last_regenerate|i:1489755998;search|s:26:"предсказывают";w_alang|s:1:"1";w_alang_f|s:7:"russian";page_filter|s:1:"1";w_pages_parent|i:0;banner_filter|s:1:"1";cross_blocks_filter|s:1:"1";user_filter|i:1;changelog_filter|s:6:"delete";modules_filter|i:1;'),
('tqqrcdhlc9121b8tnamqgo7hih6rkl02', '192.168.10.1', 1493298725, '__ci_last_regenerate|i:1493298725;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";'),
('tvr9rncifoe07k6o3c2amasqd8bjon41', '127.0.0.1', 1490283121, '__ci_last_regenerate|i:1490283035;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('ubnt3ump67f3ubmmasausjksb22c17e9', '127.0.0.1', 1491913224, '__ci_last_regenerate|i:1491912960;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;cross_blocks_filter|i:2;w_cms_pages_parent|i:0;news_filter|N;'),
('urc8nf3p12dhf8bglbmtt2qqc2s5veeu', '192.168.10.1', 1493383322, '__ci_last_regenerate|i:1493383322;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;'),
('v0nojhvvdbm5o0usfqrto7fv361g96at', '127.0.0.1', 1491042202, '__ci_last_regenerate|i:1491042202;w_alang|i:1;w_alang_f|s:7:"russian";changelog_filter|s:6:"delete";'),
('vdli4oi3ha1kusrqhilu97d4bj2ir16u', '192.168.10.1', 1493297210, '__ci_last_regenerate|i:1493297210;w_alang|i:1;w_alang_f|s:7:"russian";page_filter|i:1;w_pages_parent|i:0;banner_filter|i:1;changelog_filter|s:6:"delete";');

DROP TABLE IF EXISTS `w_user`;
CREATE TABLE `w_user` (
  `user_id` int(11) NOT NULL,
  `user_group_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_second_name` varchar(255) NOT NULL,
  `user_surname` varchar(255) NOT NULL,
  `user_nic` varchar(255) NOT NULL,
  `user_name_pref` tinyint(1) NOT NULL DEFAULT '0',
  `user_email` varchar(255) NOT NULL,
  `user_pass` varchar(255) NOT NULL,
  `user_hash` varchar(255) NOT NULL,
  `user_ip` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `user_restore_hash` varchar(255) NOT NULL,
  `user_restore_time` date NOT NULL,
  `user_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

INSERT INTO `w_user` (`user_id`, `user_group_id`, `user_name`, `user_second_name`, `user_surname`, `user_nic`, `user_name_pref`, `user_email`, `user_pass`, `user_hash`, `user_ip`, `user_agent`, `user_restore_hash`, `user_restore_time`, `user_active`) VALUES
(1, 1, 'Админ', '', 'Тестовый', 'wcms', 1, 'info@webcomfort.ru', 'f813ef1d27ee1dd4c74fc7845b0feaf4', 'dfbf0314a17a513f01d389cc8194139c', '192.168.10.1', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36 OPR/43.0.2442.1144', '222bf88ffc8066d942a7170e9fc65a94', '2017-01-17', 1);

DROP TABLE IF EXISTS `w_user_rules`;
CREATE TABLE `w_user_rules` (
  `rule_id` int(11) NOT NULL,
  `rule_user_id` int(11) NOT NULL,
  `rule_model_id` int(11) NOT NULL,
  `rule_view` tinyint(1) NOT NULL DEFAULT '0',
  `rule_add` tinyint(1) NOT NULL DEFAULT '0',
  `rule_edit` tinyint(1) NOT NULL DEFAULT '0',
  `rule_copy` tinyint(1) NOT NULL DEFAULT '0',
  `rule_delete` tinyint(1) NOT NULL DEFAULT '0',
  `rule_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `w_user_rules` (`rule_id`, `rule_user_id`, `rule_model_id`, `rule_view`, `rule_add`, `rule_edit`, `rule_copy`, `rule_delete`, `rule_active`) VALUES
(675, 1, 1, 1, 1, 1, 1, 1, 1),
(676, 1, 2, 1, 1, 1, 1, 1, 1),
(677, 1, 3, 1, 1, 1, 1, 1, 1),
(678, 1, 4, 1, 1, 1, 1, 1, 1),
(679, 1, 5, 1, 1, 1, 1, 1, 1),
(680, 1, 6, 1, 1, 1, 1, 1, 1),
(681, 1, 7, 1, 1, 1, 1, 1, 1),
(682, 1, 8, 1, 1, 1, 1, 1, 1),
(683, 1, 9, 1, 1, 1, 1, 1, 1),
(684, 1, 10, 1, 1, 1, 1, 1, 1),
(685, 1, 11, 1, 1, 1, 1, 1, 1),
(686, 1, 14, 1, 1, 1, 1, 1, 1);


ALTER TABLE `w_backgrounds`
  ADD PRIMARY KEY (`bg_id`),
  ADD KEY `banner_lang_id` (`bg_lang_id`),
  ADD KEY `banner_active` (`bg_active`);

ALTER TABLE `w_banners`
  ADD PRIMARY KEY (`banner_id`),
  ADD KEY `banner_lang_id` (`banner_lang_id`),
  ADD KEY `banner_place_id` (`banner_place_id`),
  ADD KEY `banner_active` (`banner_active`);

ALTER TABLE `w_changelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operation` (`operation`);

ALTER TABLE `w_cms_configs`
  ADD PRIMARY KEY (`config_id`),
  ADD KEY `config_module_label` (`config_module_label`),
  ADD KEY `config_lang_id` (`config_lang_id`);

ALTER TABLE `w_cms_modules`
  ADD PRIMARY KEY (`module_id`);

ALTER TABLE `w_cms_pages`
  ADD PRIMARY KEY (`cms_page_id`),
  ADD KEY `cms_page_pid` (`cms_page_pid`);

ALTER TABLE `w_galleries`
  ADD PRIMARY KEY (`gallery_id`),
  ADD KEY `gallery_lang_id` (`gallery_lang_id`),
  ADD KEY `gallery_active` (`gallery_active`);

ALTER TABLE `w_gallery_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `photo_lang_id` (`photo_lang_id`),
  ADD KEY `photo_gallery_id` (`photo_gallery_id`),
  ADD KEY `photo_active` (`photo_active`);

ALTER TABLE `w_includes`
  ADD PRIMARY KEY (`i_id`),
  ADD KEY `page_id` (`obj_id`),
  ADD KEY `inc_id` (`inc_id`);

ALTER TABLE `w_indexing_index`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_index_linkword` (`link`,`word`);

ALTER TABLE `w_indexing_link`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `w_indexing_word`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_word_word` (`word`(8)),
  ADD KEY `idx_word_sound` (`sound`);

ALTER TABLE `w_news`
  ADD PRIMARY KEY (`news_id`),
  ADD KEY `news_lang_id` (`news_lang_id`),
  ADD KEY `news_date` (`news_date`),
  ADD KEY `news_url` (`news_url`),
  ADD KEY `news_active` (`news_active`);

ALTER TABLE `w_news_categories`
  ADD PRIMARY KEY (`news_cat_id`);

ALTER TABLE `w_news_categories_cross`
  ADD PRIMARY KEY (`ncc_id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `news_cat_id` (`news_cat_id`);

ALTER TABLE `w_pages`
  ADD PRIMARY KEY (`page_id`),
  ADD KEY `page_parent_id` (`page_pid`),
  ADD KEY `page_menu_id` (`page_menu_id`),
  ADD KEY `page_lang_id` (`page_lang_id`);

ALTER TABLE `w_pages_articles`
  ADD PRIMARY KEY (`article_id`),
  ADD KEY `article_page_id` (`article_pid`),
  ADD KEY `article_id` (`article_order`);

ALTER TABLE `w_pages_cross_blocks`
  ADD PRIMARY KEY (`cross_block_id`),
  ADD KEY `cross_block_lang_id` (`cross_block_lang_id`),
  ADD KEY `cross_block_active` (`cross_block_active`);

ALTER TABLE `w_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `last_activity_idx` (`timestamp`);

ALTER TABLE `w_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `user_group_id` (`user_group_id`),
  ADD KEY `user_active` (`user_active`);

ALTER TABLE `w_user_rules`
  ADD PRIMARY KEY (`rule_id`),
  ADD KEY `rule_user_id` (`rule_user_id`),
  ADD KEY `rule_cms_module_id` (`rule_model_id`);


ALTER TABLE `w_backgrounds`
  MODIFY `bg_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `w_banners`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `w_changelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `w_cms_configs`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `w_cms_modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
ALTER TABLE `w_cms_pages`
  MODIFY `cms_page_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
ALTER TABLE `w_galleries`
  MODIFY `gallery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `w_gallery_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
ALTER TABLE `w_includes`
  MODIFY `i_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;
ALTER TABLE `w_indexing_index`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `w_indexing_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `w_indexing_word`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `w_news`
  MODIFY `news_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `w_news_categories`
  MODIFY `news_cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `w_news_categories_cross`
  MODIFY `ncc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;
ALTER TABLE `w_pages`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
ALTER TABLE `w_pages_articles`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
ALTER TABLE `w_pages_cross_blocks`
  MODIFY `cross_block_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `w_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `w_user_rules`
  MODIFY `rule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=687;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
