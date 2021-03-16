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
        $morePages = true;

        // init curl and config options
        $ch = curl_init();

        $resp = self::execCurl($ch, $url);

        // split headers and body
        $responseHeaderBodyArray = self::splitResponse($ch, $resp);
        $headersArrayIndexed = explode("\r\n", $responseHeaderBodyArray['responseHeaders']);
        $headersArrayKeyValuePairs = self::createKeyValueHeadersArray($headersArrayIndexed);
        $dec = json_decode($responseHeaderBodyArray['responseBody'], true);
        $dataArray = $dec;
        $ret = $dataArray;

        // get more data if there are more pages and add to data array
        if (array_key_exists('link', $headersArrayKeyValuePairs)) {
            while ($morePages) {
                $nextUrl = '';
                $lastUrl = '';

                // retrieve next and last page url
                $linkPaginationArray = explode(',', $headersArrayKeyValuePairs['link']);
                foreach ($linkPaginationArray as $el) {
                    // find next url
                    if (preg_match('/next/', $el)) {
                        preg_match('/(?<=<)(.*)(?=>)/', $el, $nextUrlArray);
                        $nextUrl = $nextUrlArray[0];
                    }
                    if (preg_match('/last/', $el)) {
                        preg_match('/(?<=<)(.*)(?=>)/', $el, $lastUrlArray);
                        $lastUrl = $lastUrlArray[0];
                    }
                }
                // get next page
                $resp = self::execCurl($ch, $nextUrl);
                $responseHeaderBodyArray = self::splitResponse($ch, $resp);
                $headersArrayIndexed = explode("\r\n", $responseHeaderBodyArray['responseHeaders']);
                $headersArrayKeyValuePairs = self::createKeyValueHeadersArray($headersArrayIndexed);
                $dec = json_decode($responseHeaderBodyArray['responseBody'], true);
                $dataArray = array_merge($dataArray, $dec);
                $ret = $dataArray;

                // Break loop when no more pages listed in the HTTP header's link field
                if (array_key_exists('link', $headersArrayKeyValuePairs) && !preg_match('/next/',
                        $headersArrayKeyValuePairs['link'])) {
                    $morePages = false;
                }
            }
        }

        curl_close($ch);

        if ($what === 'count') {
            $ret = count($dataArray);
        }

        return $ret;
    }

    public static function getToken()
    {
        return file_get_contents(__DIR__ . '/../../.env');
    }

    public static function createKeyValueHeadersArray($indexedArray)
    {
        $headersArrayKeyValuePairs = array();

        foreach ($indexedArray as $key => $value) {
            if (!isset($value) || $value === "") {
                continue;
            }
            if ($key === 0) {
                $headersArrayKeyValuePairs['HTTP-status'] = $value;
                continue;
            }
            $matches = explode(':', $value, 2);
            $headersArrayKeyValuePairs["$matches[0]"] = $matches[1];
        }

        return $headersArrayKeyValuePairs;
    }

    private static function execCurl($ch, $url)
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: ABC',
                'Authorization: token ' . self::getToken(),
                'Accept: application/json'
            ],
        ];


        curl_setopt_array($ch, $options);
        return $resp = curl_exec($ch);
    }

    private static function splitResponse($ch, $resp)
    {
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($resp, 0, $header_size);
        $body = substr($resp, $header_size);
        return ['responseHeaders' => $headers, 'responseBody' => $body];
    }
}