<?php

namespace Tests\Stubs;

use MediaWiki\Bot\Project;

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
    public function getApiUrls()
    {
        return [
            'en' => 'http://en.wikipedia.org/w/api.php',
            'ru' => 'http://ru.wikipedia.org/w/api.php',
        ];
    }

    /**
     * @return array
     */
    public function getApiUsernames()
    {
        return [
            'en' => 'FooBot',
            'ru' => 'FooBot',
        ];
    }
}
