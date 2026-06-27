<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoursProgressionsDisplayType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['mapped' => false, 'required' => false]);
    }

    public function getBlockPrefix(): string
    {
        return 'cours_progressions_display';
    }
}
