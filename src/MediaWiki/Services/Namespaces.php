<?php

namespace MediaWiki\Services;

class Namespaces extends Service
{
    /**
     * Retrieves list of namespaces.
     * 
     * @param string $language
     * 
     * @return array
     */
    public function getList($language)
    {
        $parameters = [
            'meta' => 'siteinfo',
            'siprop'=> 'namespaces',
            'formatversion' => 2,
        ];

        $response = $this->api($language)->query($parameters);

        return $response['query']['namespaces'];
    }
}
