<?php

namespace MediaWiki\Services;

use Psr\Log\InvalidArgumentException;

class Pages extends Service
{
    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * @param string $language
     * @param string $continue
     * @param string $apcontinue
     * @param array $extParameters
     * 
     * @return array
     */
    public function getList($language, $continue = null, $apcontinue = null, $extParameters = [])
    {
        $parameters = [
            'list' => 'allpages',
        ];

        if ($continue !== null) {
            $parameters['continue'] = $continue;
            $parameters['apcontinue'] = $apcontinue;
        }

        $parameters = array_merge($parameters, $extParameters);

        $response = $this->api($language)->query($parameters);

        if (array_key_exists('continue', $response)) {
            $continue = $response['continue']['continue'];
            $apcontinue = $response['continue']['apcontinue'];
        } else {
            $continue = null;
            $apcontinue = null;
        }

        return [
            'list' => $response['query']['allpages'],
            'continue' => $continue,
            'apcontinue' => $apcontinue,
        ];
    }

    /**
     * @param string $language
     * @param string $title
     * @param array $properties
     * @param array $extParameters
     * 
     * @return array
     */
    public function loadPage($language, $title, $properties = null, $extParameters = [])
    {
        if ($title === '') {
            throw new InvalidArgumentException(sprintf('Title must not be empty (%s)', $language));
        }

        if (is_array($properties)) {
            $properties = implode('|', $properties);
        }

        $parameters = [
            'titles' => $title,
            'prop' => $properties,
        ];

        $parameters = array_merge($parameters, $extParameters);

        $response = $this->api($language)->query($parameters);

        $page = array_shift($response['query']['pages']);

        return $page;
    }

    /**
     * @param string $language
     * @param string $title
     * @param string $content
     * @param array $extParameters
     * 
     * @return array
     */
    public function savePage($language, $title, $content, $extParameters = [])
    {
        $token = $this->getCsrfToken($language);

        $parameters = [
            'action' => 'edit',
            'title' => $title,
            'text' => $content,
            'bot' => true,
            'nocreate' => true,
            'token' => $token,
        ];

        $parameters = array_merge($parameters, $extParameters);

        return $this->api($language)->request('POST', $parameters);
    }

    /**
     * @param string $language
     * @param string $title
     * @param string|array $properties
     * @param array $extParameters
     * 
     * @return array
     */
    public function parse($language, $title, $properties = [], $extParameters = [])
    {
        $properties = is_array($properties) ? implode('|', $properties) : $properties;

        $parameters = [
            'action' => 'parse',
            'page' => $title,
            'disableeditsection' => true,
            'disablelimitreport' => true,
        ];

        if ($properties !== '') {
            $parameters['prop'] = $properties;
        }

        $parameters = array_merge($parameters, $extParameters);

        return $this->api($language)->request('POST', $parameters);
    }

    /**
     * @param string $language
     * 
     * @return string
     */
    protected function getCsrfToken($language)
    {
        if (!array_key_exists($language, $this->tokens)) {
            $parameters = [
                'action' => 'query',
                'meta' => 'tokens',
                'type' => 'csrf',
            ];

            $response = $this->api($language)->request('POST', $parameters);

            $this->tokens[$language] = $response['query']['tokens']['csrftoken'];
        }

        return $this->tokens[$language];
    }
}
