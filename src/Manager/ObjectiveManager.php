<?php

namespace App\Manager;

class ObjectiveManager extends BaseManager
{
    /**
     * @param array $objective
     * @return array
     */
    public function push(array $objective)
    {
        $data = [
            'title' => $objective['title'],
            'endingDate' => $objective['ending_date']->format('Y-m-d\TH:i:sP'),
            'achievementId' => $objective['achievement_id'],
            'character' => $objective['character']['username'],
            'realm' => $objective['character']['realm'],
            'mailSent' => false,
            'bnetUser' => $this->getUser()->getIri()
        ];
        $response = $this->getSDK()->getClient()->request('POST', '/objectives', [
            'headers' => $this->getBasicJsonHeader(),
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}