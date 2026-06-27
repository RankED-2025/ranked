<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContenuLinkDisplayType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['mapped' => false, 'required' => false]);
    }

    public function getBlockPrefix(): string
    {
        return 'contenu_link_display';
    }
}
