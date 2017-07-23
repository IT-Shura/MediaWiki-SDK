<?php


namespace MediaWiki\Services;


class SiteInfo
{
    /**
     * @return string
     */
    public function getVersion($language)
    {
        $response = $this->api($language)->query([
            'meta' => 'siteinfo',
            'continue' => '',
        ]);

        $segments = explode(' ', $response['query']['general']['generator']);

        return $segments[1];
    }
}