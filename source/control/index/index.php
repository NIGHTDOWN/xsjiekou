<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;

checktop();



class index extends indexbase
{

    protected $noNeedLogin = ['*'];


    public function control_run()
    {
        $endpoint = [[1, 4], [2, 3]];
        $newbook = M('index', 'im')->booknew(1, $this->langid, 6);
        $newcartoon = M('index', 'im')->booknew(2, $this->langid, 6);
        $endbook = M('index', 'im')->end(1, $this->langid, 4);
        $endcartoon = M('index', 'im')->end(2, $this->langid, 4);
        $hotbook = M('index', 'im')->hot(1, $this->langid, 6);
        $hotcartoon = M('index', 'im')->hot(2, $this->langid, 6);
        foreach ($endbook as $k => $v) {
            if (in_array($k + 1, $endpoint[0])) {
                $endbook[$k]['big'] = 1;
            } else {
                $endbook[$k]['big'] = 0;
            }
        }
        foreach ($endcartoon as $k => $v) {

            if (in_array($k + 1, $endpoint[1])) {
                $endcartoon[$k]['big'] = 1;
            } else {
                $endcartoon[$k]['big'] = 0;
            }
        }

        $this->view(null, ['newbook' => $newbook, 'newcartoon' => $newcartoon, 'endbook' => $endbook, 'endcartoon' => $endcartoon, 'hotbook' => $hotbook, 'hotcartoon' => $hotcartoon]);
    }
    public function control_red()
    {
        // $cache =  Rediscache::getRedis();
        // $cache->set('bbb', '111');
        // d($cache->get('bbb'));
        // \htmlspecialchars_encode('sss');

        // $s = htmlspecialchars('305e312b3029060355040b0c224372656174656420627920687474703a2f2f7777772e666964646c6572322e636f6d31153013060355040a0c0c444f5f4e4f545f54525553543118301606035504030c0f2a2e61707073666c7965722e636f6d');
        // $s = md5('305e312b3029060355040b0c224372656174656420627920687474703a2f2f7777772e666964646c6572322e636f6d31153013060355040a0c0c444f5f4e4f545f54525553543118301606035504030c0f2a2e61707073666c7965722e636f6d');
        // $string = '1d2a09653432653a3531353838262a0f686b3d0a10355c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5cde1b3ca271e7adea037f899abe900d6c8f01f0ec';

        // d(md5($string));
        // d(2222);

    }
}
