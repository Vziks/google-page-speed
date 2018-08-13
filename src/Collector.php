<?php

namespace Vziks\PageSpeed;

use Vziks\PageSpeed\Exception\CollectorException;

/**
 * Class Collector
 * Project google-page-speed
 *
 * @author Anton Prokhorov <vziks@live.ru>
 */
class Collector
{
    const STRATEGY_MOBILE = 'mobile';
    const STRATEGY_DESKTOP = 'desktop';

    const API_URL = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=%s&strategy=%s';
    const WEB_URL = 'https://developers.google.com/speed/pagespeed/insights/?url=%s';

    const ERROR_REASON_INVALID_KEY = 'keyInvalid';

    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var integer
     */
    private $score;

    /**
     * Collector constructor.
     * @param $apiKey
     * @param $url
     * @param $strategy
     * @param $score
     * @throws CollectorException
     */
    public function __construct($apiKey, $url, $strategy, $score)
    {
        if (empty($apiKey)) {
            throw new CollectorException('API KEY not defined!');
        }
        if (empty($url)) {
            throw new CollectorException('URL not defined!');
        }

        if (!in_array($strategy, [self::STRATEGY_DESKTOP, self::STRATEGY_MOBILE])) {
            throw new CollectorException(sprintf('Your strategy is not correct(%s), you need to use either %s or %s', $strategy, self::STRATEGY_DESKTOP, self::STRATEGY_MOBILE));
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new CollectorException(sprintf('%s is not a valid URL', $url));
        }

        $this->strategy = $strategy;
        $this->apiKey = $apiKey;
        $this->score = $score;
        $this->url = $url;
    }

    /**
     * @param $uri
     * @param $strategy
     * @param bool $excludeThirdParty
     * @return string
     */
    private function getEndpoint($uri, $strategy, $excludeThirdParty = false)
    {
        $endpoint = sprintf(self::API_URL, urlencode((string)$uri), $strategy);
        if ($this->apiKey) {
            $endpoint .= '&key=' . $this->apiKey;
        }
        if ($excludeThirdParty) {
            $endpoint .= '&filter_third_party_resources=true';
        }
        return $endpoint;
    }

    /**
     * @param $uri
     * @param bool $excludeThirdParty
     * @return InfoGraphic
     * @throws CollectorException
     */
    public function getGooglePageSpeedInfo($uri, $excludeThirdParty = false)
    {
        try {
            $endpoint = $this->getEndpoint($uri, $this->strategy, $excludeThirdParty);
            $response = $this->getClientData($endpoint);
        } catch (\Exception $e) {
            throw new CollectorException($e->getMessage());
        }
        return new InfoGraphic($response, $this->strategy, $this->score);
    }

    /**
     * @param $endpoint
     * @return mixed
     * @throws CollectorException
     */
    public function getClientData($endpoint)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        $curlResponse = curl_exec($curl);
        curl_close($curl);

        $jsonResult = json_decode($curlResponse, true);

        if (array_key_exists('error', $jsonResult)) {
            if ($jsonResult['error']['errors'][0]['reason'] == self::ERROR_REASON_INVALID_KEY) {
                throw new CollectorException('Invalid API Key.');
            }
        }

        return $jsonResult;
    }
}
