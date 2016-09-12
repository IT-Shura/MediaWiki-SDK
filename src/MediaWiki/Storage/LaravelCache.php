<?php

namespace MediaWiki\Storage;

use Illuminate\Cache\CacheManager;

class LaravelCache implements StorageInterface
{
    /**
     * The file cache directory.
     *
     * @var Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param Illuminate\Cache\CacheManager $cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string|array $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     */
    public function put($key, $value, $minutes)
    {
        $this->cache->put($key, $value, $minutes);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed  $value
     * 
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->cache->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed  $value
     * 
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->cache->decrement($key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value)
    {
        $this->cache->forever($key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * 
     * @return bool
     */
    public function forget($key)
    {
        return $this->cache->forget($key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        $this->cache->flush();
    }
}
