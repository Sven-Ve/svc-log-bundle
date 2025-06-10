<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
