<?php

namespace App\Manager;

use App\Security\Core\User\BnetOAuthUser;
use App\Utils\WowCollectionSDK;

class UserManager extends BaseManager
{
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

            $user
                ->setId($data['id'])
                ->setUsername($data['username'])
                ->setBnetAccessToken($data['bnetAccessToken'])
                ->setBnetBattletag($data['bnetBattletag'])
                ->setBnetId($data['bnetId'])
                ->setBnetSub($data['bnetSub'])
                ->setPassword($data['password'])
                ->setRoles($data['roles'])
                ->setEnabled($data['enabled']);

            if (null === $user = $this->jwtConnect($user, $credentials)) {
                return null;
            }

            if (isset($credentials['_email'])) {
                $user->setEmail($credentials['_email']);

                // Add the email to the account
                $this->patchEmailPreferences($data['id'], [
                    'email' => $user->getEmail(),
                    'mail_enabled' => true
                ], $user);
            }

            return $user;
        }

        return null;
    }

    /**
     * @param array $credentials
     * @return BnetOAuthUser|string|null
     */
    public function createAccount(array $credentials)
    {
        if (!isset($credentials['username'], $credentials['email'], $credentials['plainPassword'])) {
            return null;
        }

        $response = $this->fetchUserSecurity($credentials['username'], $credentials['plainPassword'], false);

        //Existing user
        if (null !== $response && isset($response['hydra:member']) && count($response['hydra:member']) > 0) {
            return 'An account with this username already exists';
        }

        $response = $this->getSDK()->getClient()->request('POST', '/register', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'username' => $credentials['username'],
                'password' => $credentials['plainPassword'],
            ],
        ]);


        if (!$this->getSDK()->isStatusValid($response)) {
            return 'An error occured while creating your account.';
        }

        return $this->generateBnetOauthUser([
            '_username' => $credentials['username'],
            '_password' => $credentials['plainPassword'],
            '_email' => $credentials['email']
        ]);
    }

    /**
     * @param int $id
     * @param array $data
     * @param BnetOAuthUser|null $user
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function patchEmailPreferences(int $id, array $data, BnetOAuthUser $user = null)
    {
        $headers = $user ? [
            'Content-Type' => 'application/json',
            'Accept' => 'application/ld+json',
            'Authorization' => 'Bearer ' . $user->getJwtToken()
        ] : $this->getBasicJsonHeader();

        $response = $this->getSDK()->getClient()->request('PUT', sprintf('/users/%s', $id), [
            'headers' => $headers,
            'json' => [
                'email' => $data['email'],
                'mail_enabled' => $data['mail_enabled'],
            ]
        ]);

        if (!$this->getSDK()->isStatusValid($response)) {
            return null;
        }

        return $response;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->getSDK()->cacheHandle(function () use ($id) {
            $response = $this->getClient()->request('GET', sprintf('/users/%s', $id), [
                'headers' => $this->getBasicJsonHeader()
            ]);

            if (!$this->getSDK()->isStatusValid($response)) {
                return null;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return $data;
        }, sprintf('users_%s', $id), WowCollectionSDK::SHORT_TIME);
    }


    /**
     * @param BnetOAuthUser $user
     * @return BnetOAuthUser
     */
    public function jwtRefreshToken(BnetOAuthUser $user)
    {
        return $this->sendToken($user);
    }

    /**
     * @param BnetOAuthUser $user
     * @param array $credentials
     * @return BnetOAuthUser|bool
     */
    private function jwtConnect(BnetOAuthUser $user, array $credentials)
    {
        return $this->sendToken($user, $credentials);
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $throwError
     * @return mixed|null
     */
    private function fetchUserSecurity(string $username, string $password, bool $throwError = true)
    {
        $response = $this->getSDK()->getClient()->request('GET', '/users/security', [
            'query' => [
                'username' => $username,
                'password' => $password,
                'api_key' => $this->getSDK()->getApiKey(),
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        if (!$this->getSDK()->isStatusValid($response)) {
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
     * @return BnetOAuthUser
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendToken(BnetOAuthUser $user, array $credentials = null)
    {
        if (is_array($credentials)) {
            $response = $this->getSDK()->getClient()->request('POST', '/login_check', [
                'json' => [
                    'username' => $credentials['_username'],
                    'password' => $credentials['_password'],
                ]
            ]);
        } else {
            $response = $this->getSDK()->getClient()->request('POST', '/token/refresh', [
                'form_params' => ['refresh_token' => $user->getJwtRefreshToken()],
            ]);
        }

        if (!$this->getSDK()->isStatusValid($response)) {
            return false;
        }

        $response = json_decode($response->getBody()->getContents(), true);

        $user
            ->setJwtToken($response['token'])
            ->setJwtRefreshToken($response['refresh_token'])
            ->resetTokenExpiration();

        return $user;
    }

}
