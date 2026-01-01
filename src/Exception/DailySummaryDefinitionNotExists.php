<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryDefinitionNotExists extends \Exception implements LogExceptionInterface
{
    /**
     * @var string
     */
    protected $message = 'The definition class for the daily summary report does not exists.';

    public function getReason(): string
    {
        return $this->message;
    }
}
