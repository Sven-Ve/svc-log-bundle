<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Exception;

/**
 * @author Sven Vetter <dev@sv-systems.com>
 */
final class DailySummaryEmailNotValid extends \Exception implements LogExceptionInterface
{
    /**
     * @var string
     */
    protected $message = 'The destination email is not a valid email address.';

    public function getReason(): string
    {
        return $this->message;
    }
}
