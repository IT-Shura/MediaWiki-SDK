<?php

namespace MediaWiki\Storage;

class FileStore implements StorageInterface
{
    /**
     * The file cache directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * Constructor.
     *
     * @param string  $directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
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
        $array = $this->getPayload($key);

        return $array['data'] === null ? $default : $array['data'];
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param string $key
     *
     * @return array
     */
    protected function getPayload($key)
    {
        $path = $this->path($key);

        // If the file doesn't exists, we obviously can't return the cache so we will
        // just return null. Otherwise, we'll get the contents of the file and get
        // the expiration UNIX timestamps from the start of the file's contents.
        if (!file_exists($path)) {
            return ['data' => null, 'time' => null];
        }

        $expire = substr(
            $contents = file_get_contents($path), 0, 10
        );

        // If the current time is greater than expiration timestamps we will delete
        // the file and return null. This helps clean up the old files and keeps
        // this directory much cleaner for us as old files aren't hanging out.
        if (time() >= $expire) {
            $this->forget($key);

            return ['data' => null, 'time' => null];
        }

        $data = unserialize(substr($contents, 10));

        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on the cache. We'll round this out.
        $time = ceil(($expire - time()) / 60);

        return compact('data', 'time');
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
        $value = $this->expiration($minutes).serialize($value);

        $this->createCacheDirectory($path = $this->path($key));

        file_put_contents($path, $value, LOCK_EX);
    }

    /**
     * Create the file cache directory if necessary.
     *
     * @param string $path
     */
    protected function createCacheDirectory($path)
    {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
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
        $raw = $this->getPayload($key);

        $int = ((int) $raw['data']) + $value;

        $this->put($key, $int, (int) $raw['time']);

        return $int;
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
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
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
        $file = $this->path($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        if (is_dir($this->directory)) {
            $files = scandir($this->directory);

            $directories = array_filter($files, function ($file) {
                return is_dir($this->directory.'/'.$file);
            });

            foreach ($directories as $directory) {
                $this->removeDirectory($directory);
            }
        }
    }

    protected function removeDirectory($path)
    {
        $files = glob($path.'/*');

        foreach ($files as $file) {
            is_dir($file) ? removeDirectory($file) : unlink($file);
        }

        rmdir($path);

        return;
    }

    /**
     * Get the full path for the given cache key.
     *
     * @param string $key
     * 
     * @return string
     */
    protected function path($key)
    {
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

        return $this->directory.'/'.implode('/', $parts).'/'.$hash;
    }

    /**
     * Get the expiration time based on the given minutes.
     *
     * @param int $minutes
     * 
     * @return int
     */
    protected function expiration($minutes)
    {
        $time = time() + ($minutes * 60);

        if ($minutes === 0 || $time > 9999999999) {
            return 9999999999;
        }

        return $time;
    }

    /**
     * Get the working directory of the cache.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}
