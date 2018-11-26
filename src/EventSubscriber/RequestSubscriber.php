<?php

namespace App\EventSubscriber;

use App\Security\Core\User\BnetOAuthUser;
use App\Utils\WowCollectionSDK;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    /** @var WowCollectionSDK $apiSDK */
    private $apiSDK;

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /**
     * RequestSubscriber constructor.
     * @param WowCollectionSDK $apiSDK
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(WowCollectionSDK $apiSDK, TokenStorageInterface $tokenStorage)
    {
        $this->apiSDK = $apiSDK;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
           'kernel.request' => 'onKernelRequest',
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // don't do anything if it's not the master request
        if (!$event->isMasterRequest()) {
            return;
        }

        if (null === $user = $this->getUser()) {
            return;
        }

        // Current Date less 5 min
        $currentDate = (new \DateTime())->sub(new \DateInterval("PT5M"));
        if ($currentDate > $user->getJwtTokenExpiration()) {
            $this->apiSDK->jwtRefreshToken($user);
        }
    }

    /**
     * @return BnetOAuthUser|null|object|string
     */
    public function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }


}
