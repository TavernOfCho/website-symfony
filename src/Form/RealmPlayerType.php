<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RealmPlayerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('character_name', TextType::class, [
                'label' => 'Character Name'
            ])
            ->add('realm', RealmType::class, [
                'label' => 'Realm',
                'attr' => [
                    'class' => 'select2'
                ]
            ]);
    }
}
