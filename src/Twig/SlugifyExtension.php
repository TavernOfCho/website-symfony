<?php

namespace App\Twig;

use Cocur\Slugify\Slugify;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SlugifyExtension extends AbstractExtension
{
    /** @var Slugify $slugify */
    private $slugify;

    /**
     * SlugifyExtension constructor.
     */
    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('slugify', [$this, 'slugify']),
        ];
    }

    /**
     * @param $value
     * @return string
     */
    public function slugify($value)
    {
        return $this->slugify->slugify($value);
    }
}
