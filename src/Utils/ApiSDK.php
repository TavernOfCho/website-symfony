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

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Accept: application/ld+json'],
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => sprintf("%s/users/security?%s", $this->api_url, http_build_query([
                'username' => $credentials['_username'],
                'password' => $credentials['_password'],
//                'api_key' => $this->api_key."1",
                'api_key' => $this->api_key,
            ]))
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        if (null === $response = json_decode($response, true)) {
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

        var_dump($response);
        $expiration = (new \DateTime())->add(new \DateInterval("PT3600S"));
        $user->setJwtToken($response['token'])->setJwtTokenExpiration($expiration);

        return true;
    }

    /**
     * @param string $url
     * @return array
     */
    protected function sendGET($url)
    {
        $url = str_replace(' ', '%20', $url);
        $ch = curl_init();
        $authorization = sprintf("Authorization: Bearer %s", $this->generateKey($url));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $authorization]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data, true);
    }

    /**
     * @param string $url
     * @param string $body
     * @return string
     */
    private function generateKey($url, $body = "")
    {
        return $body;
    }

}
