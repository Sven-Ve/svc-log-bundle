<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Svc\LogBundle\Entity\SvcLogStatMonthly;
use Svc\LogBundle\Service\EventLog;

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
          ->showEntityActionsInlined()
          ->setDefaultSort([
              'id' => 'DESC',
          ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
          ->onlyOnIndex();
        yield TextField::new('month');
        yield NumberField::new('sourceID')
          ->setLabel('Source ID');
        yield NumberField::new('sourceType');
        yield ChoiceField::new('logLevel')
          ->setChoices(EventLog::getLevelsForChoices(false));
        yield NumberField::new('logCount')
          ->setLabel('Count');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
          ->disable(Action::NEW)
          ->disable(Action::DETAIL);
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
