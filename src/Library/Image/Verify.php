<?php

/**
 * 图片验证码
 * @example
 * Verify::create(4, 30, 20);
 * Verify::output();
 */

namespace Fengzyz\Image;

class Verify
{

    // 生成的验证码
    protected static $code;
    // 生成的验证码的时间
    protected static $createTime;
    // 验证码图片实例 
    protected static $_image;
    // 验证码字体颜色    
    protected static $_color;
    // 验证码过期时间（单位：秒）
    protected static $expire = 3000;
    // 验证码中使用的字符，01IO容易混淆，不用  
    protected static $codeSet = '3456789ABCDEFGHJKLMNPQRTUVWXY';
    // 验证码字体大小(px)   
    protected static $fontSize = 16;
    // 是否画混淆曲线
    protected static $useCurve = true;
    // 是否添加杂点    
    protected static $useNoise = true;
    // 验证码图片宽
    protected static $imageH = 30;
    // 验证码图片长
    protected static $imageL = 100;
    // 验证码位数   
    protected static $length = 4;
    // 背景     
    protected static $bg = array(243, 251, 254);
    // 验证码图片长
    protected static $type = 'jpeg';
    // 验证码图片长
    protected static $seKey = 'verify';

    /**
     * 输出图片
     */
    public static function output($filename = null)
    {
        header('Pragma: no-cache');
        header("Content-type: image/" . static::$type);
        $ImageFun = 'image' . static::$type;
        if (empty($filename)) {
            $ImageFun(static::$_image);
        } else {
            $ImageFun(static::$_image, $filename);
        }
        imagedestroy(static::$_image);
    }

    /**
     * 获取静态属性
     */
    protected static function getStaticProperties()
    {
        static $reflection;
        if (!isset($reflection)) {
            $reflection = new \ReflectionClass(__CLASS__);
        }
        return $reflection->getStaticProperties();
    }

    /**
     * 设置静态属性值
     */
    public static function setOption($key, $value)
    {
        if (isset($value) && in_array($key, static::getStaticProperties())) {
            static::$$key = $value;
        }
    }

    /**
     * 获取验证码
     */
    public static function getCode()
    {
        return static::$code;
    }

    /**
     * 获取验证码生成时间
     */
    public static function getCreateTime()
    {
        return static::$createTime;
    }

    /**
     * 生成验证码
     */
    public static function create($length = null, $imageL = null, $imageH = null, $fontSize = null, $type = null)
    {
        // 字符个数
        static::setOption('length', $length);
        // 图片宽(px)
        static::setOption('imageL', $imageL);
        // 图片高(px)   
        static::setOption('imageH', $imageH);
        // 字体大小
        static::setOption('fontSize', $fontSize);
        // 图片类型
        static::setOption('type', $type);

        // 建立一幅 static::$imageL x static::$imageH 的图像   
        static::$_image = imagecreate(static::$imageL, static::$imageH);
        // 设置背景         
        imagecolorallocate(static::$_image, static::$bg[0], static::$bg[1], static::$bg[2]);
        // 验证码字体随机颜色   
        static::$_color = imagecolorallocate(static::$_image, mt_rand(1, 120), mt_rand(1, 120), mt_rand(1, 120));
        // 验证码使用随机字体，保证目录下有这些字体集   
        $ttf = dirname(__FILE__) . '/Verify.ttf';

        if (static::$useNoise) {
            // 绘杂点   
            static::_writeNoise();
        }
        if (static::$useCurve) {
            // 绘干扰线   
            static::_writeCurve();
        }
        // 绘验证码   
        $spaceUnit = (static::$imageL - static::$fontSize * static::$length) / (static::$length + 3); // 字符间距
        $code = array(); // 验证码   
        $codeNX = $spaceUnit; // 验证码第N个字符的左边距 
        for ($i = 0; $i < static::$length; $i++) {
            $code[$i] = static::$codeSet[mt_rand(0, 28)];
            $codeNX += static::$fontSize * ($i ? 1 : 0) + $spaceUnit;
            // 写一个验证码字符   
            imagettftext(
                static::$_image
                , static::$fontSize
                , mt_rand(-30, 60)
                , $codeNX
                , (static::$imageH - static::$fontSize) / 2 + static::$fontSize
                , static::$_color, $ttf, $code[$i]
            );
        }

        // 保存验证码   
        static::$code = join('', $code);
        static::$createTime = time();
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     *      正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     */
    protected static function _writeCurve()
    {
        $A = mt_rand(1, static::$imageH / 2);                  // 振幅   
        $b = mt_rand(static::$imageH / 4, static::$imageH / 4);   // Y轴方向偏移量   
        $f = mt_rand(static::$imageH / 4, static::$imageH / 4);   // X轴方向偏移量   
        $T = mt_rand(static::$imageH * 1.5, static::$imageL * 2);  // 周期   
        $w = (2 * M_PI) / $T;

        $px1 = 0;  // 曲线横坐标起始位置   
        $px2 = mt_rand(static::$imageL / 2, static::$imageL * 0.667);  // 曲线横坐标结束位置              
        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + static::$imageH / 2;  // y = Asin(ωx+φ) + b   
                $i = (int)((static::$fontSize - 6) / 4);
                while ($i > 0) {
                    imagesetpixel(static::$_image, $px + $i, $py + $i, static::$_color);
                    //这里画像素点比imagettftext和imagestring性能要好很多                     
                    $i--;
                }
            }
        }

        $A = mt_rand(1, static::$imageH / 2);                  // 振幅           
        $f = mt_rand(static::$imageH / 4, static::$imageH / 4);   // X轴方向偏移量   
        $T = mt_rand(static::$imageH * 1.5, static::$imageL * 2);  // 周期   
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - static::$imageH / 2;
        $px1 = $px2;
        $px2 = static::$imageL;
        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + static::$imageH / 2;  // y = Asin(ωx+φ) + b   
                $i = (int)((static::$fontSize - 8) / 4);
                while ($i > 0) {
                    imagesetpixel(static::$_image, $px + $i, $py + $i, static::$_color);
                    //这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出
                    //的（不用while循环）性能要好很多       
                    $i--;
                }
            }
        }
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    protected static function _writeNoise()
    {
        for ($i = 0; $i < 10; $i++) {
            //杂点颜色   
            $noiseColor = imagecolorallocate(
                static::$_image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225)
            );
            for ($j = 0; $j < 5; $j++) {
                // 绘杂点   
                imagestring(
                    static::$_image
                    , 5
                    , mt_rand(-10, static::$imageL)
                    , mt_rand(-10, static::$imageH)
                    , static::$codeSet[mt_rand(0, 28)] // 杂点文本为随机的字母或数字
                    , $noiseColor
                );
            }
        }
    }

}
