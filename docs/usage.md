# Usage

## Parameter persistence

We use jbtronics/settings-bundle to store parameter/settings. This means, you have to define a storage adapter.

Example:

```yaml
# /config/packages/jbtronics_settings.yaml
jbtronics_settings:
    default_storage_adapter: Jbtronics\SettingsBundle\Storage\JSONFileStorageAdapter
```

For more options (like storing in ORM) see the [package documentation](https://jbtronics.github.io/settings-bundle/)

## Routing

* adapt the default url prefix in config/routes/svc_log.yaml 

```yaml
# /config/routes/svc_log.yaml
_svc_log:
    resource: '@SvcLogBundle/config/routes.php'
    prefix: /admin/svc-log/
```

## Enable/disable feature
### general

```yaml
# /config/packages/svc_log.yaml
svc_log:

    # Minimal log level, see documentation for values
    min_log_level:        1

    # Should the ip address recorded? Please set to true only if this is allowed in your environment (personal data...)
    enable_ip_saving:     false

    # Should the user id and name recorded? Please set to true only if this is allowed in your environment (personal data...)
    enable_user_saving:   false

    # Do you like different source types?
    enable_source_type:   true

    # Need the user the role ROLE_ADMIN for viewing logs (default yes)
    need_admin_for_view:  true

    # Need the user the role ROLE_ADMIN for get statistics (default yes)
    need_admin_for_stats: true

    # We use offset as url parameter. If this in use, you can choose another name
    offset_param_name:    offset

    # Class of your one data provider to get info about sourceType and sourceID, see documentation
    # Example: App\Service\LogDataProvider
    data_provider:        null

    # Optional configuration for default logger, see documentation
    logger:

        # Write log entries to default logger too
        use_logger:           false

        # Minimal log level to write to logger, see documentation for values (only 3..6 allowed)
        logger_min_log_level: 6

    # Configuration for the (optional) kernel exception logger
    kernel_exception_logger:

        # enable the kernel exception logger
        use_kernel_logger:    false

        # Default log level (only 4..8 allowed)
        default_level:        5

        # Log level for critical errors - http code 500 (only 4..8 allowed)
        critical_level:       6

        # do not send http 404 to logger (and maybe mail)
        disable_404_to_logger: false

        # adds an extra sleep after every 404 error (in seconds, max. 5)
        extra_sleep_time:     0
```

### Recommended setting

```yaml
# /config/packages/svc_log.yaml
when@prod:
    svc_log:
        # Minimal log level, see documentation for values - set to 3 (LEVEL_DATA) in production
        min_log_level:        3 # Required
```

## Log level

The bundle uses a `LogLevel` enum instead of constants:

```php
use Svc\LogBundle\Enum\LogLevel;

LogLevel::DEBUG     // 1 - Development debugging
LogLevel::INFO      // 2 - General information  
LogLevel::DATA      // 3 - Data access tracking (page views, etc.)
LogLevel::WARN      // 4 - Warning conditions
LogLevel::ERROR     // 5 - Error conditions
LogLevel::CRITICAL  // 6 - Critical conditions
LogLevel::ALERT     // 7 - Alert conditions
LogLevel::EMERGENCY // 8 - Emergency conditions
```

## Write log info

### Interface

```php
namespace Svc\LogBundle\Service;
use Svc\LogBundle\Enum\LogLevel;

class EventLog
{
  /**
   * write a log record.
   *
   * @param int               $sourceID        the ID of the source object
   * @param int|null          $sourceType      the type of the source (entityA = 1, entityB = 2, ...) - These types must be managed by yourself, best is to set constants in the application
   * @param LogLevel          $level           LogLevel enum value
   * @param string|null       $message         Log message (max 254 characters)
   * @param string|null       $errorText       Error details (max 254 characters)
   * @param int|null          $httpStatusCode  HTTP status code for web requests
   * @return bool true if successfully written
   */
  public function writeLog(
    int $sourceID,
    ?int $sourceType = 0,
    LogLevel $level = LogLevel::DATA,
    ?string $message = null,
    ?string $errorText = null,
    ?int $httpStatusCode = null,
  ): bool
```
