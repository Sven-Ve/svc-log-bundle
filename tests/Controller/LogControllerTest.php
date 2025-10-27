<?php

declare(strict_types=1);

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LogControllerTest extends WebTestCase
{
    public function testLogIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/svc-log/');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Log viewer', (string) $client->getResponse()->getContent());
    }
}
