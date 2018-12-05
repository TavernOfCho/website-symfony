<?php

namespace App\Utils;

use App\Security\Core\User\BnetOAuthUser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WowCollectionSDKExtension
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var WowCollectionSDK $wowCollectionSDK */
    private $wowCollectionSDK;

    /**
     * WowCollectionSDKExtension constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param WowCollectionSDK $wowCollectionSDK
     */
    public function __construct(TokenStorageInterface $tokenStorage, WowCollectionSDK $wowCollectionSDK)
    {
        $this->wowCollectionSDK = $wowCollectionSDK;
        $this->tokenStorage = $tokenStorage;
    }

    /* API Endpoints */

    /**
     * @return array
     */
    public function getRealms()
    {
        $response = $this->getClient()->request('GET', '/realms', [
            'headers' => $this->getBasicJsonHeader()
        ]);

        if (!$this->wowCollectionSDK->isStatusValid($response)) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $realms = $this->paginate($data['hydra:view'], $data['hydra:member']);

        return array_combine(array_column($realms, 'slug'), array_column($realms, 'name'));
    }

    /**
     * @param string $player
     * @param string $realm
     * @return null
     */
    public function getCharacter(string $player, string $realm)
    {
        $response = $this->getClient()->request('GET', sprintf('/characters/%s', $player), [
            'query' => [
                'realm' => $realm
            ],
            'headers' => $this->getBasicJsonHeader()
        ]);

        if (!$this->wowCollectionSDK->isStatusValid($response)) {
            return null;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $realm
     * @return mixed|null
     */
    public function getRealm(string $realm)
    {
        $response = $this->getClient()->request('GET', sprintf('/realms/%s', $realm), [
            'headers' => $this->getBasicJsonHeader()
        ]);

        if (!$this->wowCollectionSDK->isStatusValid($response)) {
            return null;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return mixed|null
     */
    public function getCharacterClasses()
    {
        $response = $this->getClient()->request('GET', '/classes', [
            'headers' => $this->getBasicJsonHeader()
        ]);

        if (!$this->wowCollectionSDK->isStatusValid($response)) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return $this->paginateOrData($data);
    }

    /**
     * @return mixed|null
     */
    public function getCharacterRaces()
    {
        $response = $this->getClient()->request('GET', '/races', [
            'headers' => $this->getBasicJsonHeader()
        ]);

        if (!$this->wowCollectionSDK->isStatusValid($response)) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return $this->paginateOrData($data);
    }

    /**
     * @param string $player
     * @param string $realm
     * @param bool $format
     * @return array|null
     */
    public function findCharacter(string $player, string $realm, bool $format = true)
    {
        try {
            $contents = $this->getCharacter($player, $realm);

            return $format ? $this->formatCharacter($contents) : $contents;
        } catch (ClientException $e) {
            $contents = json_decode($e->getResponse()->getBody()->getContents(), true);
            if (isset($contents['reason']) && $contents['reason'] === 'Character not found.') {
                return null;
            }

            throw $e;
        }
    }

    /**
     * @param array $character
     * @return array
     */
    public function formatCharacter(array $character)
    {
        $character['thumbnail'] = sprintf('http://render-eu.worldofwarcraft.com/character/%s', $character['thumbnail']);

        $classes = $this->getCharacterClasses();
        if (false !== $key = array_search($character['class'], array_column($classes, 'id'))) {
            $character['class'] = $classes[$key];
        }

        $races = $this->getCharacterRaces();
        if (false !== $key = array_search($character['race'], array_column($races, 'id'))) {
            $character['race'] = $races[$key];
        }

        return $character;
    }

    /**
     * @param array $data
     * @return array|mixed
     */
    public function paginateOrData(array $data)
    {
        return isset($data['hydra:view']) ? $this->paginate($data['hydra:view'], $data['hydra:member']) : $data['hydra:member'];
    }

    /**
     * @param $hydraView
     * @param array $results
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function paginate($hydraView, $results = [])
    {
        do {
            $response = $this->getClient()->request('GET', $hydraView['hydra:next'], [
                'headers' => $this->getBasicJsonHeader()
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $results = array_merge($results, $data['hydra:member']);
            $hydraView = $data['hydra:view'];
        } while (isset($hydraView['hydra:next']));

        return $results;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->wowCollectionSDK->getClient();
    }

    /**
     * @return WowCollectionSDK
     */
    public function getWowCollectionSDK(): WowCollectionSDK
    {
        return $this->wowCollectionSDK;
    }

    /**
     * @return BnetOAuthUser|mixed
     */
    protected function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * @return array
     */
    protected function getBasicJsonHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/ld+json',
            'Authorization' => 'Bearer ' . $this->getUser()->getJwtToken()
        ];
    }

}
