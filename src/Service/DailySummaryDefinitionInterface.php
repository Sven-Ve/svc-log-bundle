<?php

namespace Svc\LogBundle\Service;

/**
 * Interface for the daily summary definition.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
interface DailySummaryDefinitionInterface
{
  /**
   * get the text/description for a source type.
   *
   * @return \Svc\LogBundle\Entity\DailySumDef[]
   */
  public function getDefinition(): array;
}
