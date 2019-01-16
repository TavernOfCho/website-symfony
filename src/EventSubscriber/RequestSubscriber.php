<?php

namespace App\EventSubscriber;

use App\Manager\UserManager;
use App\Security\Core\User\BnetOAuthUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    /** @var UserManager $userManager */
    private $userManager;

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /**
     * RequestSubscriber constructor.
     * @param UserManager $userManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(UserManager $userManager, TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
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
            $this->userManager->jwtRefreshToken($user);
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
