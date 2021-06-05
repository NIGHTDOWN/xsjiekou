<?php
/**
* 数据过滤
*/
namespace ng169\tool;


checktop();
class Filter
{

    public static function filterXSS($data)
    {
        if($data==null){
            return $data;
        }
        $data=self::filterScript($data);
      
        
        $data = str_replace(array(
            '&amp;',
            '&lt;',
            '&gt;'), array(
            '&amp;amp;',
            '&amp;lt;',
            '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
            '$1=$2nomozbinding...', $data);

        
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i',
            '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i',
            '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
            '$1>', $data);

        
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
                '', $data);
        } while ($old_data !== $data);

        
        return $data;
    }


    
    public static function filterBadChar($str)
    {
        if (empty($str) or $str == '') {
            return;
        } else {
            $badstring = array(
                "'",
                '"',
                "\"",
                "=",
                "#",
                "$",
                ">",
                "<",
                "\\",
                "/*",
                "%",
                "\0",
                "%00",
                '*');
            $newstring = array(
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '');
            $str = str_replace($badstring, $newstring, $str);
            return trim($str);
        }
    }

    
    public static function stripArray(&$_data)
    {
        if (is_array($_data)) {
            foreach ($_data as $_key => $_value) {
                $_data[$_key] = trim(self::stripArray($_value));
            }
            return $_data;
        } else {
            return stripslashes(trim($_data));
        }
    }

    
    public static function filterSlashes(&$value)
    {
        if (get_magic_quotes_gpc())
            return false; 
        $value = (array )$value;
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                self::filterSlashes($value[$key]);
            } else {
                $value[$key] = addslashes($val);
            }
        }
    }

    
    public static function filterScript($value)
    {
        if (empty($value)) {
            return '';
        } else {
            $value = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i",
                "&111n\\2", $value);
            $value = preg_replace("/<script(.*?)>(.*?)<\/script>/si", "", $value);
            $value = preg_replace("/<iframe(.*?)>(.*?)<\/iframe>/si", "", $value);
            $value = preg_replace("/<object.+<\/object>/isU", '', $value);
            return $value;
        }
    }

    
    public static function filterHtml($value)
    {
        if (empty($value)) {
            return '';
        } else {
            if (function_exists('htmlspecialchars')) {
                return htmlspecialchars($value);
            } else {
                return str_replace(array(
                    "&",
                    '"',
                    "'",
                    "<",
                    ">"), array(
                    "&amp;",
                    "&quot;",
                    "&#039;",
                    "&lt;",
                    "&gt;"), $value);
            }
        }
    }

    
    public static function filterSql($value)
    {
        if (empty($value)) {
            return '';
        } else {
            $sql = array(
                "select",
                'insert',
                "update",
                "delete",
                "\'",
                "\/\*",
                "\.\.\/",
                "\.\/",
                "union",
                "into",
                "load_file",
                "outfile");
            $sql_re = array(
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "");
            return str_ireplace($sql, $sql_re, $value);
        }
    }

    

    public static function filterStr($value)
    {
        if (trim($value) == '') {
            return '';
        } else {
         /*   $value = str_replace("%", "\%", $value);
            $value = trim($value);*/
        
          


       
            $value=htmlspecialchars($value, ENT_NOQUOTES);;
            
            
            
            
           
            return $value;
        }
    }

    
    public static function filterUrl()
    {
        if (preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER'])
            !== preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))
            return false;
        return true;
    }


    
    public static function filterForbidChar($content)
    {
        $new_content = $content;
        $forbidargs = Y::$conf['forbidargs'];
        if (!empty($forbidargs)) {
            $array = explode(',', $forbidargs);
            for ($i = 0; $i < sizeof($array); $i++) {
                $new_content = str_ireplace($array[$i], '', $content);
            }
        }
        return $new_content;
    }

    
    public static function checkExistsForbidChar($content)
    {
        $flag = false;
        $forbidargs = Y::$conf['forbidargs'];
        if (!empty($forbidargs)) {
            $array = explode(',', $forbidargs);
            for ($i = 0; $i < sizeof($array); $i++) {
                
                if (false === strpos(strtolower($content), strtolower($array[$i]))) {
                } else {
                    $flag = true;
                    break;
                }
            }
        }
        return $flag;
    }

    
    public static function checkExistsForbidUserName($username)
    {
        $flag = false;
        $forbidargs = Y::$conf['lockusers'];
        if (!empty($forbidargs)) {
            $array = explode(',', $forbidargs);
            for ($i = 0; $i < sizeof($array); $i++) {
                if (false === strpos(strtolower($username), strtolower($array[$i]))) {
                } else {
                    $flag = true;
                    break;
                }
            }
        }
        return $flag;
    }
}

?>
