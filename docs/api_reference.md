# API Reference

Quick reference for developers integrating with SvcLogBundle.

## Core Services

### EventLog Service

Primary service for logging events.

```php
use Svc\LogBundle\Service\EventLog;
use Svc\LogBundle\Enum\LogLevel;

// Basic logging
$eventLog->writeLog(
    sourceID: 123,
    sourceType: 1,
    level: LogLevel::INFO,
    message: 'User action performed'
);

// Full parameter logging
$eventLog->writeLog(
    sourceID: 456,
    sourceType: 2,
    level: LogLevel::ERROR,
    message: 'Operation failed',
    errorText: 'Database connection timeout',
    httpStatusCode: 500
);
```

### LogStatistics Service

```php
use Svc\LogBundle\Service\LogStatistics;

// Get counts
$totalLogs = $logStatistics->getTotalLogCount();
$errorCount = $logStatistics->getLogCountByLevel(LogLevel::ERROR);

// Get recent logs
$recentLogs = $logStatistics->getRecentLogs(50);
```

## Enums

### LogLevel

```php
use Svc\LogBundle\Enum\LogLevel;

LogLevel::DEBUG     // 1
LogLevel::INFO      // 2  
LogLevel::DATA      // 3
LogLevel::WARN      // 4
LogLevel::ERROR     // 5
LogLevel::CRITICAL  // 6
LogLevel::ALERT     // 7
LogLevel::EMERGENCY // 8
```

### DailySummaryType

```php
use Svc\LogBundle\Enum\DailySummaryType;

DailySummaryType::COUNT // Count matching entries
DailySummaryType::LIST  // List matching entries
```

### ComparisonOperator

```php
use Svc\LogBundle\Enum\ComparisonOperator;

ComparisonOperator::EQUAL              // =
ComparisonOperator::GREATER_THAN       // >
ComparisonOperator::LESS_THAN          // <
ComparisonOperator::GREATER_THAN_EQUAL // >=
ComparisonOperator::LESS_THAN_EQUAL    // <=
ComparisonOperator::NOT_EQUAL          // !=
```

## Interfaces

### DataProviderInterface

```php
use Svc\LogBundle\DataProvider\DataProviderInterface;

interface DataProviderInterface
{
    public function getSourceTypeText(int $sourceType): string;
    public function getSourceIDText(int $sourceID, ?int $sourceType = null): string;
    public function getSourceIDTextsArray(): array;
    public function getSourceTypeTextsArray(): array;
}
```

### DailySummaryDefinitionInterface

```php
use Svc\LogBundle\Service\DailySummaryDefinitionInterface;

interface DailySummaryDefinitionInterface
{
    /**
     * @return \Svc\LogBundle\Entity\DailySumDef[]
     */
    public function getDefinition(): array;
}
```

## Entities

### SvcLog

Main log entry entity:

```php
use Svc\LogBundle\Entity\SvcLog;

// Key properties
$log->getSourceID(): int
$log->getSourceType(): ?int
$log->getLogLevel(): LogLevel
$log->getMessage(): ?string
$log->getErrorText(): ?string
$log->getIp(): ?string
$log->getUserID(): ?int
$log->getUserName(): ?string
$log->getBrowser(): ?string
$log->getOs(): ?string
$log->isMobile(): bool
$log->isBot(): bool
$log->getCreatedAt(): \DateTimeInterface
```

### DailySumDef

Daily summary definition entity:

```php
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Enum\DailySummaryType;
use Svc\LogBundle\Enum\LogLevel;

$definition = new DailySumDef();
$definition->setTitle('Summary Title');
$definition->setSourceType(1);
$definition->setSourceID(123);
$definition->setLogLevel(LogLevel::ERROR);
$definition->setMessage('Error message pattern');
$definition->setType(DailySummaryType::COUNT);
```

## Routes

### Web Interface Routes

```php
// Log viewer
route: 'svc_log_viewer'
path: '/svc-log/viewer'

// Log viewer data (AJAX)
route: 'svc_log_viewer_data'
path: '/svc-log/viewer/data'

// Log detail (AJAX)
route: 'svc_log_viewer_detail'
path: '/svc-log/viewer/detail/{id}'

// Daily summary view
route: 'svc_log_daily_summary_view'
path: '/svc-log/daily-summary'
```

## Console Commands

```bash
# Generate monthly statistics
php bin/console svc:log:stat-monthly [--year=YYYY] [--month=MM]

# Purge old logs
php bin/console svc:log:purge --days=90 [--dry-run]

# Send daily summary email
php bin/console svc:log:mail-daily-summary [--date=YYYY-MM-DD] [--dry-run]

# Batch fill location data
php bin/console svc:log:batch-fill-location
```

## Configuration Keys

```yaml
svc_log:
    min_log_level: 3              # Minimum log level to store
    enable_ip_saving: false       # Save IP addresses
    enable_user_saving: true      # Save user information
    need_admin_for_view: true     # Require ROLE_ADMIN for log viewer
    need_admin_for_stats: true    # Require ROLE_ADMIN for statistics
    data_provider: null           # Custom data provider class
    
    sentry:
        use_sentry: false         # Enable Sentry integration
        sentry_min_log_level: 6   # Minimum level for Sentry
    
    logger:
        use_logger: false         # Enable Symfony logger integration
        logger_min_log_level: 6   # Minimum level for logger
    
    kernel_exception_logger:
        use_kernel_logger: false  # Enable exception logging
        default_level: 5          # Default log level for exceptions
        critical_level: 6         # Level for critical exceptions (HTTP 500)
        disable_404_to_logger: true # Don't log 404 errors
```

## Quick Examples

### Basic Event Logging

```php
// Page view tracking
$eventLog->writeLog(
    sourceID: $pageId,
    sourceType: PageConstants::TYPE_PAGE,
    level: LogLevel::DATA,
    message: 'Page viewed'
);

// User action
$eventLog->writeLog(
    sourceID: $userId,
    sourceType: UserConstants::TYPE_USER,
    level: LogLevel::INFO,
    message: 'Profile updated'
);

// Error logging
$eventLog->writeLog(
    sourceID: 0,
    level: LogLevel::ERROR,
    message: 'Payment processing failed',
    errorText: $exception->getMessage()
);
```

### Data Provider Implementation

```php
class MyDataProvider extends GeneralDataProvider
{
    public function getSourceTypeText(int $sourceType): string
    {
        return match($sourceType) {
            1 => 'User',
            2 => 'Product',
            3 => 'Order',
            default => 'Unknown'
        };
    }
    
    public function getSourceIDText(int $sourceID, ?int $sourceType = null): string
    {
        return match($sourceType) {
            1 => $this->getUserName($sourceID),
            2 => $this->getProductName($sourceID),
            default => (string) $sourceID
        };
    }
}
```

### Daily Summary Definition

```php
class MyDailySummary implements DailySummaryDefinitionInterface
{
    public function getDefinition(): array
    {
        return [
            (new DailySumDef())
                ->setTitle('New Users')
                ->setSourceType(1)
                ->setLogLevel(LogLevel::INFO)
                ->setMessage('User registered')
                ->setType(DailySummaryType::COUNT),
                
            (new DailySumDef())
                ->setTitle('System Errors')
                ->setLogLevel(LogLevel::ERROR)
                ->setType(DailySummaryType::LIST)
        ];
    }
}
```