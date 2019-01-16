<?php

declare(strict_types=1);

namespace App\Form;

use App\Manager\RealmManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealmType extends AbstractType
{
    /** @var RealmManager $realmManager */
    private $realmManager;

    /**
     * RealmType constructor.
     * @param RealmManager $realmManager
     */
    public function __construct(RealmManager $realmManager)
    {
        $this->realmManager = $realmManager;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->realmManager->getRealms()
        ]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
