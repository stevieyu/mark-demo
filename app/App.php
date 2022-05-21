<?php

namespace App;

use Mark\App as BaseApp;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

class App extends BaseApp
{
    /**
     * @param TcpConnection $connection
     * @param Request $request
     * @return null
     */
    public function onMessage($connection, $request)
    {
        $_SERVER = array_merge($_SERVER, [
            'QUERY_STRING'         => $request->get(),
            'REQUEST_METHOD'       => $request->method(),
            'REQUEST_URI'          => $request->uri(),
            'SERVER_NAME'          => $request->host(true),
            'HTTP_HOST'            => $request->host(),
            'HTTP_USER_AGENT'      => $request->header('user-agent'),
            'HTTP_ACCEPT'          => $request->header('accept'),
            'HTTP_ACCEPT_LANGUAGE' => $request->header('accept-language'),
            'HTTP_ACCEPT_ENCODING' => $request->header('accept-encoding'),
            'HTTP_COOKIE'          => $request->header('cookie'),
            'HTTP_CONNECTION'      => $request->header('connection'),
            'CONTENT_TYPE'         => $request->header('content-type'),
            'REMOTE_ADDR'          => $connection->getRemoteIp(),
            'REMOTE_PORT'          => $connection->getRemotePort(),
        ]);

        return parent::onMessage($connection, $request);
    }

    public function start()
    {
        $this->dispatcher = \FastRoute\cachedDispatcher(function(\FastRoute\RouteCollector $r) {
            foreach ($this->routeInfo as $method => $callbacks) {
                foreach ($callbacks as $info) {
                    $r->addRoute($method, $info[0], $info[1]);
                }
            }
        }, [
            'cacheFile' => '/tmp/route.cache', /* required */
        ]);

        \Workerman\Worker::runAll();
    }
}