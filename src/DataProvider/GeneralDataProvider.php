<?php

namespace Svc\LogBundle\DataProvider;

/**
 * a general easy log provider, you have to extends from here
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class GeneralDataProvider implements DataProviderInterface
{

  protected $sourceTypes = [];
  protected $isSourceTypesInitialized = false;

  /**
   * get the text/description for a source type
   *
   * @param integer $sourceType
   * @return string
   */
  public function getSourceTypeText(int $sourceType): string
  {
    if (!$this->isSourceTypesInitialized) {
      $this->initSourceTypes();
    }
    return array_key_exists($sourceType, $this->sourceTypes) ? $this->sourceTypes[$sourceType] : $sourceType;
  }


  /**
   * get the text/description for a source ID / sourceType combination
   *
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @return string
   */
  public function getSourceIDText(int $sourceID, ?int $sourceType = null): string
  {
    return strval($sourceID);
  }

  /**
   * get all sourceIDs as array
   *
   * @return array
   */
  public function getSourceIDTextsArray(): array
  {
    return [];
  }

  /**
   * get all sourceTypes as array
   *
   * @return array
   */
  public function getSourceTypeTextsArray(): array
  {
    if (!$this->isSourceTypesInitialized) {
      $this->initSourceTypes();
    }
    return $this->sourceTypes;
  }

  /**
   * init the sourceType array
   *
   * @return boolean
   */
  protected function initSourceTypes(): bool
  {
    if ($this->isSourceTypesInitialized) {
      return true;
    }
    $this->sourceTypes=[];
    return true;
  }
}
