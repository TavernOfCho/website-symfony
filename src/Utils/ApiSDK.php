<?php

namespace App\Utils;


use App\Security\Core\User\BnetOAuthUser;

class ApiSDK
{
    private $api_url = "http://api"; # TODO SET IN ENV PARAMETERS

    /**
     * @return BnetOAuthUser
     */
    public function generateBnetOauthUser(array $credentials)
    {
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
}
