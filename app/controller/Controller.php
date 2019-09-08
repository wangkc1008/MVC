<?php


namespace controller;


use framework\Template;

class Controller extends Template
{
    public function __construct()
    {
        $config = $GLOBALS['config'];
        parent::__construct($config['tpl_path'], $config['cache_path']);
    }

    public function display($viewName = null, $isInclude = true, $uri = null)
    {
        if (empty($viewName)) {
            $viewName = $_GET['m'] . '/' . $_GET['a'] . '.html';
        }
        parent::display($viewName, $isInclude, $uri);
    }
}