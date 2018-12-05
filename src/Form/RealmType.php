<?php

declare(strict_types=1);

namespace App\Form;

use App\Utils\WowCollectionSDKExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealmType extends AbstractType
{
    /**
     * @var WowCollectionSDKExtension
     */
    private $wowCollectionSDKExtension;

    /**
     * RealmType constructor.
     * @param WowCollectionSDKExtension $wowCollectionSDKExtension
     */
    public function __construct(WowCollectionSDKExtension $wowCollectionSDKExtension)
    {
        $this->wowCollectionSDKExtension = $wowCollectionSDKExtension;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->wowCollectionSDKExtension->getRealms()
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
