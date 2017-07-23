<?php

namespace Tests\Stubs;

use MediaWiki\Project\Project;

class ProjectExample extends Project
{
    /**
     * @var string
     */
    protected $name = 'foo';

    /**
     * @var string
     */
    protected $title = 'Foo';

    /**
     * @var string
     */
    protected $defaultLanguage = 'en';

    /**
     * @return array
     */
    public static function getApiUrls()
    {
        return [
            'en' => 'https://en.wikipedia.org/w/api.php',
            'ru' => 'https://ru.wikipedia.org/w/api.php',
        ];
    }

    /**
     * @return array
     */
    public static function getApiUsernames()
    {
        return [
            'en' => 'FooBot',
            'ru' => 'FooBot',
        ];
    }
}
