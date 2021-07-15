<?php

namespace Svc\ParamBundle\Tests\Controller;

use Exception;
use Svc\ParamBundle\Tests\SvcParamTestingKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChangeMailControllerTest extends KernelTestCase
{

  public function testParamIndex()
  {
    $kernel = new SvcParamTestingKernel();
    $client = new KernelBrowser($kernel);

    try {
      $client->request('GET', '/svc-param/en/');
    } catch (Exception $e) {
      dump($e);
    }
    $this->assertSame(500, $client->getResponse()->getStatusCode());
  }

  /*   public function testContactFormContent()
  {
    $kernel = new SvcParamTestingKernel();
    $client = new KernelBrowser($kernel);
    $client->request('GET', '/api/en/contact');
    $this->assertStringContainsString("Contact", $client->getResponse()->getContent());
  }

  public function testContactFormContentDE()
  {
    $kernel = new SvcParamTestingKernel();
    $client = new KernelBrowser($kernel);
    $client->request('GET', '/api/de/contact');
    $this->assertStringContainsString("Kontakt", $client->getResponse()->getContent());
  } */
}
