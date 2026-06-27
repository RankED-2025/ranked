<?php

namespace App\Controller\Admin;

use App\Entity\Cours;
use App\Form\Type\CoursActivitesDisplayType;
use App\Form\Type\CoursCompetencesDisplayType;
use App\Form\Type\CoursProgressionsDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CoursCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cours::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/form/cours_activites_display.html.twig')
            ->addFormTheme('admin/form/cours_competences_display.html.twig')
            ->addFormTheme('admin/form/cours_progressions_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('titre', 'Titre'),
            TextareaField::new('description', 'Description')->hideOnIndex(),
            AssociationField::new('matiere', 'Matière'),
            AssociationField::new('difficulte', 'Difficulté'),
            AssociationField::new('professeur', 'Professeur'),

            Field::new('activitesView', 'Activités')
                ->setFormType(CoursActivitesDisplayType::class)
                ->setTemplatePath('admin/fields/cours_activites.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),

            Field::new('competencesView', 'Compétences')
                ->setFormType(CoursCompetencesDisplayType::class)
                ->setTemplatePath('admin/fields/cours_competences.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),

            Field::new('progressionsView', 'Progressions')
                ->setFormType(CoursProgressionsDisplayType::class)
                ->setTemplatePath('admin/fields/cours_progressions.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
