<?php

namespace MediaWiki\Bot;

use MediaWiki\Api\Exceptions\ApiException;
use RuntimeException;

trait AuthTrait
{
    /**
     * @param string $language
     * @param string|null $domain
     * 
     * @return bool
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
            throw new RuntimeException(sprintf('Username for "%s" wiki not defined (%s)', $language, $this->project->getName()));
        }

        $username = $usernames[$language];

        while (true) {
            $password = $this->secret(sprintf('Please, enter the password for "%s" wiki', $language));

            try {
                return $api->login($username, $password, $domain);
            } catch (ApiException $exception) {
                if ($exception->getMessage() === 'NotExists') {
                    $message = sprintf('User with name "%s" does not exists in "%s" wiki', $username, $language);

                    throw new RuntimeException($message);
                }

                if ($exception->getMessage() === 'NoName') {
                    throw new RuntimeException(sprintf('Username can not be empty (%s)', $language));
                }

                if ($exception->getMessage() === 'Illegal') {
                    throw new RuntimeException(sprintf('Username is invalid (%s)', $language));
                }

                if ($exception->getMessage() === 'EmptyPass') {
                    $this->error(sprintf('Password can not be empty (%s)', $language));

                    continue;
                }

                if ($exception->getMessage() === 'WrongPass') {
                    $this->error('Wrong password. Please try again');

                    continue;
                }

                if ($exception->getMessage() === 'UserBlocked') {
                    $this->error(sprintf('User "%s" is blocked (%s)', $username, $language));

                    continue;
                }

                throw $exception;
            }
        }
    }

    /**
     * @param string|array $languages
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
     * @return bool
     */
    public function isLoggedIn($language = null)
    {
        return $this->project->api($language)->isLoggedIn();
    }
}
