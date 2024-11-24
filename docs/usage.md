# Usage

## Parameter persistence

We use jbtronics/settings-bundle to store parameter/settings. This means, you have to define a storage adapter.

Exemple:

## Enable/disable feature
```yaml
# /config/packages/jbtronics_settings.yaml
jbtronics_settings:
    default_storage_adapter: Jbtronics\SettingsBundle\Storage\JSONFileStorageAdapter
```

For more options (like storing in ORM) see the [package documentation](https://jbtronics.github.io/settings-bundle/)

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
    data_provider:        null

    # Optional configuration for sentry.io, see documentation
    sentry:

        # Write log entries to sentry.io too
        use_sentry:           false

        # Minimal log level to write to sentry, see documentation for values (only 4..6 allowed)
        sentry_min_log_level: 6

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
  public const LEVEL_CRITICAL = 6; // same as FATAL
  public const LEVEL_ALERT = 7;
  public const LEVEL_EMERGENCY = 8;

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
   *  - string errorText
   * @return boolean true if successfull
   */
  public function log(int $sourceID, ?int $sourceType = 0, ?array $options = []): bool
```
