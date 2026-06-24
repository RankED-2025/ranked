<?php

namespace App\Controller\Admin;

use App\Entity\Badge;
use App\Form\Type\BadgeProgressionsDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BadgeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Badge::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->addFormTheme('admin/form/badge_progressions_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('type', 'Type'),
            TextField::new('label', 'Libellé'),

            Field::new('progressionsView', 'Progressions')
                ->setFormType(BadgeProgressionsDisplayType::class)
                ->setTemplatePath('admin/fields/badge_progressions.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
