<?php

namespace framework;
class UploadFile
{
    //文件上传路径
    protected $path = './upload/';
    //允许文件上传后缀
    protected $allowSuffix = ['jpg', 'jpeg', 'png', 'gif', 'wbmp'];
    //允许文件上传MIME
    protected $allowMime = ['image/jpeg', 'image/gif', 'image/wbmp', 'image/png'];
    //允许文件上传大小
    protected $maxSize = 2000000;
    //是否启用随机文件名
    protected $isRandName = true;
    //文件上传前缀
    protected $prefix = 'up_';

    protected $errNumber;
    protected $errInfo;

    protected $oldName;
    protected $suffix;
    protected $mime;
    protected $size;
    protected $tmpName;

    protected $newName;

    public function __construct($arr = [])
    {
        foreach ($arr as $key => $value) {
            $this->setOption($key, $value);
        }
    }

    public function __get($name)
    {
        if ($name == 'errNumber') {
            return $this->errNumber;
        } elseif ($name == 'errInfo') {
            return $this->getErrInfo();
        }
        return false;
    }

    /**
     * @desc 获取错误信息
     * @return string
     */
    protected function getErrInfo()
    {
        switch ($this->errNumber) {
            case -1:
                $err = '文件路径未设置';
                break;
            case -2:
                $err = '文件上传路径不存在或路径不可写';
                break;
            case -3:
                $err = '文件太大';
                break;
            case -4:
                $err = '文件后缀错误';
                break;
            case -5:
                $err = '文件MIME类型错误';
                break;
            case -6:
                $err = '不是上传文件';
                break;
            case -7:
                $err = '文件移动失败';
                break;
            case 1:
                $err = '文件超出php.ini大小';
                break;
            case 2:
                $err = '文件超出html设置大小';
                break;
            case 3:
                $err = '文件部分上传';
                break;
            case 4:
                $err = '没有文件上传';
                break;
            case 5:
                $err = '找不到临时文件';
                break;
            case 6:
                $err = '文件写入失败';
                break;
            default:
                $err = '错误类型不存在';
                break;
        }
        return $err;
    }

    /**
     * @desc 设置成员属性
     * @param $key
     * @param $value
     */
    protected function setOption($key, $value)
    {
        $keys = array_keys(get_class_vars(__CLASS__));
        if (in_array($key, $keys)) {
            $this->$key = $value;
        }
    }

    /**
     * @desc 检查文件上传路径是否存在 是否可写
     * @return bool
     */
    protected function check()
    {
        //判断文件路径是否存在
        if (!file_exists($this->path) || !is_dir($this->path)) {
            //递归创建目录
            return mkdir($this->path, 0777, true);
        }
        //判断文件是否可写
        if (!is_writeable($this->path)) {
            return chmod($this->path, 0777);
        }
        return true;
    }

    /**
     * @desc 获取上传文件信息
     * @param $key
     */
    protected function getFileInfo($key)
    {
        $this->oldName = $_FILES[$key]['name'];
        $this->mime = $_FILES[$key]['type'];
        $this->size = $_FILES[$key]['size'];
        $this->tmpName = $_FILES[$key]['tmp_name'];
        $this->suffix = pathinfo($this->oldName)['extension'];
    }

    /**
     * @desc 检查上传文件大小
     * @return bool
     */
    protected function checkSize() {
        if ($this->size > $this->maxSize) {
            $this->setOption('errNumber', -3);
            return false;
        }
        return true;
    }

    /**
     * @desc 检查上传文件后缀
     * @return bool
     */
    protected function checkSuffix()
    {
        if (!in_array($this->suffix, $this->allowSuffix)) {
            $this->setOption('errNumber', -4);
            return false;
        }
        return true;
    }

    /**
     * @desc 检查上传文件后缀
     * @return bool
     */
    protected function checkMime()
    {
        if (!in_array($this->mime, $this->allowMime)) {
            $this->setOption('errNumber', -5);
            return false;
        }
        return true;
    }

    /**
     * @desc 设置新文件名
     * @return string
     */
    protected function createNewName()
    {
        if ($this->isRandName) {
            $name = $this->prefix . uniqid() . '.' . $this->suffix;
        } else {
            $name = $this->prefix . $this->oldName;
        }
        return $name;
    }

    /**
     * @desc 文件上传核心类
     * @param $key
     * @return bool|string
     */
    public function upload($key)
    {
        //判断是否设置路径
        if (empty($this->path)) {
            $this->setOption('errNumber', -1);
            return false;
        }
        //判断上传路径是否存在 是否可写
        if (!$this->check()) {
            $this->setOption('errNumber', -2);
            return false;
        }
        //判断上传文件的错误信息是否为0
        $error = $_FILES[$key]['error'];
        if ($error) {
            $this->setOption('errNumber', $error);
            return false;
        } else {
            $this->getFileInfo($key);
        }
        //检查上传文件的mime 后缀 大小
        if (!$this->checkSize() || !$this->checkSuffix() || !$this->checkMime()) {
            return false;
        }
        //得到新的文件名字
        $this->newName = $this->createNewName();
        //判断是否上传文件 移动文件
        if (!is_uploaded_file($this->tmpName)) {
            $this->setOption('errNumber', -6);
            return false;
        }

        if (!move_uploaded_file($this->tmpName, $this->path . $this->newName)) {
            $this->setOption('errNumber', -7);
            return false;
        }

        return $this->path . $this->newName;
    }
}