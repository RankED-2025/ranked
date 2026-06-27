<?php

namespace App\Controller\Admin;

use App\Entity\Professeur;
use App\Entity\User;
use App\Form\Type\ProfesseurClassesDisplayType;
use App\Form\Type\ProfesseurCoursDisplayType;
use App\Trait\AppAdminCrudHelper;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfesseurCrudController extends AbstractCrudController
{
    use AppAdminCrudHelper;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        //
    }

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
        $passwordField = TextField::new('pwd_primitive', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => $pageName === Crud::PAGE_NEW,
                'attr' => ['autocomplete' => 'new-password']
            ])
            ->hideOnDetail()
            ->hideOnIndex();

        if (Crud::PAGE_EDIT === $pageName) {
            $passwordField->setHelp('Laissez vide pour ne pas changer le mot de passe.');
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstname', 'Prénom'),
            TextField::new('name', 'Nom'),
            EmailField::new('email', 'Email'),
            $passwordField,

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

    /**
     * @param EntityManagerInterface $entityManager
     * @param Professeur $entityInstance
     * @return void
     */
    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $data = $this->getFormData();

        $entityInstance->setPassword(
            $this->passwordHasher->hashPassword($entityInstance, $data["Professeur"]["pwd_primitive"])
        );

        $entityInstance->setCreatedAt(new \DateTimeImmutable);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Professeur $entityInstance
     * @return void
     */
    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $data = $this->getFormData();

        if( $data["Professeur"]["pwd_primitive"] ) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $data["Professeur"]["pwd_primitive"])
            );
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
