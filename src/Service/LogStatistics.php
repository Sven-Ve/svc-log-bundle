<?php

namespace Svc\LogBundle\Service;

use Svc\LogBundle\Repository\SvcLogRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Helper class for displaing statistics
 * 
 * @author Sven Vetter <dev@sv-systems.com>
 */
class LogStatistics
{

  private $svcLogRep;
  private $requestStack;
  private $router;

  public function __construct(SvcLogRepository $svcLogRep, RequestStack $requestStack, UrlGeneratorInterface $router)
  {
    $this->svcLogRep = $svcLogRep;
    $this->requestStack = $requestStack;
    $this->router = $router;
  }

  public function reportOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array {
    $request = $this->requestStack->getCurrentRequest();
    $offset = $request->query->get('offset') ?? 0;

    $routeName = $request->attributes->get('_route');
    $routeParameters = $request->attributes->get('_route_params');
    $routeParameters['offset'] = $offset+1;

    $url = $this->router->generate($routeName, $routeParameters);

    $data = [];

    $logEntries = $this->svcLogRep->getLogPaginator($offset,$sourceID, $sourceType);
    $data['records'] = $logEntries;
    $data['next'] = $url;
    return $data;
  }
}
