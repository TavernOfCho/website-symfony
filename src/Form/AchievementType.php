<?php

declare(strict_types=1);

namespace App\Form;

use App\Manager\AchievementManager;
use App\Manager\CharacterManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchievementType extends AbstractType
{
    /** @var CharacterManager $characterManager */
    private $characterManager;

    /** @var AchievementManager $achievementManager */
    private $achievementManager;

    /**
     * AchievementType constructor.
     * @param CharacterManager $characterManager
     * @param AchievementManager $achievementManager
     */
    public function __construct(CharacterManager $characterManager, AchievementManager $achievementManager)
    {
        $this->characterManager = $characterManager;
        $this->achievementManager = $achievementManager;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['username', 'realm']);

        $choices = function (Options $options) {
            $achievements = $this->achievementManager->getCharacterAchievements($options['username'], $options['realm']);
            $categories = array_combine(array_column($achievements, 'id'), array_column($achievements, 'category'));
            $achievements = $this->achievementManager->listCharacterAchievements($achievements);

            $completedAchievements = $this->achievementManager->getCharacterCompletedAchievements($options['username'], $options['realm']);
            $completed = $this->achievementManager->listCharacterCompletedAchievements($completedAchievements);

            $remaining = array_diff_key($achievements, $completed);
            $remaining = array_flip($remaining);

            $data = [];
            foreach ($remaining as $key => $item) {
                $data[$categories[$item]][$key] = $item;
            }

            ksort($data);
            array_map('ksort', $data);

            return $data;
        };

        $resolver->setDefault('choices', $choices);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
