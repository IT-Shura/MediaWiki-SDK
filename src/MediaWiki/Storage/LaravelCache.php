<?php

namespace MediaWiki\Storage;

use Illuminate\Cache\Repository;

class LaravelCache implements StorageInterface
{
    /**
     * The file cache directory.
     *
     * @var Illuminate\Cache\Repository
     */
    protected $cache;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param Illuminate\Cache\Repository $cache
     * @param string $prefix
     */
    public function __construct(Repository $cache, $prefix = '')
    {
        $this->cache = $cache;
        $this->prefix = $prefix;
    }

    /**
     * @return Illuminate\Cache\Repository
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
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
        return $this->cache->get($this->prefix.$key, $default);
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
        $this->cache->put($this->prefix.$key, $value, $minutes);
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
        return $this->cache->increment($this->prefix.$key, $value);
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
        return $this->cache->decrement($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value)
    {
        $this->cache->forever($this->prefix.$key, $value);
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
        return $this->cache->forget($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        $this->cache->flush();
    }
}
