<?php

namespace App\Utils;

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
     */
    public function generateBnetOauthUser(array $credentials)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $params = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'api_key' => $this->api_key,
        ];

        $url = sprintf("%s/users/security?%s", $this->api_url, http_build_query($params));
        var_dump($url);

        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        var_dump($response);exit;


//        $data = $response->getData();
//        $user = new BnetOAuthUser($data['id'], $data['sub'], $data['battletag'], $response->getAccessToken());
//
//        $user->setBnetId($data['id'])
//            ->setBnetSub($data['sub'])
//            ->setBnetBattletag($data['battletag'])
//            ->setBnetAccessToken($response->getAccessToken());
//
//        return $user;
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
