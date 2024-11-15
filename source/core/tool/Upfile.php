<?php

namespace ng169\tool;

use ng169\tool\File;
use ng169\lib\Log as YLog;
use ng169\tool\Request as YRequest;
use ng169\Y;

checktop();

class Upfile
{
    const UPLOAD_ERR_INI_SIZE = 1;
    const INPUT_MAX_FILE_SIZE = 2;
    const UPLOAD_HALF = 3;
    const UPLOAD_ERR_NO_TMP_DIR = 4;
    const FILE_PATH = 'data/image/';
    const GL_FILE_PATH = 'data/illegal/';
    const USE_REAL_PATH = 1;//是否使用绝对路径:0不使用绝对路径,1使用绝对路径

    /*private $attchementdir = 'data/attachment/';
    */

    private $params;
    private $checkcontent = G_UPFILE_CHECK;
    private $defaultMaxSize = 2048000;
    private $fileurl;

    private $defaultAllowFileType = array(
        'gif',
        'jpeg',
        'jpg',
        'png',
        'swf',
        'flv',
        'rar',
        'zip',
        'tar',
        'gz',
    );

    private $defaultForbidType = array(
        'php',
        'html',
        'shtml',
        'js',
        'asp',
        'aspx',
        'jsp',
        'exe',
        'sql'
    );

    private $errorCodeArr = array(
        'upload_error'         => -1,
        'not_upload_files'     => -2,
        'not_an_allowed_type'  => -3,
        'file_size_is_large'   => -4,
        'upload_err_ini_size'  => -5,
        'input_max_file_size'  => -6,
        'upload_half'          => -7,
        'upload_err_no_tmp_dir' => -8,
        'illegal_file_type'    => -9,
        'upload_content_error' => -10,
        'input_file_is_Trojan' => -11
    );
    private $errcodetomsg = array(
        '-1' => '上传失败',
        '-2' => '不是通过HTTP POST方法上传',
        '-3' => '不允许的上传类型',
        '-4' => '文件太大',
        '-5' => '上传文件超过服务器上传限制',
        '-6' => '上传文件超过表达最大上传限制',
        '-7' => '只上传了一半文件',
        '-8' => '上传的临时目录出错',
        '-9' => '新的文件名，命名不合法',
        '-10' => '上传的内容不合法'
    );
    private $dir;

    public function __construct($conf = null)
    {
        if ($conf != null) {
            $type = $conf['filetype'];
            if (!is_array($type)) {
                $type = explode(',', $type);
            }
            // $confdir = preg_replace( '/\./', '', $conf['upfilepath'] );
            //避免后台夸目录上传
            $confdir = $conf['upfilepath'];
            //避免后台夸目录上传
            //            $this->attchementdir = self::FILE_PATH.FG.$conf['upfilepath'];
            $this->dir = $confdir . '/';
            $this->defaultAllowFileType = $type;
            if (!isset($conf['upfilesize'])) {
                $conf['upfilesize'] = Y::$conf['upfilesize'];
            }
            $this->defaultMaxSize = $conf['upfilesize'] * 1024;
        }
        if ($conf['save_url']) {
            $this->fileurl = $conf['save_url'];
        } else {
            $this->fileurl = 'http://' . $_SERVER['SERVER_NAME'] . '/';
        }
    }

    public function upload($name, $module = '', $path = '', $params = array(), $oldname = false)
    {

        //有用户id;
        //就用用户Id分组
        if (isset(Y::$wrap_user['uid'])) {
            $userdir = '/user/' . @Y::$wrap_user['uid'] . '/';
        } else {
            $userdir = '/anony/';
        }




        if ($oldname) {
            $datedir = $userdir . date('Ym', time());

            $newName = pathinfo($_FILES[$name]['name'],PATHINFO_FILENAME) . '_' . date('dHis');
            // $newName = substr( md5( time() . rand( 00000, 99999 ) ), 8, 16 );
        } else {
            $datedir = $userdir . date('Ym', time()) . '/' . date('d', time());
            $newName = substr(md5(time() . rand(00000, 99999)), 8, 16);
        }
        if (empty($path)) {
            $path =  $datedir;
        } else {
            $path = '/' . $path . $datedir;
        }
      
        $this->params = $this->parseParams($params);
        $uploadInfo = $this->init($name, $newName, $path);

        if (!$uploadInfo)
            return $this->error('upload_error');
        $errorVal = $this->checkUpload($uploadInfo['error']);
        if ($errorVal !== true)
            return $this->error($errorVal);
        if (!$this->checkIsUploadFile($uploadInfo['tmp_name']))
            return $this->error('not_upload_files');
        if (!$this->checkType($uploadInfo['ext']))
            return $this->error('not_an_allowed_type');
        if (!$this->checkSize($uploadInfo['size']))
            return $this->error('file_size_is_large');
        if (!$this->checkNewName($newName))
            return $this->error('illegal_file_type');

        
         if(self::USE_REAL_PATH){
           
            $result = $this->save($uploadInfo['tmp_name'], $this->dir . $uploadInfo['source'], $this->dir . $uploadInfo['path']);

         }   else{
            $result = $this->save($uploadInfo['tmp_name'],  $uploadInfo['source'],  $uploadInfo['path']);

         }    
        
        if ($result == false) {
            return $this->error('upload_error');
        } else {
         
            $checkContentResult = $this->checkContent($uploadInfo);
          
            if ($checkContentResult !== true)
                return $this->error($checkContentResult);

            @unlink($uploadInfo['tmp_name']);
            unset($uploadInfo['tmp_name']);
            /*$urlfile = $this->abs2rel( ROOT, $uploadInfo['source'] );
                    */

            $uploadInfo['source'] = $this->fileurl . $uploadInfo['source'];
            return array('flag' => 1, 'data' => $uploadInfo);
        }
    }

    public function setParams($params)
    {
        $this->params = $this->parseParams($params);
    }

    private function init($name, $newName, $path)
    {

        $newName = $this->escapeStr($newName);
        $path    = $this->escapeDir($path);
        $file    = $_FILES[$name];

        if (!$file['tmp_name'] || $file['tmp_name'] == '')
            return false;
        $file['name'] = $this->escapeStr($file['name']);
        $file['ext'] = strtolower(substr(strrchr($file['name'], '.'), 1));
        $file['size'] = intval($file['size']);
        if ($file['size'] < 300000) {
            $this->checkcontent = true;
        }

        $file['type'] = $file['type'];
        $file['tmp_name'] = $file['tmp_name'];
        $file['source'] = $path . '/' . $newName . '.' . $file['ext'];
        $file['path'] = $path;
        $file['newName'] = $newName . '.' . $file['ext'];
        return $file;
    }

    private function parseParams(array $params)
    {
        $temp = array();
        $temp['maxSize'] = (isset($params['maxSize'])) ? (int)$params['maxSize'] : $this->defaultMaxSize;
        $temp['allowFileType'] = @(is_array($params['allowFileType'])) ? $params['allowFileType'] :
            $this->defaultAllowFileType;
        return $temp;
    }

    private function save($tmpName, $filename, $path)
    {

        if (!is_dir($path)) {

            File::createDir($path);
        }

        if (function_exists('move_uploaded_file') && @move_uploaded_file($tmpName, $filename)) {
            d("3234");
            @chmod($filename, 0777);
            return true;
        } elseif (@copy($tmpName, $filename)) {
            d("1113234");
            @chmod($filename, 0777);
            return true;
        }
        d("35345");
        return false;
    }

    private function checkUpload($error)
    {
        if ($error == self::UPLOAD_ERR_INI_SIZE) {
            return 'upload_err_ini_size';
        } elseif ($error == self::INPUT_MAX_FILE_SIZE) {
            return 'input_max_file_size';
        } elseif ($error == self::UPLOAD_HALF) {
            return 'upload_half';
        } elseif ($error == self::UPLOAD_ERR_NO_TMP_DIR) {
            return 'upload_err_no_tmp_dir';
        } else {
            return true;
        }
    }

    private function checkType($uploadType)
    {
        if (
            !in_array(strtolower($uploadType), $this->params['allowFileType']) ||
            in_array(strtolower($uploadType), $this->defaultForbidType)
        ) {
            return false;
        } else {
            return true;
        }
    }

    private function checkSize($uploadSize)
    {
// d($uploadSize);
// d($this->defaultMaxSize);
        return ($uploadSize < 1 || $uploadSize > ($this->defaultMaxSize)) ? false : true;
    }

    private function checkNewName($newName)
    {
        $newName = strtolower($newName);
        return (strpos($newName, '..') !== false || strpos($newName, '.php.') !== false ||
            @preg_match("\.php$", $newName)) ? false : true;
    }

    private function checkIsUploadFile($tmpName)
    {
        if (!$tmpName || $tmpName == 'none') {
            return false;
        } elseif (function_exists('is_uploaded_file') && !is_uploaded_file($tmpName) && !is_uploaded_file(str_replace('\\\\', '\\', $tmpName))) {
            return false;
        } else {
            return true;
        }
    }

    public function checkContent($uploadInfo)
    {
        if (!$this->checkcontent) return true;
        if(self::USE_REAL_PATH){
            $uploadInfo['source'] = $this->dir . $uploadInfo['source'];
        }else{
            $uploadInfo['source'] =  $uploadInfo['source'];
        }
      
        $file_content = $this->readover($uploadInfo['source']);

        if (empty($file_content)) {
            @unlink($uploadInfo['source']);
            return 'upload_content_error';
        } else {
            $forbid_chars = array(
                '0' => '?php',
                '1' => 'cmd.exe',
                '2' => 'mysql_connect',
                '3' => 'phpinfo()',
                '4' => 'get_current_user',
                '5' => 'zend',
                '6' => '_GET',
                '7' => '_POST',
                '8' => '_REQUEST',
                '9' => 'base64_decode',
                '10' => 'echo',
                '11' => '?PHP',
                '12' => 'execute',
                '13' => 'assert',
                '14' => 'mysql',
                '15' => 'eval',
                '16' => 'system',
                '17' => 'hack',
                '18' => 'fuck',
                '19' => 'phpspy',
                '20' => 'Scanners',
                '21' => 'rootkit',
                '22' => 'frame',

            );

            foreach ($forbid_chars as $key => $value) {
                if (stripos($file_content, $value)) {
                    $name = self::GL_FILE_PATH . time() . '__' . md5(rand(1237777, 45688888)) . '.' . $uploadInfo['ext'];
                    if (false) {
                        File::moveFile($uploadInfo['source'], $name);
                        @unlink($uploadInfo['source']);
                        $string = YRequest::getip() . ' ' . date('Y/m/d H:i:s') . ' 试图上传木马已被隔离';
                        YLog::txt($string);
                        return 'upload_content_error';
                    } else {
                        @unlink($uploadInfo['source']);
                        $string = YRequest::getip() . ' ' . date('Y/m/d H:i:s') . ' 试图上传木马已被删除';
                        YLog::txt($string);
                        return 'upload_content_error';
                    }

                    break;
                }
            }
        }

        if (in_array(strtolower($uploadInfo['ext']), array(
            'gif',
            'jpg',
            'jpeg',
            'png'
        ))) {
            //check hex
            if (!$this->checkHex($uploadInfo['source'])) {

                if (false) {
                    $name = self::GL_FILE_PATH . time() . '__' . md5(rand(1237777, 45688888)) . '.' . $uploadInfo['ext'];
                    File::moveFile($uploadInfo['source'], $name);
                    @unlink($uploadInfo['source']);
                    $string = YRequest::getip() . ' ' . date('Y/m/d H:i:s') . ' 试图上传木马已被隔离';
                    YLog::txt($string);
                    return 'upload_content_error';
                } else {
                    @unlink($uploadInfo['source']);
                    $string = YRequest::getip() . ' ' . date('Y/m/d H:i:s') . ' 试图上传木马已被删除';
                    YLog::txt($string);
                    return 'upload_content_error';
                }
                return 'input_file_is_Trojan';
            }
            if (!$this->getFileSize($uploadInfo['source'])) {

                @unlink($uploadInfo['source']);
                return 'input_max_file_size';
            }
        }
        return true;
    }

    private function checkHex($file)
    {
        if (file_exists($file)) {
            $resource = fopen($file, 'r');
            $fileSize = filesize($file);
            fseek($resource, 0);
            if ($fileSize > 512) {
                $hexCode = bin2hex(fread($resource, 512));
                fseek($resource, $fileSize - 512);
                $hexCode .= bin2hex(fread($resource, 512));
            } else {
                $hexCode = bin2hex(fread($resource, $fileSize));
            }
            if (preg_match('/(3c25.*?28.*?29.*?253e)|(3c3f.*?28.*?29.*?3f3e)|(3C534352495054)|(2F5343524950543E)|(3C736372697074)|(2F7363726970743E)|(bin2hex)|(AddSlashes)|(Chop)|(Chr)|(rawurldecode)|(str)|(print)|(rootkit)|(preg_replace)|(urldecode)|(gzinflate)|(exec)/is', $hexCode)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function getFileSize($file)
    {
        if (empty($file)) {
            return false;
        } else {
            if (!file_exists($file)) {
                return false;
            } else {
                if (filesize($file) > $this->defaultMaxSize) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    private function readover($fileName, $method = 'rb')
    {

        $data = '';
        if ($handle = @fopen($fileName, $method)) {
            flock($handle, LOCK_SH);
            $data = @fread($handle, filesize($fileName));
            fclose($handle);
        }
        return $data;
    }

    private function getImgSize($srcFile, $srcExt = null)
    {
        empty($srcExt) && $srcExt = strtolower(substr(strrchr($srcFile, '.'), 1));
        $srcdata = array();
        if (function_exists('read_exif_data') && in_array($srcExt, array(
            'jpg',
            'jpeg',
            'jpe',
            'jfif'
        ))) {
            $datatemp = @read_exif_data($srcFile);
            $srcdata['width'] = $datatemp['COMPUTED']['Width'];
            $srcdata['height'] = $datatemp['COMPUTED']['Height'];
            $srcdata['type'] = 2;
            unset($datatemp);
        }
        !$srcdata['width'] && list($srcdata['width'], $srcdata['height'], $srcdata['type']) =
            @getimagesize($srcFile);
        if (!$srcdata['type'] || ($srcdata['type'] == 1 && in_array($srcExt, array(
            'jpg',
            'jpeg',
            'jpe',
            'jfif'
        )))) {
            return false;
        }
        return $srcdata;
    }

    private function escapeStr($string)
    {
        $string = str_replace(array(
            '\0',
            '%00',
            '\r'
        ), '', $string);
        $string = preg_replace(array(
            '/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/',
            '/&(?!(#[0-9]+|[a-z]+);)/is'
        ), array('', '&amp;'), $string);
        $string = str_replace(array('%3C', '<'), '&lt;', $string);
        $string = str_replace(array('%3E', '>'), '&gt;', $string);
        $string = str_replace(array(
            '"',
            "'",
            '\t',
            '  '
        ), array(
            '&quot;',
            '&#39;',
            '    ',
            '&nbsp;&nbsp;'
        ), $string);
        return $string;
    }

    private function escapeDir($dir)
    {
        $dir = str_replace(array(
            "'",
            '#',
            '=',
            '`',
            '$',
            '%',
            '&',
            ';'
        ), '', $dir);
        return trim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir), '/');
    }

    private function escapePath($fileName, $ifCheck = true)
    {
        $tmpname = strtolower($fileName);
        $tmparray = array('://', '\0');
        $ifCheck && $tmparray[] = '..';
        if (str_replace($tmparray, '', $tmpname) != $tmpname) {
            return false;
        }
        return true;
    }

    private function error($errorCode)
    {
        $code = $this->errorCodeArr[$errorCode];
        return array('flag' => 0, 'error' => $this->errcodetomsg[$code]);
    }
}
