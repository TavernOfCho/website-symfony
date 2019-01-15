<?php

namespace App\EventListener;

use App\Exception\CharacterMissingException;
use App\Utils\CharacterHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class CharacterListener implements EventSubscriberInterface
{
    /** @var CharacterHelper $characterHelper */
    private $characterHelper;

    /** @var RouterInterface $router */
    private $router;

    /**
     * RequestSubscriber constructor.
     * @param CharacterHelper $characterHelper
     * @param RouterInterface $router
     */
    public function __construct(CharacterHelper $characterHelper, RouterInterface $router)
    {
        $this->characterHelper = $characterHelper;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has('_character')) {
            try {
                $request->attributes->set('_character', $this->characterHelper->getCurrent());
            } catch (CharacterMissingException $exception) {
                $redirectUrl = $this->router->generate('dashboard_index', ['redirect' => $request->getRequestUri()]);

                $event->setController(function () use ($redirectUrl) {
                    return new RedirectResponse($redirectUrl);
                });
            }
        }
    }
}
