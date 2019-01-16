<?php

namespace App\Manager;


use App\Utils\WowCollectionSDK;

class RealmManager extends BaseManager
{
    /**
     * @return array
     */
    public function getRealms()
    {
        $response = $this->getClient()->request('GET', '/realms', [
            'headers' => $this->getBasicJsonHeader()
        ]);

        if (!$this->getSDK()->isStatusValid($response)) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $realms = $this->paginate($data['hydra:view'], $data['hydra:member']);

        return array_combine(array_column($realms, 'slug'), array_column($realms, 'name'));
    }

    /**
     * @param string $realm
     * @return mixed|null
     */
    public function getRealm(string $realm)
    {
        return $this->getSDK()->cacheHandle(function () use ($realm) {
            $response = $this->getClient()->request('GET', sprintf('/realms/%s', $realm), [
                'headers' => $this->getBasicJsonHeader()
            ]);

            if (!$this->getSDK()->isStatusValid($response)) {
                return null;
            }

            return json_decode($response->getBody()->getContents(), true);
        }, sprintf('realm_%s', $realm), WowCollectionSDK::LONG_TIME);
    }
}
