<?php

class Start{
    public static $auto;

    public static function init()
    {
        self::$auto = new Psr4Autoload();
    }

    public static function router()
    {
        $m = isset($_GET['m']) ? $_GET['m'] : 'index';
        $a = isset($_GET['a']) ? $_GET['a'] : 'index';

        $_GET['m'] = $m;
        $_GET['a'] = $a;

        $m = ucfirst(strtolower($m));
        $controller = 'controller\\' . $m . 'Controller';

        $obj = new $controller();
        call_user_func([$obj, $a]);
    }
}
Start::init();