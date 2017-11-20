<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_cms extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'bg_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'bg_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'bg_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '1',
            ),
        ));
        $this->dbforge->add_key('bg_id', TRUE);
        $this->dbforge->add_key('bg_active');
        $this->dbforge->create_table('w_backgrounds', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'banner_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'banner_place_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'banner_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'banner_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '1',
            ),
            'banner_blank' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0',
            ),
            'banner_code' => array(
                'type' => 'text'
            ),
            'banner_link' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'banner_view_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'banner_sort' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'banner_click' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'banner_lang_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('banner_id', TRUE);
        $this->dbforge->add_key('banner_lang_id');
        $this->dbforge->add_key('banner_place_id');
        $this->dbforge->add_key('banner_active');
        $this->dbforge->create_table('w_banners', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'pid' => array(
                'type' => 'int',
                'constraint' => 11,
                'default' => '0',
            ),
            'description' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'updated' => array(
                'type' => 'datetime'
            ),
            'user' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'host' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'operation' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'tab' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'rowkey' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'col' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'files' => array(
                'type' => 'blob'
            ),
            'oldval' => array(
                'type' => 'blob'
            ),
            'newval' => array(
                'type' => 'blob'
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('operation');
        $this->dbforge->create_table('w_changelog', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'config_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'config_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'config_label' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'config_value' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'config_module_label' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'config_lang_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('config_id', TRUE);
        $this->dbforge->add_key('config_module_label');
        $this->dbforge->add_key('config_lang_id');
        $this->dbforge->create_table('w_cms_configs', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));
        // Параметры конфигурации
        $this->db->query("INSERT INTO `w_cms_configs` (`config_id`, `config_name`, `config_label`, `config_value`, `config_module_label`, `config_lang_id`) VALUES (1, 'Форма для контактов - адрес электронной почты для отправки письма', 'contacts_email', '".$this->config->item('cms_admin_email')."', 'mod_contacts.php', 1);");

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'module_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'module_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'module_file' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'module_type' => array(
                'type' => 'tinyint',
                'constraint' => 1,
            ),
            'module_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '1',
            ),
            'module_sort' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('module_id', TRUE);
        $this->dbforge->create_table('w_cms_modules', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));
        // Модули администрирования
        $this->db->query("INSERT INTO `w_cms_modules` (`module_id`, `module_name`, `module_file`, `module_type`, `module_active`, `module_sort`) VALUES 
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
(16, 'Вывод поста/новости (служебный)', 'Mod_news.php', 1, 1, 1491916603),
(17, 'Фоны', 'Adm_backgrounds.php', 2, 1, 1493809645),
(18, 'Вендоры', 'Adm_shop_vendor.php', 2, 1, 1499937434),
(19, 'Категории', 'Adm_shop_categories.php', 2, 1, 1499942236),
(20, 'Характеристики', 'Adm_shop_fields.php', 2, 1, 1499953231),
(21, 'Товары', 'Adm_shop_item.php', 2, 1, 1500456310),
(22, 'Типы', 'Adm_shop_types.php', 2, 1, 1500556775),
(23, 'Теги', 'Adm_tags.php', 2, 1, 1500556775)
");

        /*
         *
         * */

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'cms_page_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'cms_page_pid' => array(
                'type' => 'int',
                'constraint' => 11,
                'default' => '0',
            ),
            'cms_page_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'cms_page_model_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'cms_page_view_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'cms_page_sort' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'cms_page_status' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('cms_page_id', TRUE);
        $this->dbforge->add_key('cms_page_pid');
        $this->dbforge->create_table('w_cms_pages', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));
        // Страницы администрирования
        $this->db->query("INSERT INTO `w_cms_pages` (`cms_page_id`, `cms_page_pid`, `cms_page_name`, `cms_page_model_id`, `cms_page_view_id`, `cms_page_sort`, `cms_page_status`) VALUES 
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
(15, 1, 'Модули', 14, 1, 1369146092, 1),
(16, 5, 'Фоны', 17, 1, 1493809673, 1),
(17, 0, 'Каталог', 0, 1, 1367927286, 3),
(18, 17, 'Вендоры', 18, 1, 1500456340, 1),
(19, 17, 'Категории', 19, 1, 1499942261, 1),
(20, 21, 'Характеристики', 20, 1, 1500556807, 1),
(21, 17, 'Товары', 21, 1, 1499937610, 1),
(22, 21, 'Типы', 22, 1, 1499953249, 1),
(23, 10, 'Теги', 23, 1, 1369811300, 1)
");

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'gallery_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'gallery_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'gallery_view_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'gallery_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '1',
            ),
            'gallery_lang_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('gallery_id', TRUE);
        $this->dbforge->add_key('gallery_lang_id');
        $this->dbforge->add_key('gallery_active');
        $this->dbforge->create_table('w_galleries', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'photo_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'photo_gallery_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'photo_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'photo_sort' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'photo_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '1',
            ),
            'photo_link' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'photo_text' => array(
                'type' => 'text',
            ),
            'photo_lang_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('photo_id', TRUE);
        $this->dbforge->add_key('photo_lang_id');
        $this->dbforge->add_key('photo_gallery_id');
        $this->dbforge->add_key('photo_active');
        $this->dbforge->create_table('w_gallery_photos', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'i_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'obj_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'inc_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'inc_value' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'inc_type' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
        ));
        $this->dbforge->add_key('i_id', TRUE);
        $this->dbforge->add_key('obj_id');
        $this->dbforge->add_key('inc_id');
        $this->dbforge->create_table('w_includes', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'url' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'link' => array(
                'type' => 'int',
                'constraint' => 11,
                'default' => '0',
            ),
            'word' => array(
                'type' => 'int',
                'constraint' => 11,
                'default' => '0',
            ),
            'times' => array(
                'type' => 'int',
                'constraint' => 11,
                'default' => '0',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key(array('link', 'word'));
        $this->dbforge->create_table('w_indexing_index', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'url' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'title' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'short' => array(
                'type' => 'text',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('w_indexing_link', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'url' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'word' => array(
                'type' => 'varchar',
                'constraint' => 30,
            ),
            'sound' => array(
                'type' => 'char',
                'constraint' => 4,
                'default' => 'A000',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('word');
        $this->dbforge->add_key('sound');
        $this->dbforge->create_table('w_indexing_word', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'news_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'news_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'news_date' => array(
                'type' => 'datetime',
            ),
            'news_cut' => array(
                'type' => 'text',
            ),
            'news_url' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'news_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '1',
            ),
            'news_meta_title' => array(
                'type' => 'text',
            ),
            'news_meta_keywords' => array(
                'type' => 'text',
            ),
            'news_meta_description' => array(
                'type' => 'text',
            ),
            'news_lang_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('news_id', TRUE);
        $this->dbforge->add_key('news_lang_id');
        $this->dbforge->add_key('news_date');
        $this->dbforge->add_key('news_url');
        $this->dbforge->add_key('news_active');
        $this->dbforge->create_table('w_news', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'news_cat_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'news_cat_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'news_cat_view_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('news_cat_id', TRUE);
        $this->dbforge->create_table('w_news_categories', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'ncc_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'news_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'news_cat_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('ncc_id', TRUE);
        $this->dbforge->add_key('news_id');
        $this->dbforge->add_key('news_cat_id');
        $this->dbforge->create_table('w_news_categories_cross', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'page_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'page_pid' => array(
                'type' => 'int',
                'constraint' => 11,
                'default' => '0',
            ),
            'page_menu_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'page_name' => array(
                'type' => 'text',
            ),
            'page_url' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'page_meta_title' => array(
                'type' => 'text',
            ),
            'page_link_title' => array(
                'type' => 'text',
            ),
            'page_meta_keywords' => array(
                'type' => 'text',
            ),
            'page_meta_description' => array(
                'type' => 'text',
            ),
            'page_meta_additional' => array(
                'type' => 'text',
            ),
            'page_footer_additional' => array(
                'type' => 'text',
            ),
            'page_view_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'page_status' => array(
                'type' => 'int',
                'constraint' => 11,
                'default' => '2'
            ),
            'page_sort' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'page_redirect' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'page_lang_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),

        ));
        $this->dbforge->add_key('page_id', TRUE);
        $this->dbforge->add_key('page_pid');
        $this->dbforge->add_key('page_menu_id');
        $this->dbforge->add_key('page_lang_id');
        $this->dbforge->create_table('w_pages', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));
        // Главная страница
        $this->db->query("INSERT INTO `w_pages` (`page_id`, `page_pid`, `page_menu_id`, `page_name`, `page_url`, `page_meta_title`, `page_link_title`, `page_meta_keywords`, `page_meta_description`, `page_meta_additional`, `page_footer_additional`, `page_view_id`, `page_status`, `page_sort`, `page_redirect`, `page_lang_id`) VALUES (1, 0, 1, 'Главная', 'index', 'Главная страница', 'Заголовок ссылки', '', '', '', '', 2, 1, 10, '', 1)");

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'article_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'article_pid' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'article_pid_type' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'article_order' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'article_bg_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'article_view_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'article_place_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'article_text' => array(
                'type' => 'text',
            ),
        ));
        $this->dbforge->add_key('article_id', TRUE);
        $this->dbforge->add_key('article_pid');
        $this->dbforge->add_key('article_order');
        $this->dbforge->create_table('w_pages_articles', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'cross_block_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'cross_block_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'cross_block_label' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'cross_block_content' => array(
                'type' => 'text',
            ),
            'cross_block_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '1'
            ),
            'cross_block_lang_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
        ));
        $this->dbforge->add_key('cross_block_id', TRUE);
        $this->dbforge->add_key('cross_block_lang_id');
        $this->dbforge->add_key('cross_block_active');
        $this->dbforge->create_table('w_pages_cross_blocks', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'varchar',
                'constraint' => 40,
                'default' => '0',
            ),
            'ip_address' => array(
                'type' => 'varchar',
                'constraint' => 45,
                'default' => '0',
            ),
            'timestamp' => array(
                'type' => 'int',
                'constraint' => 10,
                'default' => '0',
            ),
            'data' => array(
                'type' => 'text',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('timestamp');
        $this->dbforge->create_table('w_sessions', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'user_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_group_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'user_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_second_name' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_surname' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_nic' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_name_pref' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
            'user_email' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_pass' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_hash' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_ip' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_agent' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_restore_hash' => array(
                'type' => 'varchar',
                'constraint' => 255,
            ),
            'user_restore_time' => array(
                'type' => 'date',
            ),
            'user_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
        ));
        $this->dbforge->add_key('user_id', TRUE);
        $this->dbforge->add_key('user_group_id');
        $this->dbforge->add_key('user_active');
        $this->dbforge->create_table('w_user', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));
        // Добавляем уникальный ключ
        $this->db->query('ALTER TABLE `w_user` ADD UNIQUE KEY `user_email` (`user_email`)');
        // Добавляем администратора
        $this->db->query("INSERT INTO `w_user` (`user_id`, `user_group_id`, `user_name`, `user_second_name`, `user_surname`, `user_nic`, `user_name_pref`, `user_email`, `user_pass`, `user_hash`, `user_ip`, `user_agent`, `user_restore_hash`, `user_restore_time`, `user_active`) VALUES (1, 1, 'Админ', '', 'Тестовый', '', 1, '".$this->config->item('cms_admin_email')."', '', '', '', '', '', '', 1)");

        // ------------------------------------------------------------------------

        $this->dbforge->add_field(array(
            'rule_id' => array(
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'rule_user_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'rule_model_id' => array(
                'type' => 'int',
                'constraint' => 11,
            ),
            'rule_view' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
            'rule_add' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
            'rule_edit' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
            'rule_copy' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
            'rule_delete' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
            'rule_active' => array(
                'type' => 'tinyint',
                'constraint' => 1,
                'default' => '0'
            ),
        ));
        $this->dbforge->add_key('rule_id', TRUE);
        $this->dbforge->add_key('rule_user_id');
        $this->dbforge->add_key('rule_model_id');
        $this->dbforge->create_table('w_user_rules', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));
        // Добавляем права администратору
        $this->db->query("INSERT INTO `w_user_rules` (`rule_id`, `rule_user_id`, `rule_model_id`, `rule_view`, `rule_add`, `rule_edit`, `rule_copy`, `rule_delete`, `rule_active`) VALUES 
(1, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 1, 2, 1, 1, 1, 1, 1, 1),
(3, 1, 3, 1, 1, 1, 1, 1, 1),
(4, 1, 4, 1, 1, 1, 1, 1, 1),
(5, 1, 5, 1, 1, 1, 1, 1, 1),
(6, 1, 6, 1, 1, 1, 1, 1, 1),
(7, 1, 7, 1, 1, 1, 1, 1, 1),
(8, 1, 8, 1, 1, 1, 1, 1, 1),
(9, 1, 9, 1, 1, 1, 1, 1, 1),
(10, 1, 10, 1, 1, 1, 1, 1, 1),
(11, 1, 11, 1, 1, 1, 1, 1, 1),
(14, 1, 12, 1, 1, 1, 1, 1, 1),
(17, 1, 13, 1, 1, 1, 1, 1, 1),
(18, 1, 14, 1, 1, 1, 1, 1, 1),
(19, 1, 15, 1, 1, 1, 1, 1, 1),
(20, 1, 16, 1, 1, 1, 1, 1, 1),
(21, 1, 17, 1, 1, 1, 1, 1, 1),
(22, 1, 18, 1, 1, 1, 1, 1, 1),
(23, 1, 19, 1, 1, 1, 1, 1, 1)
");

	    // ----------------------------------- КАТАЛОГ -------------------------------------

	    // Категории
	    $this->dbforge->add_field(array(
		    'cat_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'cat_pid' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'cat_name' => array(
			    'type' => 'text',
		    ),
		    'cat_desc' => array(
			    'type' => 'text',
		    ),
		    'cat_url' => array(
			    'type' => 'varchar',
			    'constraint' => 255,
		    ),
		    'cat_active' => array(
			    'type' => 'tinyint',
			    'constraint' => 1,
			    'default' => '0',
		    ),
		    'cat_sort' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'cat_meta_title' => array(
			    'type' => 'text',
		    ),
		    'cat_meta_keywords' => array(
			    'type' => 'text',
		    ),
		    'cat_meta_description' => array(
			    'type' => 'text',
		    ),
		    'cat_seo' => array(
			    'type' => 'text',
		    ),
		    'cat_lang_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('cat_id', TRUE);
	    $this->dbforge->add_key('cat_pid');
	    $this->dbforge->create_table('w_shop_categories', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Поля
	    $this->dbforge->add_field(array(
		    'field_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'field_type_back' => array(
			    'type' => 'text',
		    ),
		    'field_type_front' => array(
			    'type' => 'text',
		    ),
		    'field_name' => array(
			    'type' => 'text',
		    ),
		    'field_label' => array(
			    'type' => 'varchar',
			    'constraint' => 255,
		    ),
		    'field_active' => array(
			    'type' => 'tinyint',
			    'constraint' => 1,
			    'default' => '0',
		    ),
		    'field_lang_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('field_id', TRUE);
	    $this->dbforge->create_table('w_shop_fields', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

		// Товары
	    $this->dbforge->add_field(array(
		    'item_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'item_pid' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_article' => array(
			    'type' => 'text',
		    ),
		    'item_vendor_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_type_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_name' => array(
			    'type' => 'text',
		    ),
		    'item_cut' => array(
			    'type' => 'text',
		    ),
		    'item_url' => array(
			    'type' => 'varchar',
			    'constraint' => 255,
		    ),
		    'item_price' => array(
			    'type' => 'double',
		    ),
		    'item_price_old' => array(
			    'type' => 'double',
		    ),
		    'item_price_curr' => array(
			    'type' => 'varchar',
			    'constraint' => 3,
		    ),
		    'item_avail' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_active' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_label' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_sort' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_meta_title' => array(
			    'type' => 'text',
		    ),
		    'item_meta_keywords' => array(
			    'type' => 'text',
		    ),
		    'item_meta_description' => array(
			    'type' => 'text',
		    ),
		    'item_seo' => array(
			    'type' => 'text',
		    ),
		    'item_lang_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('item_id', TRUE);
	    $this->dbforge->add_key('item_pid');
	    $this->dbforge->add_key('item_vendor_id');
	    $this->dbforge->add_key('item_price');
	    $this->dbforge->add_key('item_avail');
	    $this->dbforge->add_key('item_sort');
	    $this->dbforge->create_table('w_shop_items', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Пересечения товаров и категорий
	    $this->dbforge->add_field(array(
		    'sic_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'item_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'cat_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('sic_id', TRUE);
	    $this->dbforge->add_key('item_id');
	    $this->dbforge->add_key('cat_id');
	    $this->dbforge->create_table('w_shop_items_cats', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Значения параметров
	    $this->dbforge->add_field(array(
		    'par_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'item_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'field_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'par_value' => array(
			    'type' => 'text',
		    ),
	    ));
	    $this->dbforge->add_key('par_id', TRUE);
	    $this->dbforge->add_key('item_id');
	    $this->dbforge->add_key('field_id');
	    $this->dbforge->create_table('w_shop_items_params', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Типы
	    $this->dbforge->add_field(array(
		    'type_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'type_name' => array(
			    'type' => 'text',
		    ),
	    ));
	    $this->dbforge->add_key('type_id', TRUE);
	    $this->dbforge->create_table('w_shop_types', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Значения полей для типов
		$this->dbforge->add_field(array(
		    'tf_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'type_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'field_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'field_values' => array(
			    'type' => 'text',
		    ),
		    'field_default_values' => array(
			    'type' => 'text',
		    ),
		    'field_filter' => array(
			    'type' => 'tinyint',
			    'constraint' => 1,
			    'default' => '0',
		    ),
		    'field_modification' => array(
			    'type' => 'tinyint',
			    'constraint' => 1,
			    'default' => '0',
		    ),
		    'field_table' => array(
			    'type' => 'tinyint',
			    'constraint' => 1,
			    'default' => '0',
		    ),
		    'field_order' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('tf_id', TRUE);
	    $this->dbforge->add_key('type_id');
	    $this->dbforge->add_key('field_id');
	    $this->dbforge->create_table('w_shop_types_fields', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Производители
	    $this->dbforge->add_field(array(
		    'vendor_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'vendor_name' => array(
			    'type' => 'text',
		    ),
		    'vendor_active' => array(
			    'type' => 'tinyint',
			    'constraint' => 1,
			    'default' => '0',
		    ),
		    'vendor_sort' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('vendor_id', TRUE);
	    $this->dbforge->create_table('w_shop_vendors', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Теги
	    $this->dbforge->add_field(array(
		    'tag_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'tag_name' => array(
			    'type' => 'text',
		    ),
		    'tag_lang_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('tag_id', TRUE);
	    $this->dbforge->create_table('w_tags', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));

	    // Пересечения тегов и сущностей
	    $this->dbforge->add_field(array(
		    'tc_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
			    'unsigned' => TRUE,
			    'auto_increment' => TRUE
		    ),
		    'tag_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
		    'item_id' => array(
			    'type' => 'int',
			    'constraint' => 11,
		    ),
	    ));
	    $this->dbforge->add_key('tc_id', TRUE);
	    $this->dbforge->add_key('tag_id');
	    $this->dbforge->add_key('item_id');
	    $this->dbforge->create_table('w_tags_cross', FALSE, array('ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8'));
    }

    public function down()
    {
        $this->dbforge->drop_table('w_backgrounds');
        $this->dbforge->drop_table('w_banners');
        $this->dbforge->drop_table('w_changelog');
        $this->dbforge->drop_table('w_cms_configs');
        $this->dbforge->drop_table('w_cms_modules');
        $this->dbforge->drop_table('w_cms_pages');
        $this->dbforge->drop_table('w_galleries');
        $this->dbforge->drop_table('w_gallery_photos');
        $this->dbforge->drop_table('w_includes');
        $this->dbforge->drop_table('w_indexing_index');
        $this->dbforge->drop_table('w_indexing_link');
        $this->dbforge->drop_table('w_indexing_word');
        $this->dbforge->drop_table('w_news');
        $this->dbforge->drop_table('w_news_categories');
        $this->dbforge->drop_table('w_news_categories_cross');
        $this->dbforge->drop_table('w_pages');
        $this->dbforge->drop_table('w_pages_articles');
        $this->dbforge->drop_table('w_pages_cross_blocks');
        $this->dbforge->drop_table('w_sessions');
        $this->dbforge->drop_table('w_user');
        $this->dbforge->drop_table('w_user_rules');
	    $this->dbforge->drop_table('w_shop_categories');
	    $this->dbforge->drop_table('w_shop_fields');
	    $this->dbforge->drop_table('w_shop_items');
	    $this->dbforge->drop_table('w_shop_items_cats');
	    $this->dbforge->drop_table('w_shop_items_params');
	    $this->dbforge->drop_table('w_shop_types');
	    $this->dbforge->drop_table('w_shop_types_fields');
	    $this->dbforge->drop_table('w_shop_vendors');
	    $this->dbforge->drop_table('w_tags');
	    $this->dbforge->drop_table('w_tags_cross');
    }
}