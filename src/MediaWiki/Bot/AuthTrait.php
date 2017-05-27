<?php

namespace MediaWiki\Bot;

use MediaWiki\Api\Exceptions\ApiException;
use RuntimeException;

trait AuthTrait
{
    /**
     * @param string|null $language
     * @param string|null $domain
     * 
     * @return bool
     *
     * @throws RuntimeException if username for specified language is not defined
     * @throws ApiException
     */
    public function login($language = null, $domain = null)
    {
        $language = $language === null ? $this->project->getDefaultLanguage() : $language;

        $api = $this->project->api($language);

        if ($api->isLoggedIn()) {
            return true;
        }

        $usernames = $this->project->getApiUsernames();

        if (!array_key_exists($language, $usernames)) {
            throw new RuntimeException(sprintf('Username for "%s" wiki is not defined (%s)', $language, $this->project->getName()));
        }

        $username = $usernames[$language];

        while (true) {
            $password = $this->secret(sprintf('Please, enter the password for "%s" wiki', $language));

            if ($password === '') {
                $this->error(sprintf('Password can not be empty'));

                continue;
            }

            return $api->login($username, $password, $domain);
        }
    }

    /**
     * @param string|array|null $languages
     */
    public function logout($languages = null)
    {
        if (is_string($languages)) {
            if ($languages === '*') {
                $languages = $this->project->getApiCollection()->getLanguages();
            } else {
                $languages = explode(',', $languages);
            }
        }

        if ($languages === null) {
            $languages = [$this->project->getDefaultLanguage()];
        }

        foreach ($languages as $language) {
            $this->project->api($language)->logout();
        }
    }

    /**
     * @param string|null $language
     *
     * @return bool
     */
    public function isLoggedIn($language = null)
    {
        return $this->project->api($language)->isLoggedIn();
    }
}
