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
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $params = [
            'username' => $credentials['_username'],
            'password' => $credentials['_password'],
//            'api_key' => $this->api_key."1",
            'api_key' => $this->api_key,
        ];

        $url = sprintf("%s/users/security?%s", $this->api_url, http_build_query($params));

        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);

        curl_close($ch);

        if (null === $response = json_decode($response, true)) {
            return null;
        }

        $data = $response['hydra:member'];
        if (is_array($data) && count($data) > 0) {
            $user = new BnetOAuthUser();
            $data = $data[0];

            return $user->setUsername($data['username'])
                ->setBnetAccessToken($data['bnetAccessToken'])
                ->setBnetBattletag($data['bnetBattletag'])
                ->setBnetId($data['bnetId'])
                ->setBnetSub($data['bnetSub'])
                ->setPassword($data['password'])
                ->setRoles($data['roles'])
                ->setEnabled($data['enabled']);
        }

        return null;
    }

    public function getUser(string $username)
    {
        
     }

    protected function sendGETBasic($url)
    {
        $ch = curl_init();

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
