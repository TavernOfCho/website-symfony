<?php

namespace App\Manager;

use App\Security\Core\User\BnetOAuthUser;
use App\Utils\WowCollectionSDK;

class UserManager
{
    /** @var WowCollectionSDK $wowCollectionSDK */
    private $wowCollectionSDK;

    /**
     * UserManager constructor.
     * @param WowCollectionSDK $wowCollectionSDK
     */
    public function __construct(WowCollectionSDK $wowCollectionSDK)
    {
        $this->wowCollectionSDK = $wowCollectionSDK;
    }

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

        $response = $this->fetchUserSecurity($credentials['username'], $credentials['plainPassword'], false);

        //Existing user
        if (null !== $response && isset($response['hydra:member']) && count($response['hydra:member']) > 0) {
            return null;
        }

        $response = $this->wowCollectionSDK->getClient()->request('POST', '/register', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'username' => $credentials['username'],
                'password' => $credentials['plainPassword'],
            ],
        ]);


        if (!$this->wowCollectionSDK->isStatusValid($response)) {
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
    private function jwtConnect(BnetOAuthUser $user, array $credentials)
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
     * @param string $username
     * @param string $password
     * @param bool $throwError
     * @return mixed|null
     */
    private function fetchUserSecurity(string $username, string $password, bool $throwError = true)
    {
        $response = $this->wowCollectionSDK->getClient()->request('GET', '/users/security', [
            'query' => [
                'username' => $username,
                'password' => $password,
                'api_key' => $this->wowCollectionSDK->getApiKey(),
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        if (!$this->wowCollectionSDK->isStatusValid($response)) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (null !== $error = $data['hydra:member'][0]['error'] ?? null) {
            if ($throwError && class_exists($error['type'])) {
                throw new $error['type']($error['message']);
            }

            unset($data['hydra:member'][0]);
        }

        return $data;
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
            $response = $this->wowCollectionSDK->getClient()->request('POST', '/login_check', [
                'json' => [
                    'username' => $credentials['_username'],
                    'password' => $credentials['_password'],
                ]
            ]);
        } else {
            $response = $this->wowCollectionSDK->getClient()->request('POST', '/token/refresh', [
                'form_params' => ['refresh_token' => $user->getJwtRefreshToken()],
            ]);
        }

        if (!$this->wowCollectionSDK->isStatusValid($response)) {
            return false;
        }

        $response = json_decode($response->getBody()->getContents(), true);

        $user
            ->setJwtToken($response['token'])
            ->setJwtRefreshToken($response['refresh_token'])
            ->resetTokenExpiration();

        return true;
    }

}
