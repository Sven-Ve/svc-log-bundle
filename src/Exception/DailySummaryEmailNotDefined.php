<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryEmailNotDefined extends \Exception implements LogExceptionInterface
{
    /**
     * @var string
     */
    protected $message = 'The destination email for the daily summary is not defined.';

    public function getReason(): string
    {
        return $this->message;
    }
}
