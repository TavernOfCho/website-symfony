<?php

namespace App\Utils;

use App\Security\Core\User\BnetOAuthUser;

class ApiSDK
{
    /** @var string $api_url */
    private $api_url;

    /** @var string $api_key */
    private $api_key;

    /**
     * ApiSDK constructor.
     * @param string $api_url
     * @param string $api_key
     */
    public function __construct(string $api_url, string $api_key)
    {
        $this->api_url = $api_url;
        $this->api_key = $api_key;
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

        $response = $this->fetchUserSecurity($credentials['username'], $credentials['plainPassword']);

        //Existing user
        if (null !== $response && isset($response['hydra:member']) && count($response['hydra:member']) > 0) {
            return null;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => sprintf("%s/register", $this->api_url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'username' => $credentials['username'],
                'password' => $credentials['plainPassword'],
            ]),
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        ]);

        curl_exec($curl);
        curl_close($curl);

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
        $url = sprintf("%s/login_check", $this->api_url);
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'username' => $credentials['_username'],
                'password' => $credentials['_password'],
            ]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
            ],
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        if (null === $response = json_decode($response, true)) {
            return false;
        }

        $user
            ->setJwtToken($response['token'])
            ->setJwtRefreshToken($response['refresh_token'])
            ->resetTokenExpiration();

        return true;
    }

    /**
     * @param BnetOAuthUser $user
     * @return bool
     */
    public function jwtRefreshToken(BnetOAuthUser $user)
    {
        $url = sprintf("%s/token/refresh", $this->api_url);
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => ['refresh_token' => $user->getJwtRefreshToken()],
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        if (null === $response = json_decode($response, true)) {
            return false;
        }

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
    private function fetchUserSecurity(string $username, string $password)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Accept: application/ld+json'],
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => sprintf("%s/users/security?%s", $this->api_url, http_build_query([
                'username' => $username,
                'password' => $password,
                'api_key' => $this->api_key,
            ]))
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        if (null === $response = json_decode($response, true)) {
            return null;
        }

        return $response;
    }

}
