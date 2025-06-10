<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Enum;

enum DailySummaryType
{
    case LIST;
    case AGGR_LOG_LEVEL;
    case COUNT_SOURCE_TYPE;
}
