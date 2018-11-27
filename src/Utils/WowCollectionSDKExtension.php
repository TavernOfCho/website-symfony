<?php

namespace App\Utils;

use App\Security\Core\User\BnetOAuthUser;
use GuzzleHttp\Client;
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
