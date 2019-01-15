<?php

declare(strict_types=1);

namespace App\Utils;

use App\Exception\CharacterMissingException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CharacterHelper
{

    /**
     * @var SessionInterface $session
     */
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return array
     * @throws CharacterMissingException
     */
    public function getCurrent(): array
    {
        $character = $this->session->get('character');
        if (!$character) {
            throw new CharacterMissingException();
        }

        return $character;
    }
}
