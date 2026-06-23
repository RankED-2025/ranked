<?php

namespace App\Controller\Admin;

use App\Entity\Eleve;
use App\Form\Type\ActiviteProgressionsDisplayType;
use App\Form\Type\EleveCompetencesDisplayType;
use App\Form\Type\ProgressionsDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EleveCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Eleve::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/form/progressions_display.html.twig')
            ->addFormTheme('admin/form/eleve_competences_display.html.twig')
            ->addFormTheme('admin/form/activite_progressions_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstname', 'Prénom'),
            TextField::new('name', 'Nom'),
            EmailField::new('email', 'Email'),
            AssociationField::new('classe', 'Classe'),

            Field::new('progressionsView', 'Progressions')
                ->setFormType(ProgressionsDisplayType::class)
                ->setTemplatePath('admin/fields/progressions.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),

            Field::new('eleveCompetencesView', 'Compétences')
                ->setFormType(EleveCompetencesDisplayType::class)
                ->setTemplatePath('admin/fields/eleve_competences.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),

            Field::new('activiteProgressionsView', 'Progression des activités')
                ->setFormType(ActiviteProgressionsDisplayType::class)
                ->setTemplatePath('admin/fields/activite_progressions.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
