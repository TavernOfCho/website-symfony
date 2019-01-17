<?php

namespace App\Manager;

use App\Security\Core\User\BnetOAuthUser;
use App\Utils\WowCollectionSDK;
use GuzzleHttp\Client;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BaseManager
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var WowCollectionSDK $wowCollectionSDK */
    private $wowCollectionSDK;

    /**
     * BaseManager constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param WowCollectionSDK $wowCollectionSDK
     */
    public function __construct(TokenStorageInterface $tokenStorage, WowCollectionSDK $wowCollectionSDK)
    {
        $this->wowCollectionSDK = $wowCollectionSDK;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return WowCollectionSDK
     */
    public function getSDK(): WowCollectionSDK
    {
        return $this->wowCollectionSDK;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->wowCollectionSDK->getClient();
    }


    /**
     * @param array $data
     * @return array|mixed
     */
    public function paginateOrData(array $data)
    {
        return isset($data['hydra:view'], $data['hydra:view']['hydra:next']) ?
            $this->paginate($data['hydra:view'], $data['hydra:member']) :
            $data['hydra:member'];
    }

    /**
     * @param $hydraView
     * @param array $results
     * @return array
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
