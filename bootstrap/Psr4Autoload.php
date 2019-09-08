<?php


class Psr4Autoload {

    protected $maps = [];

    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);
    }

    public function autoload($className)
    {
        $pos = strrpos($className, '\\');
        $namespace = substr($className, 0, $pos);
        $realClass = substr($className, $pos + 1);
        $this->mapLoad($namespace, $realClass);
    }

    public function mapLoad($namespace, $realClass)
    {
        if (array_key_exists($namespace, $this->maps)) {
            $namespace = $this->maps[$namespace];
        }

        $namespace = rtrim(str_replace('\\/', '/', $namespace), '/') . '/';
        $filePath = $namespace . $realClass . '.php';
        if (file_exists($filePath)) {
            include $filePath;
        } else {
            exit($filePath . '文件不存在');
        }
    }

    public function addMaps($namespace, $path)
    {
        if (array_key_exists($namespace, $this->maps)) {
            exit($namespace . '命名空间已经被映射过');
        }
        $this->maps[$namespace] = $path;
    }
}