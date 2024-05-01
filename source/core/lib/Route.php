<?php


namespace ng169\lib;

checktop();
class Route extends Y{
    
    
    public static function getUri() {
        $path_info = array();
        $uri = self::_requestUri();
        $uri = str_ireplace(
            array('http://', 'index.php/'), 
            array('', ''), 
            $uri
        );
        
        if (strpos($uri, 'index.php')) {
            
        }
        
        else {
            
            if (parent::$conf['urlsuffix'] == 'rewrite') {
                $uri = str_ireplace(
                    array('index.php?'), 
                    array(''), 
                    $uri
                );
                
                if (substr_count(OESOFT_ROOT, '/') > 1) {
                    $uri = str_ireplace(OESOFT_ROOT, '', $uri);
                    $uri = '/'.$uri;
                }
                
                if (false === strpos($uri, '/')) {
                    $uri = $uri.'/';
                }
                
                $uri_array = explode('/', $uri);
                $uri_count = count($uri_array);
                
                $uri_lastitem = @$uri_array[$uri_count-1];
                
                if ($uri_count == 2) {
                    if (!empty($uri_lastitem)) {
                        self::echo404();
                    }
                    else {
                        $path_info = array(
                            'c'=>'index',
                            'a'=>'run',
                            'lastitem'=>$uri_lastitem,
                        );
                    }
                }
                
                else {
                    
                    if ($uri_count > 3) {
                        self::echo404();
                    }
                    else {
                        $c = $uri_array[1];
                        if (empty($uri_lastitem)) {
                            
                        }
                        $path_info = array(
                            'c'=>$c,
                            'lastitem'=>$uri_lastitem,
                        );
                    }
                }
            }
        }
        return $path_info;
    }
    
    
    private static function _requestUri(){
        $_uri = null;
        if (isset($_SERVER['REQUEST_URI'])) {
            $_uri = $_SERVER['REQUEST_URI'];
        }
        else {
            if (isset($_SERVER['argv'])) {
                $_uri = $_SERVER['PHP_SELF'] .(empty($_SERVER['argv']) ? '' : ('?'. $_SERVER['argv'][0]));
            }
            else {
                $_uri = $_SERVER['PHP_SELF'] .(empty($_SERVER['QUERY_STRING']) ? '' : ('?'. $_SERVER['QUERY_STRING']));
            }
        }
        return $_uri;
    }
    
    
    public static function echo404() {
        @header("http/1.1 404 not found");
        @header("status: 404 not found");
        self::_html404();
        die();
    }
    
    private static function _html404() {
        echo("<html>");
        echo("<head>");
        echo("<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">");
        echo("<title>".parent::$conf['sitename']." 错误页面 404 Not Found</title>");
        echo("<style>");
        echo("body {font-family: arial, sans-serif;font-size:14px;}");
        echo("h1 {font-size:22px;}");
        echo("ul {margin:1em;}");
        echo("li {line-height:1.6em;font-family:宋体;}");
        echo("a {color:#00f;}");
        echo("</style>");
        echo("</head>");
        echo("<body text=#000000 bgcolor=#ffffff>");
        echo("<table border=0 cellpadding=2 cellspacing=0 width=100%>");
        echo("  <tr>");
        echo("      <td rowspan=3 width=1% nowrap><b>".parent::$conf['sitename']."</b>");
        echo("      <td>&nbsp;</td>");
        echo("  </tr>");
        echo("  <tr>");
        echo("      <td bgcolor=#3366cc><font face=arial,sans-serif color=#ffffff><b>404 Error</b></td>");
        echo("  </tr>");
        echo("  <tr>");
        echo("      <td>&nbsp;</td>");
        echo("  </tr>");
        echo("</table>");
        echo("<blockquote>");
        echo("<H1>没有找到您要访问的页面</H1>");
        echo("<ol>");
        echo("    <ul type='square'>");
        echo("        <li><a href='".parent::$conf['siteurl']."'>返回首页</a></li>");
        echo("    </ul>");
        echo("</ol>");
        echo("</body>");
        echo("</html>");
    }
    
}
?>
