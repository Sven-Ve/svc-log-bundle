<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LogControllerTest extends KernelTestCase
{
    public function testLogIndex(): void
    {
        $kernel = self::bootKernel();
        $client = new KernelBrowser($kernel);

        $client->request('GET', '/svc-log/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Log viewer', (string) $client->getResponse()->getContent());
    }
}
