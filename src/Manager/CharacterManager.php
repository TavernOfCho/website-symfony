<?php

namespace App\Manager;

use App\Utils\WowCollectionSDK;
use GuzzleHttp\Exception\ClientException;

class CharacterManager extends BaseManager
{
    /**
     * @param string $player
     * @param string $realm
     * @param string|null $type
     * @return null
     */
    public function getCharacter(string $player, string $realm, string $type = null)
    {
        return $this->getSDK()->cacheHandle(function () use ($player, $realm, $type) {
            $response = $this->getClient()->request('GET', sprintf('/characters/%s/%s', $player, $type), [
                'query' => [
                    'realm' => $realm
                ],
                'headers' => $this->getBasicJsonHeader()
            ]);

            if (!$this->getSDK()->isStatusValid($response)) {
                return null;
            }

            return json_decode($response->getBody()->getContents(), true);

        }, sprintf('player_%s_%s_%s', $player, $realm, $type));
    }

    /**
     * @param string $player
     * @param string $realm
     * @return null
     */
    public function getCharacterItems(string $player, string $realm)
    {
        return $this->getCharacter($player, $realm, 'items');
    }

    /**
     * @param string $player
     * @param string $realm
     * @return null
     */
    public function getCharacterStats(string $player, string $realm)
    {
        return $this->getCharacter($player, $realm, 'stats');
    }

    /**
     * @return mixed|null
     */
    public function getCharacterClasses()
    {
        return $this->getSDK()->cacheHandle(function () {
            $response = $this->getClient()->request('GET', '/classes', [
                'headers' => $this->getBasicJsonHeader()
            ]);

            if (!$this->getSDK()->isStatusValid($response)) {
                return null;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->paginateOrData($data);
        }, 'classes', WowCollectionSDK::LONG_TIME);
    }

    /**
     * @return mixed|null
     */
    public function getCharacterRaces()
    {
        return $this->getSDK()->cacheHandle(function () {
            $response = $this->getClient()->request('GET', '/races', [
                'headers' => $this->getBasicJsonHeader()
            ]);

            if (!$this->getSDK()->isStatusValid($response)) {
                return null;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->paginateOrData($data);
        }, 'races', WowCollectionSDK::LONG_TIME);
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
        $character['main_background'] = str_replace('-avatar', '-main', $character['thumbnail']);
        $image = imagecreatefromjpeg($character['main_background']);
        $image_size = (object)['width' => imagesx($image), 'height' => imagesy($image)];

        $rgb = imagecolorat($image, round($image_size->width / 2), $image_size->height - 1);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $character['main_color'] = (object)['r' => $r, 'g' => $g, 'b' => $b];

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
     * @param string $player
     * @param string $realm
     * @param bool $format
     * @return array|null
     */
    public function findCharacterItems(string $player, string $realm, bool $format = true)
    {
        try {
            $contents = $this->getCharacterItems($player, $realm);

            return $format ? $this->formatCharacterItems($contents) : $contents;
        } catch (ClientException $e) {
            $contents = json_decode($e->getResponse()->getBody()->getContents(), true);
            if (isset($contents['reason']) && $contents['reason'] === 'Character not found.') {
                return null;
            }

            throw $e;
        }
    }

    /**
     * @param array $items
     * @return array
     */
    public function formatCharacterItems(array $items)
    {
        foreach ($items as $key => $item) {
            if (isset($item['icon'])) {
                $items[$key]['image'] = [
                    'small' => sprintf('https://wow.zamimg.com/images/wow/icons/small/%s.jpg', $item['icon']),
                    'medium' => sprintf('https://wow.zamimg.com/images/wow/icons/medium/%s.jpg', $item['icon']),
                    'large' => sprintf('https://wow.zamimg.com/images/wow/icons/large/%s.jpg', $item['icon'])
                ];
            }
        }

        return $items;
    }

}
