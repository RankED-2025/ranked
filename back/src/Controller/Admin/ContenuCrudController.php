<?php

namespace App\Controller\Admin;

use App\Entity\Contenu;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ContenuCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contenu::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $currentActiviteId = null;
        $entity = $this->getContext()?->getEntity()->getInstance();
        if ($entity instanceof Contenu) {
            $currentActiviteId = $entity->getActivite()?->getId();
        }

        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('type', 'Type')->setChoices(['Vidéo' => 'video', 'PDF' => 'pdf', 'Article' => 'article', 'Image' => 'image']),
            UrlField::new('url', 'URL'),
            AssociationField::new('activite', 'Activité')
                ->setQueryBuilder(function (QueryBuilder $qb) use ($currentActiviteId) {
                    $qb->leftJoin('entity.contenu', 'c')
                        ->andWhere('entity.type = :type')
                        ->andWhere('c.id IS NULL' . ($currentActiviteId ? ' OR entity.id = :currentActiviteId' : ''))
                        ->setParameter('type', 'contenu');
                    if ($currentActiviteId) {
                        $qb->setParameter('currentActiviteId', $currentActiviteId);
                    }
                    return $qb;
                }),
        ];
    }
}
