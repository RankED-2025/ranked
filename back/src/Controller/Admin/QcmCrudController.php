<?php

namespace App\Controller\Admin;

use App\Entity\Qcm;
use App\Form\Type\QcmQuestionsDisplayType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class QcmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Qcm::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->addFormTheme('admin/form/qcm_questions_display.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $currentActiviteId = null;
        $entity = $this->getContext()?->getEntity()->getInstance();
        if ($entity instanceof Qcm) {
            $currentActiviteId = $entity->getActivite()?->getId();
        }

        return [
            IdField::new('id')->hideOnForm(),
            NumberField::new('gainPts', 'Points gagnés'),
            AssociationField::new('activite', 'Activité')
                ->setQueryBuilder(function (QueryBuilder $qb) use ($currentActiviteId) {
                    $qb->leftJoin('entity.qcm', 'q')
                        ->andWhere('entity.type = :type')
                        ->andWhere('q.id IS NULL' . ($currentActiviteId ? ' OR entity.id = :currentActiviteId' : ''))
                        ->setParameter('type', 'qcm');
                    if ($currentActiviteId) {
                        $qb->setParameter('currentActiviteId', $currentActiviteId);
                    }
                    return $qb;
                }),

            Field::new('questionsView', 'Questions')
                ->setFormType(QcmQuestionsDisplayType::class)
                ->setTemplatePath('admin/fields/qcm_questions.html.twig')
                ->hideOnIndex()
                ->hideWhenCreating(),
        ];
    }
}
