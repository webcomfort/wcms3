<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Возвращает стандартное ul-li меню
*
* @access	public
* @param    array   - входящий древовидный массив, полученный из функции get_tree
* @param    string  - ключ массива, где хранится id элемента, напр. "page_id"
* @param    string  - ключ массива, где хранится id род. элемента, напр. "page_parent_id"
* @param    string  - ключ массива, где хранится имя элемента, напр. "page_name"
* @param    string  - ключ массива, где хранится ссылка, напр. "page_url", обычно это транслит имени
* @param    string  - ключ массива, где хранится title для ссылки
* @param    string  - начало ссылки, если не указано, то начнет с '/', напр. '/news/'
* @param    string  - ключ массива, где хранится статус элемента, напр. "page_status" - для перехода на уровень ниже
* @param    string  - значение статуса, которое обозначает переход на уровень ниже
* @param    int     - id активного элемента
* @param    string  - класс для активного <li>
* @param    string  - класс для <ul>
* @param    string  - сгенерированный html
* @return	string
*/
if ( ! function_exists('get_ul_menu'))
{
	function get_ul_menu ($forest, $id_name, $parent_name, $level_name, $url_name, $link_title, $link = '/', $status_name, $status_value, $active_id, $css = 'active', $ul_css = false, $menu = '')
	{
        if ($ul_css) $menu .= '<ul class="'.$ul_css.'">';
        else $menu .= '<ul>';

		foreach ($forest as $tree)
		{
			$menu .= '<li';

			if (isset($tree[$status_name]) && $tree[$status_name] == $status_value && isset($tree['nodes'][0][$id_name]))
            {
                $tree[$url_name] = $tree['nodes'][0][$url_name];
            }

            if (isset($tree[$status_name]) && $tree[$status_name] == 4)
            {
                $tree[$url_name] = '';
            }
			
			if ((isset ($link_title) && $link_title == '') || (isset ($tree[$link_title]) && $tree[$link_title] == '')) $tree[$link_title] = $tree[$level_name];

            if ($tree[$id_name] == $active_id)
            {
                $menu .= ' class="'.$css.'"';
                $href = '';
            }
            else
            {
                $href = ' href="'.$link.$tree[$url_name].'"';
            }

			$menu .= '><a'.$href.' title="'.@$tree[$link_title].'">'.$tree[$level_name].'</a>';

			if (isset($tree['nodes'])) $menu = get_ul_menu($tree['nodes'], $id_name, $parent_name, $level_name, $url_name, $link_title, $link, $status_name, $status_value, $active_id, $css, $ul_css, $menu);

			$menu .= '</li>';
		}

		$menu .= '</ul>';

		return $menu;
	}
}

// ------------------------------------------------------------------------

/**
* Возвращает стандартное ul-li меню только для страниц верхнего уровня
*
* @access   public
* @param    array   - входящий древовидный массив, полученный из функции get_tree
* @param    string  - ключ массива, где хранится id элемента, напр. "page_id"
* @param    string  - ключ массива, где хранится id род. элемента, напр. "page_parent_id"
* @param    string  - ключ массива, где хранится имя элемента, напр. "page_name"
* @param    string  - ключ массива, где хранится ссылка, напр. "page_url", обычно это транслит имени
* @param    string  - ключ массива, где хранится title для ссылки
* @param    string  - начало ссылки, если не указано, то начнет с '/', напр. '/news/'
* @param    string  - ключ массива, где хранится статус элемента, напр. "page_status" - для перехода на уровень ниже
* @param    string  - значение статуса, которое обозначает переход на уровень ниже
* @param    int     - id активного элемента
* @param    string  - класс для активного <li>
* @param    string  - сгенерированный html
* @return   string
*/
if ( ! function_exists('get_top_menu'))
{
    function get_top_menu ($forest, $id_name, $parent_name, $level_name, $url_name, $link_title, $link = '/', $status_name, $status_value, $active_id, $css = 'active', $menu = '')
    {
        $menu .= '<ul>';

        foreach ($forest as $tree)
        {
            $menu .= '<li';

            if (isset($tree[$status_name]) && $tree[$status_name] == $status_value && isset($tree['nodes'][0][$id_name]))
            {
                $tree[$url_name] = $tree['nodes'][0][$url_name];
            }

            if (isset($tree[$status_name]) && $tree[$status_name] == 4)
            {
                $tree[$url_name] = '';
            }
			
			if ((isset ($link_title) && $link_title == '') || (isset ($tree[$link_title]) && $tree[$link_title] == '')) $tree[$link_title] = $tree[$level_name];

            if ($tree[$id_name] == $active_id)
            {
                $menu .= ' class="'.$css.'"';
                $href = '';
            }
            else
            {
                $href = ' href="'.$link.$tree[$url_name].'"';
            }

            $menu .= '><a'.$href.' title="'.$tree[$link_title].'">'.$tree[$level_name].'</a>';

            $menu .= '</li>';
        }

        $menu .= '</ul>';

        return $menu;
    }
}

// ------------------------------------------------------------------------

/**
* Возвращает bootstrap ul-li меню
*
* @access	public
* @param    array   - входящий древовидный массив, полученный из функции get_tree
* @param    string  - ключ массива, где хранится id элемента, напр. "page_id"
* @param    string  - ключ массива, где хранится id род. элемента, напр. "page_parent_id"
* @param    string  - ключ массива, где хранится имя элемента, напр. "page_name"
* @param    string  - ключ массива, где хранится ссылка, напр. "page_url", обычно это транслит имени
* @param    string  - ключ массива, где хранится title для ссылки
* @param    string  - начало ссылки, если не указано, то начнет с '/', напр. '/news/'
* @param    string  - ключ массива, где хранится статус элемента, напр. "page_status" - для перехода на уровень ниже
* @param    string  - значение статуса, которое обозначает переход на уровень ниже
* @param    int     - id активного элемента
* @param    string  - класс для активного <li>
* @param    string  - сгенерированный html
* @param    int     - счетчик
* @param    array   - массив разрешенных id
* @return	string
*/
if ( ! function_exists('get_bootstrap_menu'))
{
	function get_bootstrap_menu ($forest, $id_name, $parent_name, $level_name, $url_name, $link_title, $link = '/', $status_name, $status_value, $active_id, $css = 'active', $menu = '', $i = 0, $valid = false)
	{
		if ($i == 0) $menu .= '<ul class="nav navbar-nav">';
        if ($i != 0) $menu .= '<ul class="dropdown-menu">';

		foreach ($forest as $tree)
		{
			if ($valid === false || (is_array($valid) && in_array($tree[$id_name], $valid)))
            {
                $menu .= '<li class="';

                if ($tree[$id_name] == $active_id) $menu .= $css.' ';
                if (isset($tree['nodes'][0][$id_name]) && $i == 0) $menu .= 'dropdown ';
                if (isset($tree['nodes'][0][$id_name]) && $i != 0) $menu .= 'dropdown-submenu ';
				if ((isset ($link_title) && $link_title == '') || (isset ($tree[$link_title]) && $tree[$link_title] == '')) $tree[$link_title] = $tree[$level_name];

                $menu .= '"';
				
				if (isset($tree[$status_name]) && $tree[$status_name] == 4)
				{
					$tree[$url_name] = '';
				}

                if (isset($tree[$status_name]) && $tree[$status_name] == $status_value && isset($tree['nodes'][0][$id_name]))
                {
                    $menu .= '><a href="#" title="'.$tree[$link_title].'"';
                }
                else
                {
                    if($tree[$url_name] == 'index') $menu .= '><a href="/" title="'.$tree[$level_name].'"';
					else $menu .= '><a href="'.$link.$tree[$url_name].'" title="'.$tree[$level_name].'"';
                }
                if (isset($tree['nodes'][0][$id_name]) && $i == 0) $menu .= ' class="dropdown-toggle" data-toggle="dropdown"';
                if (isset($tree['nodes'][0][$id_name]) && $i != 0) $menu .= ' tabindex="-1"';
                $menu .= '>'.$tree[$level_name];
                if (isset($tree['nodes'][0][$id_name]) && $i == 0) $menu .= ' <b class="caret"></b>';
                $menu .= '</a>';

                if (isset($tree['nodes'])) $menu = get_bootstrap_menu($tree['nodes'], $id_name, $parent_name, $level_name, $url_name, $link_title, $link, $status_name, $status_value, $active_id, $css, $menu, $i+1);

                $menu .= '</li>';
            }
		}

		$menu .= '</ul>';

        return $menu;
	}
}

/**
 * Возвращает bootstrap4 ul-li меню
 *
 * @access	public
 * @param    array   - входящий древовидный массив, полученный из функции get_tree
 * @param    string  - ключ массива, где хранится id элемента, напр. "page_id"
 * @param    string  - ключ массива, где хранится id род. элемента, напр. "page_parent_id"
 * @param    string  - ключ массива, где хранится имя элемента, напр. "page_name"
 * @param    string  - ключ массива, где хранится ссылка, напр. "page_url", обычно это транслит имени
 * @param    string  - ключ массива, где хранится title для ссылки
 * @param    string  - начало ссылки, если не указано, то начнет с '/', напр. '/news/'
 * @param    string  - ключ массива, где хранится статус элемента, напр. "page_status" - для перехода на уровень ниже
 * @param    string  - значение статуса, которое обозначает переход на уровень ниже
 * @param    int     - id активного элемента
 * @param    string  - класс для активного <li>
 * @param    string  - сгенерированный html
 * @param    int     - счетчик
 * @param    array   - массив разрешенных id
 * @return	string
 */
if ( ! function_exists('get_bootstrap4_menu'))
{
	function get_bootstrap4_menu ($forest, $id_name, $parent_name, $level_name, $url_name, $link_title, $link = '/', $status_name, $status_value, $active_id, $css = 'active', $menu = '', $i = 0, $valid = false)
	{
		$j = 1;
		if ($i == 0) $menu .= '<ul class="navbar-nav mr-auto">';
		if ($i != 0) $menu .= '<div class="dropdown-menu" aria-labelledby="dropdown'.$j.'">';

		foreach ($forest as $tree)
		{
			if ($valid === false || (is_array($valid) && in_array($tree[$id_name], $valid)))
			{
				if ( ( isset ( $link_title ) && $link_title == '' ) || ( isset ( $tree[ $link_title ] ) && $tree[ $link_title ] == '' ) ) {
					$tree[ $link_title ] = $tree[ $level_name ];
				}
				if (isset($tree[$status_name]) && $tree[$status_name] == 4)
				{
					$tree[$url_name] = '';
				}

				// li
				if ($i == 0) {
					$menu .= '<li class="nav-item';
					if ( $tree[ $id_name ] == $active_id ) {
						$menu .= ' ' . $css;
					}
					if ( isset( $tree['nodes'][0][ $id_name ] ) ) {
						$menu .= ' dropdown';
					}
					$menu .= '">';
				}

				// a
				$menu .= '<a href="';
				if (isset($tree[$status_name]) && $tree[$status_name] == $status_value && isset($tree['nodes'][0][$id_name]))
				{
					$menu .= '#';
				}
				else
				{
					if($tree[$url_name] == 'index') $menu .= '/';
					else $menu .= $link.$tree[$url_name];
				}
				$menu .= '"';
				$menu .= ' title="'.$tree[$link_title].'"';
				$menu .= ' class="';
				if ($i == 0) { $menu .= 'nav-link'; }
				else { $menu .= 'dropdown-item'; }
				if (isset($tree['nodes'][0][$id_name]) && $i == 0) { $menu .= ' dropdown-toggle'; }
				$menu .= '"';
				if (isset($tree['nodes'][0][$id_name]) && $i == 0) { $menu .= ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="dropdown'.$j.'"'; }
				$menu .= '>';
				$menu .= $tree[$level_name];
				$menu .= '</a>';

				if (isset($tree['nodes']) && $i == 0) $menu = get_bootstrap4_menu($tree['nodes'], $id_name, $parent_name, $level_name, $url_name, $link_title, $link, $status_name, $status_value, $active_id, $css, $menu, $i+1);

				if ($i == 0) $menu .= '</li>';
			}
			$j++;
		}

		if ($i != 0) $menu .= '</div>';
		if ($i == 0) $menu .= '</ul>';

		return $menu;
	}
}