<?php

namespace App\Controller\Admin;

use App\Entity\Eleve;
use App\Entity\User;
use App\Form\Type\ActiviteProgressionsDisplayType;
use App\Form\Type\EleveCompetencesDisplayType;
use App\Form\Type\ProgressionsDisplayType;
use App\Trait\AppAdminCrudHelper;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EleveCrudController extends AbstractCrudController
{
    use AppAdminCrudHelper;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        //
    }

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

    /**
     * @param EntityManagerInterface $entityManager
     * @param User $entityInstance
     * @return void
     */
    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $data = $this->getFormData();

        $entityInstance->setPassword(
            $this->passwordHasher->hashPassword($entityInstance, $data["Eleve"]["pwd_primitive"])
        );

        $entityInstance->setCreatedAt(new \DateTimeImmutable);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param User $entityInstance
     * @return void
     */
    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $data = $this->getFormData();

        if( !!$data["Eleve"]["pwd_primitive"] ) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $data["Eleve"]["pwd_primitive"])
            );
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
