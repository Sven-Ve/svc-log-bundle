# Usage

## Entities
Create tables (run `bin/console doctrine:schema:update --force`) or create a migration

## Enable/disable feature

### general

```yaml
# /config/packages/svc_log.yaml
svc_log:

    # Minimal log level, see documentation for values
    min_log_level:        1 # Required

    # Should the ip address recorded? Please set to true only if this is allowed in your environment (personal data...)
    enable_ip_saving:     false


    # Should the user id and name recorded? Please set to true only if this is allowed in your environment (personal data...) and the security bundle is installed
    enable_user_saving:   false

    # Do you like different source types?
    enable_source_type:   true

    # We use offset as url parameter. If this in use, you can choose another name
    offset_param_name:    offset

    # Class of your own data provider to get info about sourceType and sourceID
    data_provider: Svc\LogBundle\DataProvider\GeneralDataProvider

when@prod:
    svc_log:
        # Minimal log level, see documentation for values - set to 3 (LEVEL_DATA) in production
        min_log_level:        3 # Required
```

## Log level

```php
namespace Svc\LogBundle\Service;
class EventLog
{
  public const LEVEL_ALL = 0;
  public const LEVEL_DEBUG = 1;
  public const LEVEL_INFO = 2;
  /**
   * data is a special log level to store access data (page views, ...)
   */
  public const LEVEL_DATA = 3;
  public const LEVEL_WARN = 4;
  public const LEVEL_ERROR = 5;
  public const LEVEL_FATAL = 6;

  ...
}
```

## Write log info

### Interface

```php
namespace Svc\LogBundle\Service;

class EventLog
{
  /**
   * write a log record
   *
   * @param integer $sourceID the ID of the source object
   * @param integer|null $sourceType the type of the source (entityA = 1, entityB = 2, ...) - These types must be managed by yourself, best is to set constants in the application
   * @param array|null $options
   *  - int level
   *  - string message
   * @return boolean true if successfull
   */
  public function log(int $sourceID, ?int $sourceType = 0, ?array $options = []): bool
```