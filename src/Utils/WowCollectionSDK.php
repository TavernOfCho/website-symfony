<?php

namespace App\Utils;

use App\Security\Core\User\BnetOAuthUser;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use GuzzleHttp\Client;

class WowCollectionSDK
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var string $api_url */
    private $api_url;

    /** @var string $api_key */
    private $api_key;

    /** @var Client $client */
    private $client;

    /**
     * ApiSDK constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param string $api_url
     * @param string $api_key
     */
    public function __construct(TokenStorageInterface $tokenStorage, string $api_url, string $api_key)
    {
        $this->tokenStorage = $tokenStorage;
        $this->api_url = $api_url;
        $this->api_key = $api_key;
        $this->client = new Client([
            'verify' => false,
            'base_uri' => $api_url,
            'timeout' => 30,
        ]);
    }

    /* API Endpoints */
    /**
     * TODO fetch all pages, put to cache the result
     * @return array
     */
    public function getRealms()
    {
        $response = $this->client->request('GET', '/realms', [
            'headers' => $this->getBasicJsonHeader()
        ]);

        if (!$this->isStatusValid($response)) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $realms = $data['hydra:member'];

        return array_combine(array_column($realms, 'slug'), array_column($realms, 'name'));
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

        $response = $this->client->request('POST', '/register', [
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
            $response = $this->client->request('POST', '/login_check', [
                'json' => [
                    'username' => $credentials['_username'],
                    'password' => $credentials['_password'],
                ]
            ]);
        } else {
            $response = $this->client->request('POST', '/token/refresh', [
                'body' => sprintf('refresh_token="%s"', $user->getJwtRefreshToken()),
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
        $response = $this->client->request('GET', '/users/security', [
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
     * @return BnetOAuthUser|null|object|string
     */
    private function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * @param Response $response
     * @return bool
     */
    private function isStatusValid(Response $response)
    {
        return $response->getStatusCode() === 200;
    }

    /**
     * @return array
     */
    private function getBasicJsonHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/ld+json',
            'Authorization' => 'Bearer ' . $this->getUser()->getJwtToken()
        ];
    }
}
