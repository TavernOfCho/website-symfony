<?php

namespace App\Manager;

use App\Entity\BnetOAuthUser;
use App\Repository\BnetOauthUserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

/**
 * Class BnetOAuthUserManager
 *
 * @method BnetOAuthUserRepository getRepository()
 * @method BnetOAuthUserManager persistAndFlush(BnetOAuthUser $entity)
 * @method BnetOAuthUserManager removeEntity(BnetOAuthUser $entity)
 * @method BnetOAuthUser newClass()
 */
class BnetOAuthUserManager extends BaseManager
{
    /**
     * BnetOAuthUserManager constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        parent::__construct($manager, BnetOAuthUser::class);
    }

    /**
     * @param UserResponseInterface $response
     * @return BnetOAuthUser|null|object
     */
    public function findOrCreateUser(UserResponseInterface $response)
    {
        if (null === $bnetOAuthUser = $this->getRepository()->findOneByBnetId($response->getUsername())) {
            $bnetOAuthUser = $this->generateBnetOauthUser($response);
        }

        return $bnetOAuthUser;
    }

    /**
     * @param UserResponseInterface $response
     * @return BnetOAuthUser
     */
    public function generateBnetOauthUser(UserResponseInterface $response)
    {
        return $this->updateFromUserResponse($this->newClass(), $response);
    }

    /**
     * @param BnetOAuthUser $user
     * @param UserResponseInterface $response
     * @return BnetOAuthUser
     */
    public function updateFromUserResponse(BnetOAuthUser $user, UserResponseInterface $response)
    {
        $data = $response->getData();

        $user->setBnetId($data['id'])
            ->setBnetSub($data['sub'])
            ->setBnetBattletag($data['battletag'])
            ->setBnetAccessToken($response->getAccessToken());

        $this->persistAndFlush($user);

        return $user;
    }
}