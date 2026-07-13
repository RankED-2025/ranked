<?php

namespace App\Controller\Admin;

use App\Entity\Progression;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ProgressionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Progression::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('eleve', 'Élève'),
            AssociationField::new('cours', 'Cours'),
            AssociationField::new('badge', 'Badge'),
            NumberField::new('percentage', '%'),
        ];
    }
}
