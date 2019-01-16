<?php

namespace App\Manager;


class AchievementManager extends BaseManager
{
    /**
     * @param string $player
     * @param string $realm
     * @return mixed
     */
    public function getCharacterAchievements(string $player, string $realm)
    {
        return $this->getSDK()->cacheHandle(function () use ($player, $realm) {
            $response = $this->getClient()->request('GET', sprintf('/characters/%s/achievements', $player), [
                'query' => [
                    'realm' => $realm
                ],
                'headers' => $this->getBasicJsonHeader()
            ]);

            if (!$this->getSDK()->isStatusValid($response)) {
                return null;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->paginateOrData($data);
        }, sprintf('character_achievements_%s_%s', $player, $realm));
    }

    /**
     * @param array $achievements
     * @return array|null
     */
    public function listCharacterAchievements(array $achievements)
    {
        return array_combine(array_column($achievements, 'id'), array_column($achievements, 'title'));
    }

    /**
     * @param string $player
     * @param string $realm
     * @return mixed
     */
    public function getCharacterCompletedAchievements(string $player, string $realm)
    {
        return $this->getSDK()->cacheHandle(function () use ($player, $realm) {
            $response = $this->getClient()->request('GET', sprintf('/characters/%s/achievements/completed', $player), [
                'query' => [
                    'realm' => $realm
                ],
                'headers' => $this->getBasicJsonHeader()
            ]);

            if (!$this->getSDK()->isStatusValid($response)) {
                return null;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return $this->paginateOrData($data);
        }, sprintf('character_completed_achievements_%s_%s', $player, $realm));
    }

    /**
     * @param array $achievements
     * @return array|null
     */
    public function listCharacterCompletedAchievements(array $achievements)
    {
        return array_flip(array_column($achievements, 'id'));
    }


    public function formatAchievements(array $content, int $factionId)
    {
        $content = $content['achievements'] ?? [];

        $output = [];
        foreach ($content as $item) {
            $output[$item['id']] = $item['name'];

            $achievements = $item['achievements'] ?? [];

            $categoriesAchievements = array_column($item['categories'] ?? [], 'achievements');

            foreach ($categoriesAchievements as $categogiesAchievement) {
                $achievements = array_merge($achievements, $categogiesAchievement);
            }

            foreach ($achievements as $achievement) {
                if (!in_array($achievement['factionId'], [$factionId, 2])) {
                    continue;
                }

                $output[$achievement['id']] = $achievement['title'];
            }
        };

        return $output;

    }

}