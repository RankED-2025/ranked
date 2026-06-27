<?php

namespace App\Controller\Admin;

use App\Entity\Difficulte;
use App\Form\Type\DifficulteCoursDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DifficulteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Difficulte::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->addFormTheme('admin/form/difficulte_cours_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('label', 'Libellé'),

            Field::new('coursView', 'Cours')
                ->setFormType(DifficulteCoursDisplayType::class)
                ->setTemplatePath('admin/fields/difficulte_cours.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
