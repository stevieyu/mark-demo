<?php

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
//use Symfony\Contracts\Cache\ItemInterface;

if (!function_exists('cache')) {
    /**
     * @return mixed|FilesystemAdapter
     * @throws \Psr\Cache\InvalidArgumentException
     */
    function cache() {
        $arguments = func_get_args();
        $cache = new FilesystemAdapter('', 0, '/tmp/storage/cache');

        if (empty($arguments)) {
            return $cache;
        }

        if (is_string($arguments[0])) {
            return $cache->get(...$arguments);
        }

        if (! is_array($arguments[0])) {
            throw new Exception(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        return $cache->put(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
    }
}