<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Функции для обработки древовидных массивов
 */

class Tree {

	private $forest = array();
    private $top = 0;
    private $crumbs = array();

    /**
    * Преобразует одномерный массив, содержащий ключи родительских элементов, в многомерный
    * древовидный массив, отсортированный по эти ключам. Может быть полезно для генерации меню.
    *
    * @access   public
    * @param    string  - ключ массива, где хранится id элемента, напр. "page_id"
    * @param    string  - ключ массива, где хранится id род. элемента, напр. "page_parent_id"
    * @param    array   - входящий одномерный массив с данными, обычно это результат запроса к БД
    * @param    int     - номер ветки. Если 0 - все дерево, если не 0, то только ветки от этого id
    * @return   array
    */

    function get_tree($id, $pid, $result_array, $start=0){

        $tree = array();

        if (! is_array($result_array) ) return $tree;

        $nodes = array();
        $keys = array();

        foreach ($result_array as $node)
        {
            $nodes[$node[$id]] =& $node;
            $keys[] = $node[$id];
            unset($node);
        }

        foreach ($keys as $key)
        {
            if ($nodes[$key][$pid] == $start) $tree[] =& $nodes[$key];

            else
            {
                if (isset($nodes[ $nodes[$key][$pid] ]))
                {
                    if (! isset($nodes[ $nodes[$key][$pid] ]['nodes']))
                        $nodes[ $nodes[$key][$pid] ]['nodes'] = array();

                    $nodes[ $nodes[$key][$pid] ]['nodes'][] =& $nodes[$key];
                }
            }
        }

        return $tree;
    }

    // ------------------------------------------------------------------------

    /**
     * Присваиваем древовидный массив переменной класса, это требуется для
     * определения верхнего элемента и формирования массива хлебных крошек.
     *
     * @access  public
     * @param   array
     * @return  void
     */
    function set_tree ($forest)
    {
        $this->forest = $forest;
    }

    // ------------------------------------------------------------------------

    /**
     * Ищем верхний элемент
     *
     * @access  public
     * @param   array
     * @param   string
     * @param   string
     * @param   int
     * @return  void
     */
    function set_top ($forest, $id_name, $parent_name, $active_id)
    {
        if (is_array($forest))
        {
            foreach ($forest as $tree)
            {
                if ($tree[$id_name] == $active_id)
                {
                    if ($tree[$parent_name] != 0)
                    {
                        $this->set_top($this->forest, $id_name, $parent_name, $tree[$parent_name]);
                    }
                    else
                    {
                        $this->top = $tree[$id_name];
                    }
                }
                else
                {
                    if (isset($tree['nodes'])) $this->set_top($tree['nodes'], $id_name, $parent_name, $active_id);
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Возвращаем значение id верхнего элемента
     *
     * @access  public
     * @return  int
     */
    function get_top ()
    {
        return $this->top;
    }

    // ------------------------------------------------------------------------

    /**
     * Формируем массив из связанных страниц
     *
     * @access  public
     * @param   array
     * @param   string
     * @param   string
     * @param   int
     * @return  void
     */
    function set_crumbs ($forest, $id_name, $parent_name, $level_name, $url_name, $link = '/', $status_name, $status_value, $active_id)
    {
        if (is_array($forest))
        {
            foreach ($forest as $tree)
            {
                if ($tree[$id_name] == $active_id)
                {
                    $this->crumbs[$tree[$id_name]][$id_name] = $tree[$id_name];
                    $this->crumbs[$tree[$id_name]][$parent_name] = $tree[$parent_name];
                    $this->crumbs[$tree[$id_name]][$level_name] = $tree[$level_name];
                    $this->crumbs[$tree[$id_name]][$url_name] = $tree[$url_name];

                    if (isset($tree[$status_name]) && $tree[$status_name] == $status_value && isset($tree['nodes'][0][$id_name]))
                    {
                        $this->crumbs[$tree[$id_name]][$url_name] = $tree['nodes'][0][$url_name];
                    }
                    else
                    {
                        $this->crumbs[$tree[$id_name]][$id_name] = $tree[$id_name];
                    }

                    if ($tree[$parent_name] != 0) $this->set_crumbs($this->forest, $id_name, $parent_name, $level_name, $url_name, $link, $status_name, $status_value, $tree[$parent_name]);
                }
                else
                {
                    if(isset($tree['nodes'])) $this->set_crumbs($tree['nodes'], $id_name, $parent_name, $level_name, $url_name, $link, $status_name, $status_value, $active_id);
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Возвращаем значение id верхнего элемента
     *
     * @access  public
     * @return  array
     */
    function get_crumbs ()
    {
        return array_reverse($this->crumbs);
    }
}