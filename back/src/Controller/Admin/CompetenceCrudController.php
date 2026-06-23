<?php

namespace App\Controller\Admin;

use App\Entity\Competence;
use App\Form\Type\CompetenceEleveCompetencesDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompetenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Competence::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->addFormTheme('admin/form/competence_eleve_competences_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom'),
            TextField::new('niveau', 'Niveau'),
            AssociationField::new('cours', 'Cours'),

            Field::new('eleveCompetencesView', 'Élèves')
                ->setFormType(CompetenceEleveCompetencesDisplayType::class)
                ->setTemplatePath('admin/fields/competence_eleve_competences.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
