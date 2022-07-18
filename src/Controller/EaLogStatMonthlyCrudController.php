<?php

namespace Svc\LogBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Svc\LogBundle\Entity\SvcLogStatMonthly;

class EaLogStatMonthlyCrudController extends AbstractCrudController
{
  public static function getEntityFqcn(): string
  {
    return SvcLogStatMonthly::class;
  }

  public function configureCrud(Crud $crud): Crud
  {
    return parent::configureCrud($crud)
      ->setPageTitle(Crud::PAGE_INDEX, 'SvcLog (monthly statistics)')
      ->setHelp(Crud::PAGE_INDEX, 'show the monthly statistics in a raw format')
      ->setDefaultSort([
        'id' => 'DESC',
      ]);
  }

  public function configureActions(Actions $actions): Actions
  {
    return parent::configureActions($actions)
      ->disable(Action::NEW);
  }

  public function configureFilters(Filters $filters): Filters
  {
    return parent::configureFilters($filters)
      ->add('month')
      ->add('sourceID')
      ->add('sourceType')
      ->add('logLevel')
      ;
  }
}
