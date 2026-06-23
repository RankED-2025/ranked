<?php

namespace App\Controller\Admin;

use App\Entity\Matiere;
use App\Form\Type\MatiereCoursDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MatiereCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Matiere::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->addFormTheme('admin/form/matiere_cours_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('libelle', 'Libellé'),
            Field::new('coursView', 'Cours')
                ->setFormType(MatiereCoursDisplayType::class)
                ->setTemplatePath('admin/fields/matiere_cours.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
