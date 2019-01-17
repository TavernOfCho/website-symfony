<?php

declare(strict_types=1);

namespace App\Twig;

use App\Manager\AchievementManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AchievementExtension extends AbstractExtension
{
    /** @var AchievementManager $achievementManager */
    private $achievementManager;

    /**
     * AchievementExtension constructor.
     * @param AchievementManager $achievementManager
     */
    public function __construct(AchievementManager $achievementManager)
    {
        $this->achievementManager = $achievementManager;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('achievement_label', [$this, 'achievement_label']),
        ];
    }

    /**
     * @param int $id
     * @return string
     */
    public function achievement_label(int $id): string
    {
        $achievement = $this->achievementManager->getAchievement($id);

        return $achievement['title'] ?? '';
    }
}
