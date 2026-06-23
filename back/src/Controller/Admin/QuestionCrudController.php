<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Form\Type\QuestionReponsesDisplayType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->addFormTheme('admin/form/question_reponses_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextareaField::new('enonce', 'Énoncé'),
            AssociationField::new('qcm', 'QCM'),

            Field::new('reponsesView', 'Réponses')
                ->setFormType(QuestionReponsesDisplayType::class)
                ->setTemplatePath('admin/fields/question_reponses.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
