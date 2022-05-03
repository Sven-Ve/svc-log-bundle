<?php

namespace Svc\LogBundle\DataProvider;

/**
 * Interface for the log provider.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
interface DataProviderInterface
{
  /**
   * get the text/description for a source type.
   */
  public function getSourceTypeText(int $sourceType): string;

  /**
   * get the text/description for a source ID / sourceType combination.
   */
  public function getSourceIDText(int $sourceID, ?int $sourceType = null): string;

  /**
   * get all sourceIDs as array.
   */
  public function getSourceIDTextsArray(): array;

  /**
   * get all sourceTypess as array.
   */
  public function getSourceTypeTextsArray(): array;
}
