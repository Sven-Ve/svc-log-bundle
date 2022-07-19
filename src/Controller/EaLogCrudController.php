<?php

namespace Svc\LogBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Service\EventLog;

class EaLogCrudController extends AbstractCrudController
{
  public static function getEntityFqcn(): string
  {
    return SvcLog::class;
  }

  public function configureCrud(Crud $crud): Crud
  {
    return parent::configureCrud($crud)
      ->setPageTitle(Crud::PAGE_INDEX, 'SvcLog')
      ->setHelp(Crud::PAGE_INDEX, 'show results of the logging table in a raw format')
      ->setDefaultSort([
        'id' => 'DESC',
      ]);
  }

  public function configureFields(string $pageName): iterable
  {
    yield FormField::addPanel('General info');
    yield IdField::new('id')
      ->onlyOnIndex();
    yield NumberField::new('sourceID')
      ->setColumns(5)
      ->setLabel('Source ID');
    yield NumberField::new('sourceType')
      ->setColumns(5);
    yield DateTimeField::new('logDate')
      ->setColumns(5);
    yield ChoiceField::new('logLevel')
      ->setColumns(5)
      ->setChoices(EventLog::getLevelsForChoices(false));
    yield TextField::new('message')
      ->setColumns(10);
    yield FormField::addPanel('Client info')->collapsible();
    yield TextField::new('ip')
      ->hideOnIndex()
      ->setColumns(5)
      ->setLabel('IP');
    yield TextField::new('userAgent')
      ->setColumns(5)
      ->hideOnIndex();
    yield TextField::new('country')
      ->setColumns(5)
      ->hideOnIndex();
    yield TextField::new('city')
      ->setColumns(5)
      ->hideOnIndex();
    yield TextField::new('platform')
      ->hideOnIndex();
    yield TextField::new('browser')
      ->setColumns(5)
      ->hideOnIndex();
    yield TextField::new('browserVersion')
      ->setColumns(5)
      ->hideOnIndex();
    yield TextField::new('referer')
      ->setColumns(10)
      ->hideOnIndex();
    yield NumberField::new('userID')
      ->setColumns(5)
      ->hideOnIndex()
      ->setLabel('User ID');
    yield TextField::new('userName')
      ->setColumns(5)
      ->hideOnIndex();
  }
}
