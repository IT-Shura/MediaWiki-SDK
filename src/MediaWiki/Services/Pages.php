<?php

namespace MediaWiki\Services;

class Pages extends Service
{
    protected $tokens = [];

    public function getList($language, $continue = null, $apcontinue = null, $extParameters = [)
    {
        $parameters = [
            'list' => 'allpages',
        ];

        if ($continue !== null) {
            $parameters['continue'] = $continue;
            $parameters['apcontinue'] = $apcontinue;
        }

        $parameters = array_merge($parameters, $extParameters);

        $response = $this->project->api($language)->query($parameters);

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

    public function savePage($language, $title, $content)
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

        return $this->api($language)->request('POST', $parameters);
    }

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
