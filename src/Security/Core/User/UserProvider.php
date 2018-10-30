<?php

namespace App\Security\Core\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Utils\ApiSDK;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class OauthUserProvider
 * @package App\Security\Core\User
 */
class UserProvider implements UserProviderInterface
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

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        return null;
    }
}
