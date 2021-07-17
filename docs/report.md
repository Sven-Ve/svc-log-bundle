
## Example for show a log table

### Create a controller

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

### Create a twig template

include in the your twig template the table:

```twig
...
   {{ include('@SvcLog/stats/_stats.html.twig') }}
...
```