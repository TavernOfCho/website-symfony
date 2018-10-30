<?php

namespace App\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Utils\ApiSDK;

/**
 * Class OauthUserProvider
 * @package App\Security\Core\User
 */
class UserProvider extends OAuthUserProvider
{
    /**
     * @var ApiSDK $apiSDK
     */
    private $apiSDK;

    /**
     * OauthUserProvider constructor.
     * @param ApiSDK $apiSDK
     */
    public function __construct(ApiSDK $apiSDK)
    {
        $this->apiSDK = $apiSDK;
    }

    /**
     * @param UserResponseInterface $response
     * @return BnetOAuthUser|null|object
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->apiSDK->generateBnetOauthUser($response);
    }

    /**
     * @param UserInterface $user
     * @return BnetOAuthUser|UserInterface
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $user;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return BnetOAuthUser::class === $class;
    }
}
