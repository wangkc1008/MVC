<?php

namespace framework;
class Image
{
    protected $path;
    protected $isRandom;
    protected $type;

    public function __construct($path = './', $isRandom = true, $type = 'png')
    {
        $this->path = $path;
        $this->isRandom = $isRandom;
        $this->type = $type;
    }

    public function water($image, $water, $position, $trans = 100, $prefix = 'water_')
    {
        //1.判断图片是否存在
        if (!file_exists($image) || !file_exists($water)) {
            exit('原图片或水印图片不存在');
        }
        //2.获取原图片和水印图片的宽度和高度
        $resInfo = self::getImageInfo($image);
        $waterInfo = self::getImageInfo($water);
        //3.判断水印图片是否可以贴上来
        if (!$this->checkImage($resInfo, $waterInfo)) {
            exit('水印图片太大');
        }
        //4.打开图片
        $resImage = self::openAnyImage($image);
        $waterImage = self::openAnyImage($water);
        //5.根据水印图片的宽高计算水印图片的坐标
        $waterPosition = $this->getWaterPosition($resInfo, $waterInfo, $position);
        //6.将水印图片贴上来
        imagecopymerge($resImage, $waterImage, $waterPosition['x'], $waterPosition['y'], 0, 0, $waterInfo['width'], $waterInfo['height'], $trans);
        //7.得到处理后的图片名
        $newName = $this->createNewName($image, $prefix);
        //8.得到处理后的图片路径
        $newPath = rtrim($this->path, '/') . '/' . $newName;
        //9.保存图片
        $this->saveImage($resImage, $newPath);
        //10.销毁图片资源
        imagedestroy($resImage);
        imagedestroy($waterImage);
    }

    public static function getImageInfo($imagePath)
    {
        $res = getimagesize($imagePath);
        $imageInfo['width'] = $res[0];
        $imageInfo['height'] = $res[1];
        $imageInfo['mime'] = $res['mime'];
        return $imageInfo;
    }

    protected function checkImage($resImage, $waterImage)
    {
        if ($waterImage['width'] > $resImage['width'] || $waterImage['height'] > $resImage['height']) {
            return false;
        }
        return true;
    }

    public static function openAnyImage($imagePath)
    {
        $imageMime = self::getImageInfo($imagePath)['mime'];
        switch ($imageMime) {
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
                break;
            case 'image/wbmp':
                $image = imagecreatefromwbmp($imagePath);
                break;
            default:
                $image = '请添加该类型对应的图片打开方式';
        }
        return $image;
    }

    protected function getWaterPosition($resInfo, $waterInfo, $position)
    {
        switch ($position) {
            case 1:
                $x = 0;
                $y = 0;
                break;
            case 2:
                $x = ($resInfo['width'] - $waterInfo['width']) / 2;
                $y = 0;
                break;
            case 3:
                $x = $resInfo['width'] - $waterInfo['width'];
                $y = 0;
                break;
            case 4:
                $x = 0;
                $y = ($resInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 5:
                $x = ($resInfo['width'] - $waterInfo['width']) / 2;
                $y = ($resInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 6:
                $x = $resInfo['width'] - $waterInfo['width'];
                $y =($resInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 7:
                $x = 0;
                $y = $resInfo['height'] - $waterInfo['height'];
                break;
            case 8:
                $x = ($resInfo['width'] - $waterInfo['width']) / 2;
                $y = $resInfo['height'] - $waterInfo['height'];
                break;
            case 9:
                $x = $resInfo['width'] - $waterInfo['width'];
                $y = $resInfo['height'] - $waterInfo['height'];
                break;
            case 0:
                $x = mt_rand(0, $resInfo['width'] - $waterInfo['width']);
                $y = mt_rand(0, $resInfo['height'] - $waterInfo['height']);
                break;
            default:
                exit('位置参数错误');
        }
        return array('x' => $x, 'y' => $y);
    }

    protected function createNewName($imagePath, $prefix)
    {
        if ($this->isRandom) {
            $name = $prefix . uniqid() . '.' . $this->type;
        } else {
            $filename = pathinfo($imagePath)['filename'];
            $name = $prefix . $filename . '.' . $this->type;
        }
        return $name;
    }

    protected function saveImage($resImage, $newPath)
    {
        $func = 'image' . $this->type;
        $func($resImage, $newPath);
    }

    public function scaling($image, $width, $height, $prefix = 'scale_')
    {
        if (!file_exists($image)) {
            exit('图片资源不存在');
        }
        //得到图片原来的宽高
        $imageInfo = self::getImageInfo($image);
        //得到图片不变形的宽高
        $size = $this->getNewSize($width, $height, $imageInfo);
        //打开图片
        $resImage = self::openAnyImage($image);
        //进行缩放
        $scaleImage = $this->kidOfImage($resImage, $size, $imageInfo);
        //得到新图片名
        $newName = $this->createNewName($image, $prefix);
        //得到保存路径
        $newPath = rtrim($this->path, '/') . '/' . $newName;
        //保存图片
        $this->saveImage($scaleImage, $newPath);
        //销毁资源
        imagedestroy($resImage);
        imagedestroy($scaleImage);
    }

    protected function getNewSize($width, $height, $imageInfo)
    {
        $size['old_w'] = $width;
        $size['old_h'] = $height;
        $scaleWidth = $width / $imageInfo['width'];
        $scaleHeight = $height / $imageInfo['height'];
        $scaleFinal = min($scaleWidth, $scaleHeight);

        $size['new_w'] = round($imageInfo['width'] * $scaleFinal);
        $size['new_h'] = round($imageInfo['height'] * $scaleFinal);

        if ($scaleWidth < $scaleHeight) {
            $size['x'] = 0;
            $size['y'] = round(abs($size['new_h'] - $height) / 2);
        } else {
            $size['x'] = round(abs($size['new_w'] - $width) / 2);
            $size['y'] = 0;
        }
        return $size;
    }

    protected function kidOfImage($srcImage, $size, $imageInfo)
    {
        //创建图片 新尺寸
        $newImage = imagecreatetruecolor($size['old_w'], $size['old_h']);
        //定义透明色
        $otsc = imagecolortransparent($srcImage);
        if ($otsc > 0) {
            //取得透明色
            $transparentColor = imagecolorsforindex($srcImage, $otsc);
            //创建透明色
            $newTransparentColor = imagecolorallocate(
                $newImage,
                $transparentColor['red'],
                $transparentColor['green'],
                $transparentColor['blue']
            );
        } else {
            //将黑色作为透明色
            $newTransparentColor = imagecolorallocate($newImage, 0, 0, 0);
        }
        //背景填充透明
        imagefill($newImage, 0, 0, $newTransparentColor);
        imagecolortransparent($newImage, $newTransparentColor);
        imagecopyresampled($newImage, $srcImage, $size['x'], $size['y'], 0, 0, $size['new_w'], $size['new_h'], $imageInfo['width'], $imageInfo['height']);
        return $newImage;
    }
}