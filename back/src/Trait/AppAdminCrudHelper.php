<?php

namespace App\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @extends AbstractCrudController
 */
trait AppAdminCrudHelper
{
    public function getFormData(): array
    {
        return $this->getContext()->getRequest()->request->all();
    }
}
