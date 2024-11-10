<?php

namespace ng169\tool;

use ng169\tool\File;
use ng169\Y;

checktop();
class Image
{

    //保持图片到本地
    public static function imgtolocal($path, $type = '', $savename = '', $savepath = null)
    {
        if ($path == "") {
            return false;
        }

        $rootpath = $savepath ? $savepath : "/d/xs/pic/";

        if ($type == '') {
            $attchementdir = $rootpath;
        } else {
            $attchementdir = $rootpath . $type . '/';
        }

        if (!preg_match('/\/([^\/]+\.([a-z]{3,4}))$/i', $path, $matches)) {
            // return false;
        }
        $ext = '';
        if ($matches && $matches[2]) {
            $ext = $matches[2];
        }

        if ($savename) {
            $datedir = '';
            $newName = $savename;
        } else {
            $datedir = date("Ym", time()) . "/" . date("d", time()) . "/" . date("H_i", time()) . "/";
            $newName = substr(md5($path . rand(00000, 99999)), 8, 16) . '.' . $ext;
        }

        $path1 = $attchementdir . $datedir;
        if ($savepath) {
            $path1 = $savepath;
        }



        $image_name = $path1 . $newName;
        // d($image_name);
        File::createDir(dirname($image_name));
        $retname = $type . '/' . $datedir . $newName;
        if ($savepath) {
            $retname = $newName;
        }
        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch, CURLOPT_REFERER, "https:\/\/www.taobao.com\/");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        // curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_PROXY, '192.168.2.106');
        // curl_setopt($ch, CURLOPT_PROXYPORT, '8888');


        $img = curl_exec($ch);
        curl_close($ch);
        if (!$img) {
            return false;
        }

        $fp = fopen($image_name, 'w');
        //抓取失败
        fwrite($fp, $img);
        fclose($fp);

        return $retname;
    }
    public static function imgtolocalwebp($path, $type = '', $savename = '', $savepath = null)
    {
        if ($path == "") {
            return false;
        }

        $rootpath = $savepath ? $savepath : "/d/xs/pic/";

        if ($type == '') {
            $attchementdir = $rootpath;
        } else {
            $attchementdir = $rootpath . $type . '/';
        }

        if (!preg_match('/\/([^\/]+\.([a-z]{3,4}))$/i', $path, $matches)) {
            // return false;
        }
        $ext = '';
        if ($matches && $matches[2]) {
            $ext = $matches[2];
        }

        if ($savename) {
            $datedir = '';
            $newName = $savename;
        } else {
            $datedir = date("Ym", time()) . "/" . date("d", time()) . "/" . date("H_i", time()) . "/";
            $newName = substr(md5($path . rand(00000, 99999)), 8, 16) . '.' . $ext;
        }

        $path1 = $attchementdir . $datedir;
        if ($savepath) {
            $path1 = $savepath;
        }



        $image_name = $path1 . $newName;
        // d($image_name);
        File::createDir(dirname($image_name));
        $retname = $type . '/' . $datedir . $newName;
        if ($savepath) {
            $retname = $newName;
        }
        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch, CURLOPT_REFERER, "https:\/\/www.taobao.com\/");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        // curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_PROXY, '192.168.2.106');
        // curl_setopt($ch, CURLOPT_PROXYPORT, '8888');


        $img = curl_exec($ch);
        curl_close($ch);
        if (!$img) {
            return false;
        }

        // $fp = fopen($image_name, 'w');
        // //抓取失败
        // fwrite($fp, $img);
        // fclose($fp);

        // return $retname;
        $imagick = new \Imagick();
        $imagick->readImageBlob($img);
        $imagick->setImageFormat('webp'); // 转换为webp格式
    
        // 保存图片
        $webp_image_name = str_replace('.' . $ext, '.webp', $image_name); // 替换文件扩展名为.webp
        $imagick->writeImage($webp_image_name);
        $imagick->clear();
        $imagick->destroy();
    
        return str_replace($image_name, $webp_image_name, $retname); // 返回webp图片的路径



    }
    public static function isimg($srcfile)
    {
        $data = @getimagesize($srcfile);

        if ($data == false) {
            return false;
        }

        return true;
    }
    public static function makeCut($srcfile, $size = null, $savefile = null)
    {
        $upload_config = Y::$conf;

        /*if(!file_exists($srcfile)){
        return '';
        }*/

        $simg = getimagesize($srcfile);

        $sw = $simg[0] == 0 ? 1 : $simg[0];
        $sh = $simg[1] == 0 ? 1 : $simg[1];

        $rw = ($size['width'] / $sw);

        $rh = ($size['height'] / $sh);

        if ($rw == 0 && $rh != 0) {
            $rw = $rh;
            $size['width'] = $rw * $sw;
        } elseif ($rw != 0 && $rh == 0) {

            $rh = $rw;
            $size['height'] = $rh * $sh;
        }

        if ($simg) {
            if ($rw == $rh && $rh != 1 && $rw != 1) {

                $ret = self::makeThumb($srcfile, $size, $savefile);
            } elseif ($rw == $rh && $rh == 1 && $rw == 1) {

                @copy($srcfile, $savefile);
                $ret = $savefile;
            } else {

                $ret = self::cut($srcfile, 0, 0, $size['width'], $size['height'], $savefile);
            }
        }
        if ($ret) {
            return $ret;
        } else {
            return $srcfile;
        }
    }

    public static function makeThumb($srcfile, $size = null, $savefile = null)
    {
        $upload_config = Y::$conf;

        /*if(!file_exists($srcfile)){
        return '';
        }*/

        $dstfile = $savefile ? $savefile : $srcfile;
        if (is_array($size)) {
            $tow = ($size['width']) ? $size['width'] : $size[0];
            $toh = ($size['height']) ? $size['height'] : $size[1];
        }
        $tow = isset($tow) ? $tow : 60;

        $toh = isset($toh) ? $toh : 60;
        $simg = getimagesize($srcfile);
        $sw = $simg[0];
        $sh = $simg[1];
        if ($simg[0] < $tow && $simg[1] < $toh) {
            return $srcfile;
        }
        if ($tow < 60) {
            $tow = 60;
            $toh = $toh * (60 / $size['width']);
        }

        $make_max = 0;

        $maxtow = isset($upload_config['maxthumbwidth']) ? intval($upload_config['maxthumbwidth']) : 0;
        $maxtoh = isset($upload_config['maxthumbheight']) ? intval($upload_config['maxthumbheight']) : 0;
        if ($maxtow >= 300 && $maxtoh >= 300) {
            $make_max = 1;
        }
        $im = '';
        $ispng = false;
        if ($data = getimagesize($srcfile)) {
            if ($data[2] == 1) {
                $make_max = 0;
                if (function_exists("imagecreatefromgif")) {
                    $im = imagecreatefromgif($srcfile);
                }
            } elseif ($data[2] == 2) {
                if (function_exists("imagecreatefromjpeg")) {
                    $im = imagecreatefromjpeg($srcfile);
                }
            } elseif ($data[2] == 3) {

                if (function_exists("imagecreatefrompng")) {
                    $ispng = 1;
                    $im = imagecreatefrompng($srcfile);

                    imagesavealpha($im, true);
                }
            }
        }

        if (!$im) {
            return '';
        }

        $srcw = imagesx($im);
        $srch = imagesy($im);
        $towh = $tow / $toh;
        $srcwh = $srcw / $srch;

        if ($towh <= $srcwh) {
            $ftow = $tow;
            $ftoh = $ftow * ($srch / $srcw);
            $fmaxtow = $maxtow;
            $fmaxtoh = $fmaxtow * ($srch / $srcw);
        } else {
            $ftoh = $toh;
            $ftow = $ftoh * ($srcw / $srch);
            $fmaxtoh = $maxtoh;
            $fmaxtow = $fmaxtoh * ($srcw / $srch);
        }
        if ($srcw <= $maxtow && $srch <= $maxtoh) {
            $make_max = 0;
        }

        if ($srcw > $tow || $srch > $toh) {

            if (
                function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") &&
                $ni = imagecreatetruecolor($ftow, $ftoh)
            ) {

                if ($ispng) {
                    $c = imagecolorallocatealpha($ni, 0, 0, 0, 127);
                    imagealphablending($ni, false);

                    imagesavealpha($ni, true);
                }

                imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);

                if ($make_max && @$maxni = imagecreatetruecolor($fmaxtow, $fmaxtoh)) {
                    imagecopyresampled($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
                }
            } elseif (
                function_exists("imagecreate") && function_exists("imagecopyresized") &&
                $ni = imagecreate($ftow, $ftoh)
            ) {

                if ($ispng) {
                    $c = imagecolorallocatealpha($ni, 0, 0, 0, 127);
                    imagealphablending($ni, false);
                    imagesavealpha($ni, true);
                }
                imagecopyresized($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);

                if ($make_max && @$maxni = imagecreate($fmaxtow, $fmaxtoh)) {
                    imagecopyresized($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
                }
            } else {

                return '';
            }
            $path = dirname($dstfile);

            if (!is_dir($path)) {
                File::createDir($path);
            }
            if (function_exists('imagejpeg') && !$ispng) {
                imagejpeg($ni, $dstfile, 100);

                if ($make_max) {
                    imagejpeg($maxni, $srcfile, 100);
                }
            } elseif (function_exists('imagepng')) {

                imagepng($ni, $dstfile);

                if ($make_max) {
                    imagepng($maxni, $srcfile);
                }
            }
            imagedestroy($ni);
            if ($make_max) {
                imagedestroy($maxni);
            }
        }
        imagedestroy($im);

        if (!file_exists($dstfile)) {
            return $srcfile;
        } else {
            return $dstfile;
        }
    }

    public static function makeWaterMark($srcfile)
    {

        Y::loadLib('option');
        $upload_config = Option::get('upload_config');

        $watermarkfile = empty($upload_config['watermarkfile']) ? ROOT .
            './tpl/static/images/watermark.png' :
            ROOT . "./" . $upload_config['watermarkfile'];

        if (!file_exists($watermarkfile) || !$water_info = getimagesize($watermarkfile)) {
            return '';
        }
        $water_w = $water_info[0];
        $water_h = $water_info[1];
        $water_im = '';
        switch ($water_info[2]) {
            case 1:
                @$water_im = imagecreatefromgif($watermarkfile);
                break;
            case 2:
                @$water_im = imagecreatefromjpeg($watermarkfile);
                break;
            case 3:
                @$water_im = imagecreatefrompng($watermarkfile);
                break;
            default:
                break;
        }
        if (empty($water_im)) {
            return '';
        }

        if (!file_exists($srcfile) || !$src_info = getimagesize($srcfile)) {
            return '';
        }
        $src_w = $src_info[0];
        $src_h = $src_info[1];
        $src_im = '';
        switch ($src_info[2]) {
            case 1:
                $fp = fopen($srcfile, 'rb');
                $filecontent = fread($fp, filesize($srcfile));
                fclose($fp);
                if (strpos($filecontent, 'NETSCAPE2.0') === false) {
                    @$src_im = imagecreatefromgif($srcfile);
                }
                break;
            case 2:
                @$src_im = imagecreatefromjpeg($srcfile);
                break;
            case 3:
                @$src_im = imagecreatefrompng($srcfile);
                break;
            default:
                break;
        }
        if (empty($src_im)) {
            return '';
        }

        if (($src_w < $water_w + 150) || ($src_h < $water_h + 150)) {
            return '';
        }
        $upload_config['watermarkpos'] = $upload_config['watermarkpos'] ? $upload_config['watermarkpos'] : 1;

        switch (intval($upload_config['watermarkpos'])) {
            case 1:
                $posx = 0;
                $posy = 0;
                break;
            case 2:
                $posx = $src_w - $water_w;
                $posy = 0;
                break;
            case 3:
                $posx = 0;
                $posy = $src_h - $water_h;
                break;
            case 4:
                $posx = $src_w - $water_w;
                $posy = $src_h - $water_h;
                break;
            default:
                $posx = mt_rand(0, ($src_w - $water_w));
                $posy = mt_rand(0, ($src_h - $water_h));
                break;
        }

        @imagealphablending($src_im, true);

        @imagecopy($src_im, $water_im, $posx, $posy, 0, 0, $water_w, $water_h);
        switch ($src_info[2]) {
            case 1:
                @imagegif($src_im, $srcfile);
                break;
            case 2:
                @imagejpeg($src_im, $srcfile);
                break;
            case 3:
                @imagepng($src_im, $srcfile);
                break;
            default:
                return '';
        }
        @imagedestroy($water_im);
        @imagedestroy($src_im);
    }

    public static function ys($old, $new)
    {
        $maxsize = 500;
        if (!class_exists('Imagick')) {
            return;
        }
        $image = new Imagick($old);
        if ($image->getImageHeight() <= $image->getImageWidth()) {
            $image->resizeImage($maxsize, 0, Imagick::FILTER_LANCZOS, 1);
        } else {
            $image->resizeImage(0, $maxsize, Imagick::FILTER_LANCZOS, 1);
        }
        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality(80);
        $image->stripImage();
        $image->writeImage($new);
        $image->destroy();
    }
    public static function cut($picname, $x, $y, $tw, $th, $savename = null)
    {
        $source_path = $picname;
        $target_width = $tw;
        $target_height = $th;
        $source_info = getimagesize($source_path);

        $source_width = $source_info[0];
        $source_height = $source_info[1];
        $source_mime = $source_info['mime'];
        $source_ratio = $source_height / $source_width;
        $target_ratio = $target_height / $target_width;

        if ($source_ratio > $target_ratio) {
            $cropped_width = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x = 0;
            $source_y = ($source_height - $cropped_height) / 2;
        } elseif ($source_ratio < $target_ratio) {
            $cropped_width = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x = ($source_width - $cropped_width) / 2;
            $source_y = 0;
        } else {
            $cropped_width = $source_width;
            $cropped_height = $source_height;
            $source_x = 0;
            $source_y = 0;
        }

        switch ($source_mime) {
            case 'image/gif':
                $source_image = @imagecreatefromgif($source_path);
                break;

            case 'image/jpeg':
                $source_image = @imagecreatefromjpeg($source_path);
                break;

            case 'image/png':
                $source_image = @imagecreatefrompng($source_path);
                break;

            default:
                return false;
                break;
        }
        $target_image = imagecreatetruecolor($target_width, $target_height);
        $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
        imagecopyresized($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
        imagepng($target_image, $savename, 2);
        self::ys($savename, $savename);
        imagedestroy($target_image);
        imagedestroy($cropped_image);
        return $savename;
    }
    public static function verify($name = 'verifycode', $width = '62', $height = 20, $bgr = 255, $bgg = 255, $bgb = 255)
    {

        session_start();
        /*ob_start();*/

        Header("Content-type: image/PNG");
        srand((float) microtime() * 1000000);

        $im = imagecreate(intval($width), intval($height));
        $black = ImageColorAllocate($im, 0, 0, 0);
        $white = ImageColorAllocate($im, 255, 255, 255);
        $gray = ImageColorAllocate($im, 200, 200, 200);

        $bgcolor = ImageColorAllocate($im, $bgr, $bgg, $bgb);
        // $bgcolor = imagecolorallocatealpha($im,  0 , 0 , 0 , 127); //透明背景
        imagefill($im, 0, 0, $bgcolor);

        while (($randval = rand() % 100000) < 10000); {
            $_SESSION[$name] = $randval;
            //  imagestring($im, 5, 10, 3, $randval, $white);
            imagestring($im, 5, 10, 3, $randval, $black);
        }

        for ($i = 0; $i < 200; $i++) {
            // $randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
            // imagesetpixel($im, rand() % 70 , rand() % 30 , $randcolor);
            $randcolor = ImageColorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand() % 100, rand() % 50, $randcolor);
        }

        ImagePNG($im);

        ImageDestroy($im);
        /*$output = ob_get_contents();

    ob_end_clean();
    Output::show($output);*/
    }
    public static function verify2($code = 'verifycode', $width = '62', $height = 20, $bgr = 255, $bgg = 255, $bgb = 255)
    {

        session_start();
        /*ob_start();*/

        Header("Content-type: image/PNG");
        srand((float) microtime() * 1000000);

        $im = imagecreate(intval($width), intval($height));
        $black = ImageColorAllocate($im, 0, 0, 0);
        $white = ImageColorAllocate($im, 255, 255, 255);
        $gray = ImageColorAllocate($im, 200, 200, 200);

        $bgcolor = ImageColorAllocate($im, $bgr, $bgg, $bgb);
        // $bgcolor = imagecolorallocatealpha($im,  0 , 0 , 0 , 127); //透明背景
        imagefill($im, 0, 0, $bgcolor);

        /*while(($randval = rand() % 100000) < 10000);
        {

        //  imagestring($im, 5, 10, 3, $randval, $white);
        imagestring($im, 5, 10, 3, $code, $black);
        }*/
        imagestring($im, 5, 10, 3, $code, $black);
        for ($i = 0; $i < 200; $i++) {
            // $randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
            // imagesetpixel($im, rand() % 70 , rand() % 30 , $randcolor);
            $randcolor = ImageColorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand() % 100, rand() % 50, $randcolor);
        }

        ImagePNG($im);

        ImageDestroy($im);
    }
    public static function qr($url)
    {

        im(CORE . 'other/phpqrcode.php');

        $errorCorrectionLevel = "L";
        $matrixPointSize = "4";
        \QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
        exit;
    }

    public static function _setTransparency($imgSrc, $imgDest, $ext)
    {

        if ($ext == "png" || $ext == "gif") {
            $trnprt_indx = imagecolortransparent($imgSrc);

            if ($trnprt_indx >= 0) {

                $trnprt_color = imagecolorsforindex($imgSrc, $trnprt_indx);

                $trnprt_indx = imagecolorallocate($imgDest, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

                imagefill($imgDest, 0, 0, $trnprt_indx);

                imagecolortransparent($imgDest, $trnprt_indx);
            } elseif ($ext == "png") {

                imagealphablending($imgDest, true);

                $color = imagecolorallocatealpha($imgDest, 0, 0, 0, 127);

                imagefill($imgDest, 0, 0, $color);

                imagesavealpha($imgDest, true);
            }
        }
    }
}
