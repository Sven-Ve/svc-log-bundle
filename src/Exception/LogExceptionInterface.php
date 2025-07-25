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
 * An exception that is thrown by SvcLogBundle.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
interface LogExceptionInterface extends \Throwable
{
    /**
     * Returns a safe string that describes why verification failed.
     */
    public function getReason(): string;
}
