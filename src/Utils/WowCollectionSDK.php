<?php

namespace App\Utils;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Psr\Cache\CacheItemPoolInterface;

class WowCollectionSDK
{
    /** @var string $api_url */
    private $api_url;

    /** @var string $api_key */
    private $api_key;

    /** @var Client $client */
    private $client;

    /** @var CacheItemPoolInterface $cacheManager */
    private $cacheManager;

    const LONG_TIME = 86400; //1 day to seconds
    const SHORT_TIME = 600; //10 minutes to seconds

    /**
     * ApiSDK constructor.
     * @param Client $guzzleClient
     * @param CacheItemPoolInterface $cacheManager
     * @param string $api_url
     * @param string $api_key
     */
    public function __construct(Client $guzzleClient, CacheItemPoolInterface $cacheManager, string $api_url, string $api_key)
    {
        $this->client = $guzzleClient;
        $this->cacheManager = $cacheManager;
        $this->api_url = $api_url;
        $this->api_key = $api_key;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->api_key;
    }

    /* Security */

    /**
     * @param Response $response
     * @return bool
     */
    public function isStatusValid(Response $response)
    {
        return $response->getStatusCode() === 200;
    }

    /**
     * @param callable $callback
     * @param string $itemName
     * @param int $expiresAfter
     * @return mixed
     */
    public function cacheHandle(callable $callback, string $itemName, int $expiresAfter = self::SHORT_TIME)
    {
        $cacheContent = $this->cacheManager->getItem($itemName);
        if ($cacheContent->isHit()) {
            return $cacheContent->get();
        }

        if (null !== $result = $callback()) {
            $this->cacheManager->save($cacheContent->expiresAfter($expiresAfter)->set($result));
        }

        return $result;
    }


}
