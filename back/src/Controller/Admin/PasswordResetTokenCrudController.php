<?php

namespace App\Controller\Admin;

use App\Entity\PasswordResetToken;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PasswordResetTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PasswordResetToken::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            Field::new('userLink', 'Utilisateur')
                ->setTemplatePath('admin/fields/user_link.html.twig'),
            TextField::new('token', 'Token'),
            DateTimeField::new('expiresAt', 'Expire le'),
            BooleanField::new('used', 'Utilisé')->renderAsSwitch(false),
        ];
    }
}
