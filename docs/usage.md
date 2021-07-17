# Usage

## Entities
Create tables (run `bin/console doctrine:schema:update --force`) or create a migration


## Enable/disable feature
```yaml
# /config/packages/svc_log.yaml
svc_log:

    # Should the ip address recorded? Please set to true only if this is allowed in your environment (personal data...)
    enable_ip_saving:     false

    # Do you like different source types?
    enable_source_type:   true

    # We use offset as url parameter. If this in use, you can choose another name
    offset_param_name:    offset
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