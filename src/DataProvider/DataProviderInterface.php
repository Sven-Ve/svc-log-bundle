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
   * get the text/description for a source ID / sourceType combination
   *
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @return string
   */
  public function getSourceIDText(int $sourceID, ?int $sourceType = null): string;

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