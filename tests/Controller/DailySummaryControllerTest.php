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

class DailySummaryControllerTest extends WebTestCase
{
    public function testDailySummaryRouteRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/svc-log/daily_summary');

        // Route exists but requires authentication (ROLE_ADMIN)
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('authentication', strtolower($content));
    }

    public function testDailySummaryRouteWithRawParameterRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/svc-log/daily_summary?raw=1');

        // Route exists but requires authentication (ROLE_ADMIN)
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('authentication', strtolower($content));
    }

    public function testControllerConstructor(): void
    {
        // Test that we can instantiate the controller with a mock service
        $dailySummaryHelper = $this->createMock(\Svc\LogBundle\Service\DailySummaryHelper::class);
        $controller = new \Svc\LogBundle\Controller\DailySummaryController($dailySummaryHelper);

        $this->assertInstanceOf(\Svc\LogBundle\Controller\DailySummaryController::class, $controller);
    }
}
