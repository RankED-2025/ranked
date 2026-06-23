<?php

namespace App\Controller\Admin;

use App\Entity\Professeur;
use App\Form\Type\ProfesseurClassesDisplayType;
use App\Form\Type\ProfesseurCoursDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProfesseurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Professeur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/form/professeur_classes_display.html.twig')
            ->addFormTheme('admin/form/professeur_cours_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstname', 'Prénom'),
            TextField::new('name', 'Nom'),
            EmailField::new('email', 'Email'),

            Field::new('classesView', 'Classes')
                ->setFormType(ProfesseurClassesDisplayType::class)
                ->setTemplatePath('admin/fields/professeur_classes.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),

            Field::new('coursView', 'Cours')
                ->setFormType(ProfesseurCoursDisplayType::class)
                ->setTemplatePath('admin/fields/professeur_cours.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
