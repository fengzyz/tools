<?php

/**
 * 本类为图片处理工具类，用于图片的缩放、剪辑、格式转换
 *
 * @author fengzyz
 * @since 2019-05-17
 */

namespace Fengzyz\Image;

class ImageToolkit
{

    /**
     * 从文件中读入一张图片，返回该图像标识符，失败返回false，
     * 只支持jpg/jpeg，gif，png，bmp
     *
     * @param string $image_file 图像文件路径
     * @param array $image_info 若提供了本参数，则将实参设置为getimagesize函数的结果
     * @return resource 图像标识符，失败返回false
     */
    public static function read($image_file, &$image_info = null)
    {
        $info = getimagesize($image_file);
        if(false === $info) {
            return false;
        }
        $image_info = $info;
        switch($info[2]) {
            case 1: // gif
                $img = imagecreatefromgif($image_file);
                break;
            case 2: // jpeg
                $img = imagecreatefromjpeg($image_file);
                break;
            case 3: // png
                $img = imagecreatefrompng($image_file);
                break;
            case 6: // bmp
                $img = self::readbmp($image_file);
                break;
            default:
                return false;
        }
        return $img;
    }

    /**
     * 读取一张BMP图片，返回图片对象
     *
     * @param string $image_file
     * @return resource 返回图片对象
     */
    public static function readbmp($image_file)
    {
        // Load the image into a string
        $file = fopen($image_file, "rb");
        $read = fread($file, 10);
        while(!feof($file) && ($read <> "")) {
            $read .= fread($file, 1024);
        }

        $temp = unpack("H*", $read);
        $hex = $temp[1];
        $header = substr($hex, 0, 108);

        // Process the header
        // Structure: http://www.fastgraph.com/help/bmp_header_format.html
        if(substr($header, 0, 4) == "424d") {
            //  Cut it in parts of 2 bytes
            $header_parts = str_split($header, 2);

            // Get the width 4 bytes
            $width = hexdec($header_parts[19] . $header_parts[18]);

            // Get the height 4 bytes
            $height = hexdec($header_parts[23] . $header_parts[22]);

            // Unset the header params
            unset($header_parts);
        }

        // Define starting X and Y
        $x = 0;
        $y = 1;

        // Create newimage
        $image = imagecreatetruecolor($width, $height);

        // Grab the body from the image
        $body = substr($hex, 108);

        // Calculate if padding at the end-line is needed
        // Divided by two to keep overview.
        // 1 byte = 2 HEX-chars
        $body_size = (strlen($body) / 2);
        $header_size = ($width * $height);

        // Use end-line padding? Only when needed
        $usePadding = ($body_size > ($header_size * 3) + 4);

        // Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption
        // Calculate the next DWORD-position in the body
        for($i = 0; $i < $body_size; $i += 3) {
            // Calculate line-ending and padding
            if($x >= $width) {
                // If padding needed, ignore image-padding
                // Shift i to the ending of the current 32-bit-block
                if($usePadding) {
                    $i += $width % 4;
                }

                // Reset horizontal position
                $x = 0;

                // Raise the height-position (bottom-up)
                $y++;

                // Reached the image-height? Break the for-loop
                if($y > $height) {
                    break;
                }
            }

            // Calculation of the RGB-pixel (defined as BGR in image-data)
            // Define $i_pos as absolute position in the body
            $i_pos = $i * 2;
            $r = hexdec($body[$i_pos + 4] . $body[$i_pos + 5]);
            $g = hexdec($body[$i_pos + 2] . $body[$i_pos + 3]);
            $b = hexdec($body[$i_pos] . $body[$i_pos + 1]);

            // Calculate and draw the pixel
            $color = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $x, $height - $y, $color);

            // Raise the horizontal position
            $x++;
        }

        // Unset the body / free the memory
        unset($body);

        // Return image-object
        return $image;
    }

    /**
     * 保存BMP图片
     *
     * @param resource $image 需要保存或输出的图像标识符
     * @param string $filename 保存的文件名，否则输出到标准输出
     * @return bool 成功返回true，失败返回false
     */
    public static function savebmp($image, $filename = null)
    {
        $widthOrig = imagesx($image);
        // width = 16*x
        $widthFloor = ((floor($widthOrig / 16)) * 16);
        $widthCeil = ((ceil($widthOrig / 16)) * 16);
        $height = imagesy($image);

        $size = ($widthCeil * $height * 3) + 54;

        // Bitmap File Header
        $result = 'BM';     // header (2b)
        $result .= self::int_to_dword($size); // size of file (4b)
        $result .= self::int_to_dword(0); // reserved (4b)
        $result .= self::int_to_dword(54);  // byte location in the file which is first byte of IMAGE (4b)
        // Bitmap Info Header
        $result .= self::int_to_dword(40);  // Size of BITMAPINFOHEADER (4b)
        $result .= self::int_to_dword($widthCeil);  // width of bitmap (4b)
        $result .= self::int_to_dword($height); // height of bitmap (4b)
        $result .= self::int_to_word(1);  // biPlanes = 1 (2b)
        $result .= self::int_to_word(24); // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b)
        $result .= self::int_to_dword(0); // RLE COMPRESSION (4b)
        $result .= self::int_to_dword(0); // width x height (4b)
        $result .= self::int_to_dword(0); // biXPelsPerMeter (4b)
        $result .= self::int_to_dword(0); // biYPelsPerMeter (4b)
        $result .= self::int_to_dword(0); // Number of palettes used (4b)
        $result .= self::int_to_dword(0); // Number of important colour (4b)
        // is faster than chr()
        $arrChr = array();
        for($i = 0; $i < 256; $i++) {
            $arrChr[$i] = chr($i);
        }

        // creates image data
        $bgfillcolor = array("red" => 0, "green" => 0, "blue" => 0);

        // bottom to top - left to right - attention blue green red !!!
        $y = $height - 1;
        for($y2 = 0; $y2 < $height; $y2++) {
            for($x = 0; $x < $widthFloor;) {
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
                $rgb = imagecolorsforindex($image, imagecolorat($image, $x++, $y));
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
            }
            for($x = $widthFloor; $x < $widthCeil; $x++) {
                $rgb = ($x < $widthOrig) ? imagecolorsforindex($image, imagecolorat($image, $x, $y)) : $bgfillcolor;
                $result .= $arrChr[$rgb["blue"]] . $arrChr[$rgb["green"]] . $arrChr[$rgb["red"]];
            }
            $y--;
        }

        // see imagegif
        $return = false;
        if(empty($filename)) {
            echo $result;
            $return = !empty($result);
        } else {
            $file = fopen($filename, "wb");
            $return = fwrite($file, $result);
            fclose($file);
            $return = $file && $return;
        }
        return $return;
    }

    // imagebmp helpers
    private static function int_to_dword($n)
    {
        return chr($n & 255) . chr(($n >> 8) & 255) . chr(($n >> 16) & 255) . chr(($n >> 24) & 255);
    }

    private static function int_to_word($n)
    {
        return chr($n & 255) . chr(($n >> 8) & 255);
    }

    /**
     *  保存一张图片到文件，或直接输出到标准输出，成功返回true，失败返回false，
     *  只支持jpg/jpeg，gif，png，bmp
     *
     * @param resource $image 需要保存或输出的图像标识符
     * @param string $image_file 图片输出文件地址，为空时，输出到标准输出（浏览器），否则输出到文件
     * @param string $format 输出的图片格式，可接受的值为jpg、jpeg、gif、png，默认为jpg
     * @param int $quality 图片的质量，0最差，100最好，默认75，转换为非jpg格式时，忽略本参数
     * @return bool 成功返回true，失败返回false
     */
    public static function save($image, $image_file = null, $format = 'jpg', $quality = 75)
    {
        $result = false;
        switch($format) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($image, $image_file, $quality);
                break;
            case 'gif':
                $result = imagegif($image, $image_file);
                break;
            case 'png':
                $result = imagepng($image, $image_file);
                break;
            case 'bmp':
                $result = self::savebmp($image, $image_file);
            default:
                return false;
        }
        return $result;
    }

    /**
     *  转换图片格式，支持jpeg、gif、png、bmp格式的互相转换
     *
     * @param string $new_format 新的图片格式，可接受的值为jpg、jpeg、gif、png
     * @param string $img_src 需要转换的图片的地址
     * @param string $img_dis 图片输出文件地址，为空时，输出到标准输出（浏览器），否则输出到文件
     * @param int $quality 图片的质量，0最差，100最好，默认75，转换为非jpg格式时，忽略本参数
     * @return bool 成功返回true，失败返回false
     */
    public static function convert($new_format, $img_src, $img_dis = null, $quality = 75)
    {
        $img = self::read($img_src);
        if(false === $img) {
            return false;
        }
        $result = self::save($img, $img_dis, strtolower($new_format), $quality);
        imagedestroy($img);
        return $result;
    }

    /**
     * 计算等比例缩放的缩放率，返回缩放率，可以通过指定$result参数来获得缩放后的宽与高
     *
     * @param int $src_width 源图像宽度
     * @param int $src_height 源图像高度
     * @param int $dis_width 缩放宽度
     * @param int $dis_height 缩放高度
     * @param mixed $result 若指定本参数，则将实参设置为一个一维数组，其键分别为width,height
     * @return float 返回缩放比率
     */
    public static function computeScaleInfo($src_width, $src_height, $dis_width, $dis_height, &$result = null)
    {
        $scalerate = 0;
        if($dis_width && ($src_width < $src_height)) {
            $scalerate = $dis_height / $src_height;
            $dis_width = round($scalerate * $src_width);
        } else {
            $scalerate = $dis_width / $src_width;
            $dis_height = round($scalerate * $src_height);
        }
        $result = array('width' => $dis_width, 'height' => $dis_height);
        return $scalerate;
    }

    /**
     * 计算剪辑信息，返回一个一维数组，其键分别为x,y,width,height
     *
     * @param int $x 开始剪辑的横坐标
     * @param int $y 开始剪辑的纵坐标
     * @param int $src_width 源图像宽度
     * @param int $src_height 源图像高度
     * @param int $dis_width 剪辑宽度
     * @param int $dis_height 剪辑高度
     * @param mixed $result 若指定本参数，则将实参设置为一个一维数组，其键分别为x,y,width,height
     * @return array 返回一维数组，其键分别为x,y,width,height
     */
    public static function computeClipInfo($x, $y, $src_width, $src_height, $dis_width, $dis_height)
    {
        $dis_width > 0 || $dis_width = $src_width; // 如果没有设置宽度，则使用原图的宽度
        $dis_height > 0 || $dis_height = $src_height; // 如果没有设置高度，则使用原图的高度

        if($x + $dis_width > $src_width) {
            $dis_width = $src_width - $x;
            $dis_width > 0 || $dis_width = 1;
        }
        if($y + $dis_height > $src_height) {
            $dis_height = $src_height - $y;
            $dis_height > 0 || $dis_height = 1;
        }
        $x > 0 || $x = 0;
        $y > 0 || $y = 0;
        return array('x' => round($x), 'y' => round($y), 'width' => round($dis_width), 'height' => round($dis_height));
    }

    /**
     * 从文件读取一张图片，按指定的宽、高进行<strong>等比例</strong>缩放，
     * 缩放后的图像高度不大于指定的高，宽不大于指定的宽。
     *
     * @param string $img_src 需要进行缩放的图片文件地址
     * @param int $width 缩放后的最大宽度
     * @param int $height 缩放后的最大高度
     * @param boolean $zoomin 如果原图的宽、高小于指定的宽高，是否进行放大，默认为不放大
     * @return resource 成功时返回图像标识符，否则返回false
     */
    public static function scale($img_src, $width, $height, $zoomin = false)
    {
        $image_info = null;
        $img = self::read($img_src, $image_info);
        if((false === $img) || empty($image_info)) {
            return false;
        }
        $img_w = $image_info[0]; // 原图的宽度
        $img_h = $image_info[1]; // 原图的高度
        // 当图片宽、高均小于指定的宽、高时，如果不进行放大，则直接返回源图像
        if(!$zoomin && ($img_w < $width) && ($img_h < $height)) {
            return $img;
        }
        self::computeScaleInfo($img_w, $img_h, $width, $height, $info);
        $new_image = imagecreatetruecolor($info['width'], $info['height']);
        imagecopyresampled($new_image, $img, 0, 0, 0, 0, $info['width'], $info['height'], $img_w, $img_h);
        imagedestroy($img);
        return $new_image;
    }

    /**
     * 从文件读取一张图片，按指定的宽、高进行<strong>非等比例</strong>的缩放，
     * 缩放后的图像高度等于指定的高，宽等于指定的宽。
     *
     * @param string $img_src 需要进行缩放的图片文件地址
     * @param int $width 缩放后的最大宽度
     * @param int $height 缩放后的最大高度
     * @return resource 成功时返回图像标识符，否则返回false
     */
    public static function resize($img_src, $width, $height)
    {
        $image_info = null;
        $img = self::read($img_src, $image_info);
        if((false === $img) || empty($image_info)) {
            return false;
        }
        $width = round($width);
        $height = round($height);
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $img, 0, 0, 0, 0, $width, $height, $image_info[0], $image_info[1]);
        imagedestroy($img);
        return $new_image;
    }

    /**
     * 从给定图片的指定坐标位置开始，裁剪出指定宽、高的图片。
     * 裁剪出来的图片，宽度不大于指定宽度，高度不大于指定高度。
     * 返回裁剪出来的图片的标识符。
     *
     * @param string $img_src 需要进行剪辑的图片文件地址
     * @param int $x 剪辑的横坐标，默认为0
     * @param int $y 剪辑的纵坐标，默认为0
     * @param int $width 剪辑的宽度，忽略时，使用原图的宽度
     * @param int $height 剪辑的高度，忽略时，使用原图的高度
     * @return resource 成功时返回经过剪辑的图像标识符，否则返回false
     */
    public static function clip($img_src, $x = 0, $y = 0, $width = 0, $height = 0)
    {
        $image_info = null;
        $img = self::read($img_src, $image_info);
        if((false === $img) || empty($image_info)) {
            return false;
        }
        $info = self::computeClipInfo($x, $y, $image_info[0], $image_info[1], $width, $height);
        $new_image = imagecreatetruecolor($info['width'], $info['height']);
        imagecopyresampled($new_image, $img, 0, 0, $info['x'], $info['y'], $info['width'], $info['height'], $info['width'], $info['height']);
        imagedestroy($img);
        return $new_image;
    }

}
