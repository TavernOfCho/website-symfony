<?php

namespace App\Utils;

use App\Security\Core\User\BnetOAuthUser;
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

    /* Security */

    /**
     * @param array $credentials
     * @return BnetOAuthUser|null
     */
    public function generateBnetOauthUser(array $credentials)
    {
        if (!isset($credentials['_username'], $credentials['_password'])) {
            return null;
        }

        if (null === $response = $this->fetchUserSecurity($credentials['_username'], $credentials['_password'])) {
            return null;
        }

        $data = $response['hydra:member'];
        if (is_array($data) && count($data) > 0) {
            $user = new BnetOAuthUser();
            $data = $data[0];

            $user->setUsername($data['username'])
                ->setBnetAccessToken($data['bnetAccessToken'])
                ->setBnetBattletag($data['bnetBattletag'])
                ->setBnetId($data['bnetId'])
                ->setBnetSub($data['bnetSub'])
                ->setPassword($data['password'])
                ->setRoles($data['roles'])
                ->setEnabled($data['enabled']);

            if (false === $this->jwtConnect($user, $credentials)) {
                return null;
            }

            return $user;
        }

        return null;
    }

    /**
     * @param array $credentials
     * @return BnetOAuthUser|null
     */
    public function createAccount(array $credentials)
    {
        if (!isset($credentials['username'], $credentials['email'], $credentials['plainPassword'])) {
            return null;
        }

        $response = $this->fetchUserSecurity($credentials['username'], $credentials['plainPassword']);

        //Existing user
        if (null !== $response && isset($response['hydra:member']) && count($response['hydra:member']) > 0) {
            return null;
        }

        $response = $this->getClient()->request('POST', '/register', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'username' => $credentials['username'],
                'password' => $credentials['plainPassword'],
            ],
        ]);


        if (!$this->isStatusValid($response)) {
            return null;
        }

        return $this->generateBnetOauthUser([
            '_username' => $credentials['username'],
            '_password' => $credentials['plainPassword'],
        ]);
    }

    /**
     * @param BnetOAuthUser $user
     * @param array $credentials
     * @return bool
     */
    protected function jwtConnect(BnetOAuthUser $user, array $credentials)
    {
        return $this->sendToken($user, $credentials);
    }

    /**
     * @param BnetOAuthUser $user
     * @return bool
     */
    public function jwtRefreshToken(BnetOAuthUser $user)
    {
        return $this->sendToken($user);
    }

    /**
     * @param BnetOAuthUser $user
     * @param array|null $credentials
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendToken(BnetOAuthUser $user, array $credentials = null)
    {
        if (is_array($credentials)) {
            $response = $this->getClient()->request('POST', '/login_check', [
                'json' => [
                    'username' => $credentials['_username'],
                    'password' => $credentials['_password'],
                ]
            ]);
        } else {
            $response = $this->getClient()->request('POST', '/token/refresh', [
                'form_params' => ['refresh_token' => $user->getJwtRefreshToken()],
            ]);
        }

        if (!$this->isStatusValid($response)) {
            return false;
        }

        $response = json_decode($response->getBody()->getContents(), true);

        $user
            ->setJwtToken($response['token'])
            ->setJwtRefreshToken($response['refresh_token'])
            ->resetTokenExpiration();

        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed|null
     */
    public function fetchUserSecurity(string $username, string $password)
    {
        $response = $this->getClient()->request('GET', '/users/security', [
            'query' => [
                'username' => $username,
                'password' => $password,
                'api_key' => $this->api_key,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        if (!$this->isStatusValid($response)) {
            return null;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

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
