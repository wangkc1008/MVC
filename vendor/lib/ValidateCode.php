<?php

namespace framework;
class ValidateCode
{
    //验证码个数
    protected $number;
    //验证码类型
    protected $codeType;
    //验证码图像高度
    protected $height;
    //验证码图像宽度
    protected $width;
    //验证码图像
    protected $image;
    //验证码字符串
    protected $code;

    public function __construct($number = 4, $codeType = 2, $width = 100, $height = 40)
    {
        $this->number = $number;
        $this->codeType = $codeType;
        $this->height = $height;
        $this->width = $width;

        $this->code = $this->createCode();
    }

    /**
     * @desc 允许外部访问code属性
     * @param $field
     * @return bool|string
     */
    public function __get($field)
    {
        if ($field == 'code') {
            return $this->code;
        }
        return false;
    }

    /**
     * @desc 销毁图像
     */
    public function __destruct()
    {
        imagedestroy($this->image);
    }

    /**
     * @desc 生成验证码
     * @return bool|string
     */
    protected function createCode()
    {
        switch ($this->codeType) {
            case 1:
                $code = $this->createNumCode();
                break;
            case 2:
                $code = $this->createStrCode();
                break;
            case 3:
                $code = $this->createNumStrCode();
                break;
            default:
                die('验证码类型错误');
        }
        return $code;
    }

    /**
     * @desc 生成数字验证码
     * @return bool|string
     */
    protected function createNumCode()
    {
        $str = join('', range(0, 9));
        return substr(str_shuffle($str), 0, $this->number);
    }

    /**
     * @desc 生成字符验证码
     * @return bool|string
     */
    protected function createStrCode()
    {
        $str = join('', range('a', 'z'));
        $str = $str . strtoupper($str);
        return substr(str_shuffle($str), 0, $this->number);
    }

    /**
     * @desc 生成数字字符混合验证码
     * @return bool|string
     */
    protected function createNumStrCode()
    {
        $numStr = join('', range(0, 9));
        $str = join('', range('a', 'z'));
        $str = $numStr . $str . strtoupper($str);
        return substr(str_shuffle($str), 0, $this->number);
    }

    /**
     * @desc 创建画布
     */
    protected function createImage()
    {
        $this->image = imagecreatetruecolor($this->width, $this->height);
    }

    protected function fillBack()
    {
        imagefill($this->image, 0, 0, $this->lightColor());
    }

    /**
     * @desc 生成浅色系的颜色
     * @return false|int
     */
    protected function lightColor()
    {
        return imagecolorallocate($this->image, mt_rand(130, 255), mt_rand(130, 255), mt_rand(130, 255));
    }

    /**
     * @desc 生成深色系的颜色
     * @return false|int
     */
    protected function darkColor()
    {
        return imagecolorallocate($this->image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
    }

    /**
     * @desc 在图像中添加字符串
     */
    protected function drawChar()
    {
        $width = ceil($this->width / $this->number);
        for ($i = 0; $i < $this->number; $i++) {
            $x = mt_rand($width * $i + 10, $width * ($i + 1) - 10);
            $y = mt_rand(0, $this->height - 15);
            imagechar($this->image, 5, $x, $y, $this->code[$i], $this->darkColor());
        }
    }

    /**
     * @desc 在图像中添加干扰
     */
    protected function drawDisturb()
    {
        for ($i = 0; $i < 150; $i++) {
            $x = mt_rand(0, $this->width);
            $y = mt_rand(0, $this->height);
            imagesetpixel($this->image, $x, $y, $this->lightColor());
        }
    }

    /**
     * @desc 输出图像
     */
    protected function show()
    {
        header('Content-Type:image/png');
        imagepng($this->image);
    }

    /**
     * @desc 外部调用
     */
    public function outImage()
    {
        //创建画布
        $this->createImage();
        //填充背景色
        $this->fillBack();
        //将验证码字符串加入图片
        $this->drawChar();
        //加入干扰
        $this->drawDisturb();
        //输出显示图像
        $this->show();
    }

}