<?php

namespace framework;

class Template
{
    //模板文件的路径
    protected $viewDir = './view/';
    //缓存文件的路径
    protected $cacheDir = './cache/';
    //过期时间
    protected $expireTime = 3600;
    //存放显示变量的数组
    protected $option = [];

    public function __construct($viewDir = null, $cacheDir = null, $expireTime = null)
    {
        if (!empty($viewDir)) {
            if ($this->checkDir($viewDir)) {
                $this->viewDir = $viewDir;
            }
        }
        if (!empty($cacheDir)) {
            if ($this->checkDir($cacheDir)) {
                $this->cacheDir = $cacheDir;
            }
        }
        if (!empty($expireTime) && ctype_digit($expireTime)) {
            $this->expireTime = $expireTime;
        }
    }

    /**
     * @desc 检查文件
     * @param $dirPath
     * @return bool
     */
    public function checkDir($dirPath)
    {
        if (!file_exists($dirPath) || !is_dir($dirPath)) {
            return mkdir($dirPath, 0755, true);
        }
        if (!is_writable($dirPath) || !is_readable($dirPath)) {
            return chmod($dirPath, 0755);
        }
        return true;
    }

    //分配变量方法
    public function assign($name, $value)
    {
        $this->option[$name]  = $value;
    }

    //显示方法
    public function display($viewName, $isInclude = true, $uri = null)
    {
        //拼接模板文件的全路径
        $viewPath = rtrim($this->viewDir, '/') . '/' . $viewName;
        if (!file_exists($viewPath)) {
            exit('模板文件不存在');
        }
        //拼接缓存文件的全路径
        $cacheName = md5($viewName . $uri) . '.php';
        $cachePath = rtrim($this->cacheDir, '/') . '/' . $cacheName;
        //判断缓存文件是否存在
        if (!file_exists($cachePath)) {
            //不存在 编译生成缓存文件
            $php = $this->compile($viewPath);
            file_put_contents($cachePath, $php);
        } else {
            //存在 判断缓存文件是否过期 过期重新生成
            $isTimeOut = (filectime($cachePath) + $this->expireTime < time()) ? false : true;
            //判断缓存文件是否被修改过 修改过则重新生成
            $isChange = (filemtime($viewPath) > filemtime($cachePath)) ? false : true;
            if (!$isTimeOut || !$isChange) {
                $php = $this->compile($viewPath);
                file_put_contents($cachePath, $php);
            }
        }
        //判断缓存文件是否需要包含进来
        if ($isInclude) {
            extract($this->option);
            include $cachePath;
        }
    }

    protected function compile($viewPath)
    {
        $html = file_get_contents($viewPath);
        $patternArray = [
            '{$%%}' => '<?php echo $\1;?>',
            '{foreach %%}' => '<?php foreach (\1): ?>',
            '{/foreach}' => '<?php endforeach ?>',
            '{include %%}' => '',
            '{if %%}' => '<?php if (\1): ?>',
            '{/if}' => '<?php endif ?>'
        ];
        foreach ($patternArray as $key => $value) {
            $pattern = '#' . str_replace('%%', '(.+?)', preg_quote($key, '#')) . '#';
            if (strstr($pattern, 'include')) {
                $html = preg_replace_callback($pattern, [$this, 'parseInclude'], $html);
            } else {
                $html = preg_replace($pattern, $value, $html);
            }
        }
        return $html;
    }

    protected function parseInclude($data)
    {
        //将文件名两边的引号去掉
        $fileName = trim($data[1], '\'"');
        //不包含文件生成缓存
        $this->display($fileName, false);
        $cacheName = md5($fileName) . '.php';
        $cachePath = rtrim($this->cacheDir, '/') . '/' . $cacheName;
        return '<?php include "' . $cachePath . '"; ?>';
    }
}