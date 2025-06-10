<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\DataProvider;

/**
 * a general easy log provider, you have to extend from here.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class GeneralDataProvider implements DataProviderInterface
{
    /**
     * @var array<int,string>
     */
    protected array $sourceTypes = [];

    protected bool $isSourceTypesInitialized = false;

    /**
     * @var array<int,string>
     */
    protected array $sourceIDs = [];

    protected bool $isSourceIDsInitialized = false;

    /**
     * get the text/description for a source type.
     */
    public function getSourceTypeText(int $sourceType): string
    {
        if (!$this->isSourceTypesInitialized) {
            $this->initSourceTypes();
        }

        return array_key_exists($sourceType, $this->sourceTypes) ? $this->sourceTypes[$sourceType] : strval($sourceType);
    }

    /**
     * get the text/description for a source ID / sourceType combination.
     */
    public function getSourceIDText(int $sourceID, ?int $sourceType = null): string
    {
        if (!$this->isSourceIDsInitialized) {
            $this->initSourceIDs();
        }

        return array_key_exists($sourceID, $this->sourceIDs) ? $this->sourceIDs[$sourceID] : strval($sourceID);
    }

    /**
     * get all sourceIDs as array.
     *
     * @return array<int,string>
     */
    public function getSourceIDTextsArray(): array
    {
        if (!$this->isSourceIDsInitialized) {
            $this->initSourceIDs();
        }

        return $this->sourceIDs;
    }

    /**
     * get all sourceTypes as array.
     */
    public function getSourceTypeTextsArray(): array
    {
        if (!$this->isSourceTypesInitialized) {
            $this->initSourceTypes();
        }

        return $this->sourceTypes;
    }

    /**
     * init the sourceType array.
     */
    protected function initSourceTypes(): bool
    {
        if ($this->isSourceTypesInitialized) {
            return true;
        }
        $this->sourceTypes = [];

        return true;
    }

    /**
     * init the sourceIDs array.
     */
    protected function initSourceIDs(): bool
    {
        if ($this->isSourceIDsInitialized) {
            return true;
        }
        $this->sourceIDs = [];

        return true;
    }
}
