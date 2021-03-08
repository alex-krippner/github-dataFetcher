<?php

namespace Mon\Oversight\inc;

// TODO: Handle pagination from gitHub

class Services
{

    /**
     * @param string $url github REST API with parameters
     * @param string $what 'count' or an empty string
     * @return int|array returns an array of plugin repos or a count of some repo data (eg. the count of then number of issues)
     */

    public static function getApiData($url, $what)
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => [
                'User-Agent: ABC',
                'Authorization: token ' . self::getToken(),
                'Accept: application/json'
            ],
        ];

        curl_setopt_array($ch, $options);
        $resp = curl_exec($ch);
        $dec = json_decode($resp, true);
        $ret = $dec;
        curl_close($ch);

        if ($what === 'count') {
            $ret = count($dec);
        }

        return $ret;
    }

    public static function getToken()
    {
        return file_get_contents(__DIR__ . '/../../.env');
    }
}