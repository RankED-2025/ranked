<?php

namespace App\Controller\Admin;

use App\Entity\Classe;
use App\Form\Type\ClasseElevesDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClasseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Classe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->addFormTheme('admin/form/classe_eleves_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom'),
            AssociationField::new('professeur', 'Professeur'),

            Field::new('elevesView', 'Élèves')
                ->setFormType(ClasseElevesDisplayType::class)
                ->setTemplatePath('admin/fields/classe_eleves.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
