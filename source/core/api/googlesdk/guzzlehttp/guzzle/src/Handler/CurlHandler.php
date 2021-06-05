<?php

namespace GuzzleHttp\Handler;

use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP handler that uses cURL easy handles as a transport layer.
 *
 * When using the CurlHandler, custom curl options can be specified as an
 * associative array of curl option constants mapping to values in the
 * **curl** key of the "client" key of the request.
 */
class CurlHandler
{
    /** @var CurlFactoryInterface */
    private $factory;

    /**
     * Accepts an associative array of options:
     *
     * - factory: Optional curl factory used to create cURL handles.
     *
     * @param array $options Array of options to use with the handler
     */
    public function __construct(array $options = [])
    {
        $this->factory = isset($options['handle_factory'])
            ? $options['handle_factory']
            : new CurlFactory(3);
    }

    public function __invoke(RequestInterface $request, array $options)
    {
        if (isset($options['delay'])) {
            usleep($options['delay'] * 1000);
        }

        $easy = $this->factory->create($request, $options);
        //   这里可以配置代理调试
        // curl_setopt($easy->handle, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($easy->handle, CURLOPT_PROXY, '192.168.2.106');
        // curl_setopt($easy->handle, CURLOPT_PROXYPORT, '8888');
        // curl_setopt($easy->handle, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($easy->handle, CURLOPT_SSL_VERIFYHOST, 2);
          //   这里可以配置代理调试
        curl_exec($easy->handle);
        $easy->errno = curl_errno($easy->handle);

        return CurlFactory::finish($this, $easy, $this->factory);
    }
}
