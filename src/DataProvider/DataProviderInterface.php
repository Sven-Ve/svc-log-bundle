<?php

namespace Svc\LogBundle\DataProvider;

interface DataProviderInterface
{

  /**
   * get the text/description for a source type
   *
   * @param integer $sourceType
   * @return string
   */
  public function getSourceTypeText(int $sourceType): string;

  /**
   * get the text/description for a source ID
   *
   * @param integer $sourceID
   * @return string
   */
  public function getSourceIDText(int $sourceID): string;

  /**
   * get all sourceIDs as array
   *
   * @return array
   */
  public function getSourceIDTextsArray(): array;

  /**
   * get all sourceTypess as array
   *
   * @return array
   */
  public function getSourceTypeTextsArray(): array;

}