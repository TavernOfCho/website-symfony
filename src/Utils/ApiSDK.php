<?php

namespace App\Utils;


use App\Security\Core\User\BnetOAuthUser;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

class ApiSDK
{
    /**
     * @param UserResponseInterface $response
     * @return BnetOAuthUser
     */
    public function generateBnetOauthUser(UserResponseInterface $response)
    {
        $data = $response->getData();
        $user = new BnetOAuthUser($data['id'], $data['sub'], $data['battletag'], $response->getAccessToken());

        $user->setBnetId($data['id'])
            ->setBnetSub($data['sub'])
            ->setBnetBattletag($data['battletag'])
            ->setBnetAccessToken($response->getAccessToken());

        return $user;
    }
}
