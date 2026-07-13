<?php

namespace App\Controller\Admin;

use App\Entity\ActiviteProgression;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ActiviteProgressionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ActiviteProgression::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('eleve', 'Élève'),
            AssociationField::new('activite', 'Activité'),
            DateTimeField::new('completedAt', 'Complété le'),
            NumberField::new('score', 'Score'),
            NumberField::new('total', 'Total'),
            NumberField::new('earnedPts', 'Points gagnés'),
        ];
    }
}
