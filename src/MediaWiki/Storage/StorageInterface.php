<?php

namespace MediaWiki\Storage;

interface StorageInterface
{
    /**
     * Retrieve an item from the storage by key.
     *
     * @param string|array $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Store an item in the storage for a given number of minutes.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     */
    public function put($key, $value, $minutes);

    /**
     * Store an item in the storage indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value);

    /**
     * Remove an item from the storage.
     *
     * @param string $key
     * 
     * @return bool
     */
    public function forget($key);

    /**
     * Remove all items from the storage.
     */
    public function flush();
}
