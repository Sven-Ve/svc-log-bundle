# Reports

### Option 1: Use the LogViewer with Ajax filtering (complete out-of-the-box controller)

Call the route "svc_log_viewer_view" in your application (twig template)

### Option 2: Integrate the log results in your application ("modern way" with ajax)

In your twig template include the template "@SvcLog/log_viewer/_table.html.twig" with this parameters:
* showFilter (default true): show the filter for sourceID, sourceType, logLevel, country and allow ajax based filtering
* sourceID (default null): display a sourceID or all, if null
* sourceType (default null): display a sourceType or all, if null
* logLevel (default null): display a logLevel or all, if null or null (see logLevel definitions [here](usage.md))
* hideSourceCols (default false): hide sourceID and sourceType in the result table

**Example**

```twig
  {{ include("@SvcLog/log_viewer/_table.html.twig", {
     'showFilter': false, 
     'sourceID': sourceID, 
     'sourceType': sourceType, 
     'logLevel': logLevel, 
     'hideSourceCols': true
      }) 
  }}
```

### Option 3: Integrate the log results in your application (classic way without ajax)

#### Create a controller

add a parameter logData to the twig-render function and call the function reportOneId() with sourceID, sourceType and (optional) logLevel

```php
use Svc\LogBundle\Service\LogStatistics;

  /**
   * show statistics
   */
  public function stats(Video $video, LogStatistics $logStatistics): Response
  {
    return $this->render('stats.html.twig', [
      'video' => $video,
      'logData' => $logStatistics->reportOneId($video->getId(), VideoController::OBJ_TYPE_VIDEO)
    ]);    
  }
  ```

#### Create a twig template

include in your twig template the table:

```twig
...
   {{ include('@SvcLog/stats/_stats.html.twig') }}
...
```

### Option 4: Direct access on log data

you can direct query the logdata for a specific ID within a sourceType and (optional) a specific logLevel

```php
use Svc\LogBundle\Service\LogStatistics;

  /**
   * give an array with log entries for one sourceID
   *
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @param integer|null $logLevel
   * @return array
   */
  public function reportOneId(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA): array 
```

### Option 5: Access via EasyAdmin (in development)

If you have EasyAdmin installed, you can use a small admin interface there (but only with raw data)

Example:<br/>
```php
use Svc\LogBundle\Controller\EaLogCrudController;
use Svc\LogBundle\Controller\EaLogStatMonthlyCrudController;
use Svc\LogBundle\Entity\SvcLog;
use Svc\LogBundle\Entity\SvcLogStatMonthly;

class DashboardController extends AbstractDashboardController
{
  public function configureMenuItems(): iterable
  {
    ...
    yield MenuItem::section('Logs');
    yield MenuItem::linkToCrud('Logs (raw)', 'fas fa-binoculars', SvcLog::class);
    yield MenuItem::linkToCrud('Logs (aggr.)', 'fa-solid fa-chart-pie', SvcLogStatMonthly::class);
```
