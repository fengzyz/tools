<?php
/**
 * ImageTools.php
 *
 * @author fengzyz
 * 
 */

namespace Fengzyz\Image;

/**
 * Class ImageTools
 * @package Fengzyz\Image
 */
class ImageTools
{
    /**
     * [合併兩張圖片]
     * @param $source1 [图片一，大图]
     * @param $source2 [图片二，小图]
     * @param null $saveName [图片合成后输出路劲]
     * @param int $alpha [透明度]
     * @param int $position [偏移九宫格位置1,2,3,4,5,6,7,8,9]
     * @param null $posX [小图偏移位置]
     * @param null $posY [小图偏移位置]
     * @return bool
     */
    public static  function combine($source1, $source2, $saveName = null, $alpha = 80,$position=0,$posX=null, $posY=null) {
        //检查文件是否存在
        //如果是远程图片
        if (!self::isRemoteImage($source1) && !file_exists($source1)) {
            throw new \RuntimeException('source1 is not exist');
        }

        if (!self::isRemoteImage($source2) && !file_exists($source2)) {
            throw new \RuntimeException('source2 is not exist');
        }

        //图片信息
        $sInfo = self::getImageInfo($source1);
        $s2Info = self::getImageInfo($source2);

        if(empty($sInfo) || empty($s2Info)) {
            return false;
        }

        //如果图片1小于图片2，不生成图片
        if ($sInfo["width"] < $s2Info["width"] || $sInfo['height'] < $s2Info['height']) {
            return false;
        }

        //建立图像
        $sCreateFun = "imagecreatefrom" . $sInfo['type'];
        $sImage = $sCreateFun($source1);
        $s2CreateFun = "imagecreatefrom" . $s2Info['type'];
        $s2Image = $s2CreateFun($source2);


        imagepalettetotruecolor($s2Image);
        imagesavealpha($s2Image, true);

        //设定图像的混色模式
        imagealphablending($sImage, true);

        //图像位置,默认为右下角右对齐
        $positionInfo = static::getOffsetPosition($position,$sInfo,$s2Info);
        if(empty($posX)) {
            $posX = $positionInfo['posX'];
        }
        if(empty($posY)) {
            $posY = $positionInfo['posY'];
        }

        $s2Width = $s2Info['width'];
        $s2Height = $s2Info['height'];

        //生成混合图像
        //imagecopymerge($sImage, $s2Image, $posX, $posY, 0, 0, $s2Info['width'], $s2Info['height'], $alpha);
        imagecopyresampled($sImage, $s2Image, $posX, $posY, 0, 0, $s2Width, $s2Height,$s2Width, $s2Height);
        //输出图像
        $ImageFun = 'Image' . $sInfo['type'];
        //如果没有给出保存文件名，默认为原图像名
        if (!$saveName) {
            $saveName = $source1;
            @unlink($source1);
        }

        //保存图像
        $ImageFun($sImage, $saveName);
        imagedestroy($sImage);
        imagedestroy($s2Image);

        return true;
    }

    /**
     * 远程图片
     * @param $source
     * @return bool
     */
    public static function isRemoteImage($source)
    {
        return strpos($source,'http') === false ? false : true;
    }

    /**
     * 根据九宫格获取合并图片位置
     * @param $position [偏移九宫格位置1,2,3,4,5,6,7,8,9]
     * @param $bigImg [大图信息]
     * @param $smlImg [小图信息]
     * @return bool
     */
    public static function getOffsetPosition($position,$bigImg,$smlImg)
    {
        if(empty($bigImg) || empty($smlImg)) {
            return false;
        }

        $bigHeight = $bigImg['height'];
        $bigWidth  = $bigImg['width'];
        $smlHeight = $smlImg['height'];
        $smlWidth  = $smlImg['width'];

        $halfBigHeight = $bigHeight/2;
        $halfBigWidth = $bigWidth/2;
        $halfSmlHeight = $smlHeight/2;
        $halfSmlWidth = $smlWidth/2;

        $posX = 0;
        $posY = 0;

        switch ($position){
            case 1:
                break;
            case 2:
                $posX = $halfBigWidth - $halfSmlWidth;
                break;
            case 3:
                $posX = $bigWidth - $smlWidth;
                break;
            case 4:
            case 5:
            case 6:
                if($position==5) {
                    $posX = $halfBigWidth - $halfSmlWidth;
                } else if($position==6) {
                    $posX = $bigWidth - $smlWidth;
                }
                $posY = $halfBigHeight - $halfSmlHeight;
                break;
            case 7:
            case 8:
            case 9:
                if($position==8) {
                    $posX = $halfBigWidth - $halfSmlWidth;
                } else if($position==9) {
                    $posX = $bigWidth - $smlWidth;
                }
                $posY = $bigHeight - $smlHeight;
                break;
            default:
                $posX = $bigWidth - $smlWidth;
                $posY = $bigHeight - $smlHeight;
                break;
        }

        return compact('posX','posY');
    }

    /**
     * 取得图像信息
     * @param $img [图像文件名]
     * @return array|bool
     */
    public static function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 获取图片对象
     * @param $source [图片源]
     * @param null $sourceInfo [图片信息]
     * @return bool
     */
    public static function getImageObj($source, &$sourceInfo = null)
    {
        $info = getimagesize($source);
        if(false === $info) {
            return false;
        }

        $sourceInfo = $info;
        $imageType = strtolower(substr(image_type_to_extension($info[2]), 1));
        $sourceInfo['type'] = $imageType;

        $img = null;

        switch($info[2]) {
            case 1: // gif
                $img = imagecreatefromgif($source);
                break;
            case 2: // jpeg
                $img = imagecreatefromjpeg($source);
                break;
            case 3: // png
                $img = imagecreatefrompng($source);
                break;
            case 6: // bmp
                //$img = self::readbmp($source);
                break;
            default:
                return false;
        }
        return $img;
    }

    /**
     * 合并文字
     * @param $source [图片源]
     * @param $word [文字]
     * @param null $posX [文字位置]
     * @param null $posY [文字位置]
     * @param bool $isCenter [文字是否居中]
     * @param string $fontColor [文字颜色]
     * @param int $fontSize [文字大小]
     * @param null $targetPath [合成图片输出目录]
     * @param int $angle [文字旋转角度]
     * @return bool
     */
    public static function combineWord($source,$word,$posX=null, $posY=null,$isCenter=true,$fontColor='black',$fontSize=18,$targetPath=null,$angle=0,$font='myhfont')
    {
        if (!file_exists($source)) {
            return false;
        }

        $sourceInfo = null;
        $imgObj = self::getImageObj($source,$sourceInfo);
        if($imgObj === false) {
            return false;
        }

//        $font = dirname(__FILE__) . '/msyh.ttc';
        if($font=='pingfang') {
            $font = dirname(__FILE__) . '/PingFang.ttc';
        } else {
            $font = dirname(__FILE__) . '/myhfont.otf';
        }

        $box = static::calculateTextBox($fontSize,0,$font,$word);
        $sourceWidth = $sourceInfo[0];
        $sourceHeight = $sourceInfo[1];

        if($isCenter==true) {
            //取得使用 TrueType 字体的文本的范围
            $posX = $sourceWidth/2 - $box['width']/2;
        }

        if($posX<0) {
            $posX  = $sourceWidth + $posX - $box['width'];
        }

        if($posY<0) {
            $posY  = $sourceHeight + $posY - $box['top'];
        } else {
            $posY = $posY + $box['top'];
        }


        $fontColor = self::getImageColor($imgObj,$fontColor);
        imagefttext($imgObj, $fontSize, $angle, $posX, $posY, $fontColor, $font, $word);
        $ImageFun = 'image' . $sourceInfo['type'];
        if(empty($targetPath)) {
            $targetPath = $source;
        }
        $ImageFun($imgObj,$targetPath);
        imagedestroy($imgObj);

        return true;
    }

    /**
     * 计算文本盒子宽高
     * @param $font_size
     * @param $font_angle
     * @param $font_file
     * @param $text
     * @return array|bool
     */
    public static function  calculateTextBox($font_size, $font_angle, $font_file, $text) {
        $box   = imagettfbbox($font_size, $font_angle, $font_file, $text);
        if( !$box )
            return false;
        $min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
        $max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
        $min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
        $max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
        $width  = ( $max_x - $min_x );
        $height = ( $max_y - $min_y );
        $left   = abs( $min_x ) + $width;
        $top    = abs( $min_y ) + $height;
        // to calculate the exact bounding box i write the text in a large image
        $img     = @imagecreatetruecolor( $width << 2, $height << 2 );
        $white   =  imagecolorallocate( $img, 255, 255, 255 );
        $black   =  imagecolorallocate( $img, 0, 0, 0 );
        imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);
        // for sure the text is completely in the image!
        imagettftext( $img, $font_size,
            $font_angle, $left, $top,
            $white, $font_file, $text);
        // start scanning (0=> black => empty)
        $rleft  = $w4 = $width<<2;
        $rright = 0;
        $rbottom   = 0;
        $rtop = $h4 = $height<<2;
        for( $x = 0; $x < $w4; $x++ )
            for( $y = 0; $y < $h4; $y++ )
                if( imagecolorat( $img, $x, $y ) ){
                    $rleft   = min( $rleft, $x );
                    $rright  = max( $rright, $x );
                    $rtop    = min( $rtop, $y );
                    $rbottom = max( $rbottom, $y );
                }
        // destroy img and serve the result
        imagedestroy( $img );
        return array( "left"   => $left - $rleft,
            "top"    => $top  - $rtop,
            "width"  => $rright - $rleft + 1,
            "height" => $rbottom - $rtop + 1 );
    }

    /**
     * 获取颜色
     * @param $imgObj [图片对象]
     * @param $color [颜色]
     * @return null
     */
    public static function getImageColor($imgObj,$color)
    {
        $fontColor = null;
        if(is_string($color)) {
            switch ($color){
                case 'black':
                    $fontColor = imagecolorallocatealpha($imgObj, 0, 0, 0, 0);
                    break;
                case 'white':
                    $fontColor = imagecolorallocatealpha($imgObj, 255, 255, 255, 0);
                    break;
                case 'gray':
                    $fontColor = imagecolorallocatealpha($imgObj, 102, 102, 102, 0);
                    break;
                case 'moreGray':
                    $fontColor = imagecolorallocatealpha($imgObj, 153, 153, 153, 0);
                    break;
                default:
                    $fontColor = imagecolorallocatealpha($imgObj, 0, 0, 0, 0);
                    break;
            }
        }
        if(is_array($color)) {
            $fontColor = imagecolorallocatealpha($imgObj, $color[0], $color[1], $color[2], 0);
        }

        return $fontColor;
    }

    /**
     * 调整图片大小
     * @param $source [图片源]
     * @param $width [调整后宽]
     * @param $height [调整后高]
     * @param null $targetPath [合成图片输出目录]
     * @return bool
     */
    public static function resize($source, $width, $height,$targetPath=null)
    {
//        if (!file_exists($source)) {
//            return false;
//        }

        $sourceInfo = null;
        $imgObj = self::getImageObj($source, $sourceInfo);
        if((false === $imgObj) || empty($sourceInfo)) {
            return false;
        }

        $width = round($width);
        $height = round($height);

        $newImgObj = imagecreatetruecolor($width, $height);
        imagecopyresampled($newImgObj, $imgObj, 0, 0, 0, 0, $width, $height, $sourceInfo[0], $sourceInfo[1]);
        $ImageFun = 'image' . $sourceInfo['type'];
        if(empty($targetPath)) {
            $targetPath = $source;
        }
        $ImageFun($newImgObj,$targetPath);
        imagedestroy($newImgObj);
        imagedestroy($imgObj);

        return true;
    }

    /**
     * 图片内切圆
     * @param $source [图片地址]
     * @param null $radius [圆半径]
     * @param null $targetPath [输出目录]
     * @param null $fillColor [四个角填充色]
     * @return bool
     */
    public static function circle($source,$radius=null,$targetPath=null,$fillColor=null)
    {
        $sourceInfo = null;
        $imgObj = self::getImageObj($source, $sourceInfo);
        if((false === $imgObj) || empty($sourceInfo)) {
            return false;
        }

        $w = $sourceInfo[0];
        $h = $sourceInfo[1];
        $newImage = imagecreatetruecolor($w,$h);
        imagealphablending($newImage,false);
        if (!empty($fillColor) && is_array($fillColor)) {
            $transparent = imagecolorallocatealpha($newImage, $fillColor[0], $fillColor[1], $fillColor[2], $fillColor[3]);
        } else {
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        }

        $r = !empty($radius) ? $radius : ($w/2);

        for($x=0;$x<$w;$x++)
            for($y=0;$y<$h;$y++){
                $c = imagecolorat($imgObj,$x,$y);
                $_x = $x - $w/2;
                $_y = $y - $h/2;
                if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
                    imagesetpixel($newImage,$x,$y,$c);
                }else{
                    imagesetpixel($newImage,$x,$y,$transparent);
                }
            }
        imagesavealpha($newImage, true);
        $targetPath = !empty($targetPath) ? $targetPath : $source;
        imagepng($newImage, $targetPath);
        imagedestroy($newImage);
        imagedestroy($imgObj);

        return true;
    }

    /**
     * 生成文字图片
     * @param $word [文字]
     * @param null $targetPath [文字图片输出目录]
     * @param int $fontSize [字体大小]
     * @param null $fontColor [字体颜色，数组]
     * @param null $bgColor [背景色颜色，数组]
     * @param null $width [宽度]
     * @param null $height [高度]
     * @return resource
     */
    public static function wordImage($word,$targetPath=null,$fontSize=18,$fontColor=null,$bgColor=null,$width=null,$height=null)
    {
        $font = dirname(__FILE__) . '/myhfont.otf';
        $box = static::calculateTextBox($fontSize,0,$font,$word);

        $width = !empty($width) ? $width : $box['width'];
        $height = !empty($height) ? $height : $box['height'];
        $im = imagecreatetruecolor($width, $height);

        if(!empty($fontColor) && is_array($fontColor)) {
            $fontColor = imagecolorallocate($im, $fontColor[0], $fontColor[1], $fontColor[2]);
        } else {
            $fontColor = imagecolorallocate($im, 0, 0, 0);
        }

        if(!empty($bgColor) && is_array($bgColor)) {
            $bgColor = imagecolorallocate($im, $bgColor[0], $bgColor[1], $bgColor[2]);
        } else {
            $bgColor = imagecolorallocate($im, 255, 255, 255);
        }

        $posX = $width/2 - $box['width']/2;
        $posY = $height/2 + $fontSize/2 ;
        imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);
        imagefttext($im, $fontSize, 0, $posX, $posY, $fontColor, $font, $word);

        if(!empty($targetPath)) {
            imagepng($im,$targetPath);
            imagedestroy($im);
        }
        return $im;
    }

    /**
     * 生成文字中线
     * @param $source [路径]
     * @param $price
     * @param $x
     * @param $y
     * @param $size
     * @param $color
     * @param bool $type
     */
    public static function generateWordMidLine($source,$price,$x,$y,$size,$color,$type = false)
    {
        $sourceInfo = null;
        $image = ImageTools::getImageObj($source,$sourceInfo);
        $font = dirname(__FILE__) . '/myhfont.otf';
        $gap =15;
        $box = @imageTTFBbox($size, 0, $font, $price);
        $lineH = abs($box[1]-$box[5]);
        $lineW = abs($box[4] - $box[0]);
        if ($type){
            imagefttext($image, $size, 0, $x, $y, $color, $font, "¥");
        }
        imagefttext($image, $size, 0, $x+$gap, $y, $color, $font, $price);
        imagefilledrectangle($image, $x+$gap, $y-$lineH/2, $x+$gap+$lineW, $y-$lineH/2+1,$color);

        $ImageFun = 'image' . $sourceInfo['type'];
        $ImageFun($image,$source);
        imagedestroy($image);
    }
}