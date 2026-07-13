<?php

namespace App\Controller\Admin;

use App\Entity\Activite;
use App\Form\Type\ActiviteProgressionsByActiviteDisplayType;
use App\Form\Type\ContenuLinkDisplayType;
use App\Form\Type\QcmLinkDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ActiviteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activite::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/form/contenu_link_display.html.twig')
            ->addFormTheme('admin/form/qcm_link_display.html.twig')
            ->addFormTheme('admin/form/activite_progressions_by_activite_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('type', 'Type')->setChoices(['Contenu' => 'contenu', 'QCM' => 'qcm']),
            NumberField::new('ordre', 'Ordre'),
            AssociationField::new('cours', 'Cours'),

            Field::new('contenuLink', 'Contenu')
                ->setFormType(ContenuLinkDisplayType::class)
                ->setTemplatePath('admin/fields/contenu_link.html.twig')
                ->hideWhenCreating(),

            Field::new('qcmLink', 'QCM')
                ->setFormType(QcmLinkDisplayType::class)
                ->setTemplatePath('admin/fields/qcm_link.html.twig')
                ->hideWhenCreating(),

            Field::new('activiteProgressionsView', 'Progressions')
                ->setFormType(ActiviteProgressionsByActiviteDisplayType::class)
                ->setTemplatePath('admin/fields/activite_activite_progressions.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
