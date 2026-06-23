<?php

namespace App\Controller\Admin;

use App\Entity\Contenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ContenuCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contenu::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('type', 'Type'),
            UrlField::new('url', 'URL'),
            AssociationField::new('activite', 'Activité'),
        ];
    }
}
