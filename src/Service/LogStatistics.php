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

  public function reportOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array
  {
    $request = $this->requestStack->getCurrentRequest();
    $offset = $request->query->get('offset') ?? 0;

    $logEntries = $this->svcLogRep->getLogPaginator($offset, $sourceID, $sourceType, $logLevel);
    if ($offset >= count($logEntries)) {
      $offset = count($logEntries) - SvcLogRepository::PAGINATOR_PER_PAGE;
      $logEntries = $this->svcLogRep->getLogPaginator($offset, $sourceID, $sourceType, $logLevel);
    }

    $routeName = $request->attributes->get('_route');
    $defRouteParam = $request->attributes->get('_route_params');

    $firstUrl = $this->router->generate($routeName, $defRouteParam);

    $prevRoutParam = $defRouteParam;
    $prevRoutParam['offset'] = max($offset - SvcLogRepository::PAGINATOR_PER_PAGE, 0);
    $prevUrl = $this->router->generate($routeName, $prevRoutParam);

    $nextRoutParam = $defRouteParam;
    $nextRoutParam['offset'] = min(count($logEntries), $offset + SvcLogRepository::PAGINATOR_PER_PAGE);
    $nextUrl = $this->router->generate($routeName, $nextRoutParam);

    $lastRoutParam = $defRouteParam;
    $lastRoutParam['offset'] = count($logEntries) - SvcLogRepository::PAGINATOR_PER_PAGE;
    $lastUrl = $this->router->generate($routeName, $lastRoutParam);

    $data = [];

    $data['records'] = $logEntries;
    $data['nextUrl'] = $nextUrl;
    $data['prevUrl'] = $prevUrl;
    $data['firstUrl'] = $firstUrl;
    $data['lastUrl'] = $lastUrl;
    $data['offset'] = $offset;
    $data['count'] = count($logEntries);
    $data['hidePrev'] = $offset <= 0;
    $data['hideNext'] = $offset >= count($logEntries) - SvcLogRepository::PAGINATOR_PER_PAGE;
    $data['from'] = $offset + 1;
    $data['to'] = $offset + SvcLogRepository::PAGINATOR_PER_PAGE;
    return $data;
  }
}
