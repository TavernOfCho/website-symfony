<?php

namespace App\Security\Core\User;

use App\Entity\BnetOAuthUser;
use App\Manager\BnetOAuthUserManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseOauthUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OauthUserProvider
 * @package App\Security\Core\User
 */
class OauthUserProvider extends BaseOauthUserProvider
{
    /** @var BnetOAuthUserManager $bnetOAuthUserManager */
    private $bnetOAuthUserManager;

    /**
     * OauthUserProvider constructor.
     * @param BnetOAuthUserManager $bnetOAuthUserManager
     */
    public function __construct(BnetOAuthUserManager $bnetOAuthUserManager)
    {
        $this->bnetOAuthUserManager = $bnetOAuthUserManager;
    }

    /**
     * @param UserResponseInterface $response
     * @return BnetOAuthUser|null|object
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $bnetOAuthUser = $this->bnetOAuthUserManager->findOrCreateUser($response);

        return $this->bnetOAuthUserManager->updateFromUserResponse($bnetOAuthUser, $response);
    }

    /**
     * @param string $username
     * @return BnetOAuthUser
     */
    public function loadUserByUsername($username)
    {
        return $this->bnetOAuthUserManager->getRepository()->findOneByBnetId($username);
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