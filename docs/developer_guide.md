# Developer Guide

This guide provides comprehensive information for developers who want to integrate, extend, or contribute to the SvcLogBundle.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Core Services](#core-services)
3. [Event Logging API](#event-logging-api)
4. [Data Providers](#data-providers)
5. [Daily Summary System](#daily-summary-system)
6. [Web Interface Integration](#web-interface-integration)
7. [Console Commands](#console-commands)
8. [Testing](#testing)
9. [Contributing](#contributing)

## Quick Start

### Basic Logging

```php
use Svc\LogBundle\Service\EventLog;
use Svc\LogBundle\Enum\LogLevel;

class MyController
{
    public function __construct(
        private EventLog $eventLog
    ) {}
    
    public function someAction(int $userId): Response
    {
        // Log a user action
        $this->eventLog->writeLog(
            sourceID: $userId,
            sourceType: 1, // User entity type
            level: LogLevel::INFO,
            message: 'User viewed dashboard'
        );
        
        // Log an error
        $this->eventLog->writeLog(
            sourceID: $userId,
            sourceType: 1,
            level: LogLevel::ERROR,
            message: 'Failed to load user data',
            errorText: 'Database connection timeout'
        );
        
        return $this->render('dashboard.html.twig');
    }
}
```

### Service Injection

Register the EventLog service in your controllers or services:

```php
// In your controller or service
public function __construct(
    private EventLog $eventLog,
    private LogStatistics $logStatistics // For statistics
) {}
```

## Core Services

### EventLog Service

The main service for writing log entries to the database.

**Location**: `src/Service/EventLog.php`

**Key Methods**:

```php
/**
 * Write a log record
 *
 * @param int      $sourceID        The ID of the source object
 * @param int|null $sourceType      The type of the source (use constants)
 * @param LogLevel $level          Log level (DEBUG, INFO, DATA, etc.)
 * @param string|null $message     Log message (max 254 chars)
 * @param string|null $errorText   Error details (max 254 chars)
 * @param int|null $httpStatusCode HTTP status code for web requests
 * @return bool Success status
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

**Features**:
- Automatic device detection (browser, OS, mobile/desktop)
- IP address logging (if enabled)
- User information capture (if enabled)
- Bot detection
- Integration with Symfony Logger and Sentry

### LogStatistics Service

Provides statistical data about logged events.

**Location**: `src/Service/LogStatistics.php`

**Key Methods**:

```php
// Get total log count
public function getTotalLogCount(): int

// Get count by log level
public function getLogCountByLevel(LogLevel $level): int

// Get logs for specific source
public function getLogsForSource(int $sourceID, ?int $sourceType = null): array

// Get recent logs
public function getRecentLogs(int $limit = 100): array
```

### LoggerHelper Service

Integrates with external logging systems (Symfony Logger, Sentry).

**Location**: `src/Service/LoggerHelper.php`

## Event Logging API

### Log Levels

The bundle uses a custom `LogLevel` enum:

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

### Source Types

Use constants in your application to define source types:

```php
class MyAppConstants
{
    public const SOURCE_TYPE_USER = 1;
    public const SOURCE_TYPE_PRODUCT = 2;
    public const SOURCE_TYPE_ORDER = 3;
    public const SOURCE_TYPE_ARTICLE = 4;
}
```

### Advanced Logging Examples

```php
// Log user registration
$this->eventLog->writeLog(
    sourceID: $user->getId(),
    sourceType: MyAppConstants::SOURCE_TYPE_USER,
    level: LogLevel::INFO,
    message: 'New user registered'
);

// Log product view with tracking
$this->eventLog->writeLog(
    sourceID: $product->getId(),
    sourceType: MyAppConstants::SOURCE_TYPE_PRODUCT,
    level: LogLevel::DATA,
    message: 'Product viewed'
);

// Log critical error with HTTP status
$this->eventLog->writeLog(
    sourceID: 0,
    sourceType: null,
    level: LogLevel::CRITICAL,
    message: 'Database connection failed',
    errorText: $exception->getMessage(),
    httpStatusCode: 500
);
```

## Data Providers

Data providers allow you to enrich log entries with human-readable descriptions.

### Creating a Data Provider

1. Create a class that extends `GeneralDataProvider`:

```php
use Svc\LogBundle\DataProvider\GeneralDataProvider;

class MyLogDataProvider extends GeneralDataProvider
{
    public function __construct(
        private UserRepository $userRepository,
        private ProductRepository $productRepository
    ) {}
    
    /**
     * Get text description for source types
     */
    public function getSourceTypeText(int $sourceType): string
    {
        return match($sourceType) {
            MyAppConstants::SOURCE_TYPE_USER => 'User',
            MyAppConstants::SOURCE_TYPE_PRODUCT => 'Product',
            MyAppConstants::SOURCE_TYPE_ORDER => 'Order',
            default => 'Unknown (' . $sourceType . ')'
        };
    }
    
    /**
     * Get text description for specific source IDs
     */
    public function getSourceIDText(int $sourceID, ?int $sourceType = null): string
    {
        return match($sourceType) {
            MyAppConstants::SOURCE_TYPE_USER => $this->getUserText($sourceID),
            MyAppConstants::SOURCE_TYPE_PRODUCT => $this->getProductText($sourceID),
            default => (string) $sourceID
        };
    }
    
    private function getUserText(int $userId): string
    {
        $user = $this->userRepository->find($userId);
        return $user ? $user->getUsername() : "User #{$userId}";
    }
    
    private function getProductText(int $productId): string
    {
        $product = $this->productRepository->find($productId);
        return $product ? $product->getName() : "Product #{$productId}";
    }
}
```

2. Configure the data provider:

```yaml
# config/packages/svc_log.yaml
svc_log:
    # Example: App\Service\LogDataProvider
    data_provider: App\Service\MyLogDataProvider
```

### Alternative: Array-based Data Provider

For static mappings, override `initSourceTypes()` and `initSourceIDs()`:

```php
protected function initSourceTypes(): bool
{
    if ($this->isSourceTypesInitialized) {
        return true;
    }
    
    $this->sourceTypes[MyAppConstants::SOURCE_TYPE_USER] = 'User';
    $this->sourceTypes[MyAppConstants::SOURCE_TYPE_PRODUCT] = 'Product';
    $this->sourceTypes[MyAppConstants::SOURCE_TYPE_ORDER] = 'Order';
    
    $this->isSourceTypesInitialized = true;
    return true;
}
```

## Daily Summary System

The daily summary system allows you to generate automated reports and send them via email.

### Creating Daily Summary Definitions

1. Implement the `DailySummaryDefinitionInterface`:

```php
use Svc\LogBundle\Service\DailySummaryDefinitionInterface;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Enum\ComparisonOperator;
use Svc\LogBundle\Enum\DailySummaryType;

class MyDailySummaryDefinition implements DailySummaryDefinitionInterface
{
    /**
     * @return DailySumDef[]
     */
    public function getDefinition(): array
    {
        $definitions = [];
        
        // Count all user registrations (INFO level, source type 1)
        $userRegistrations = new DailySumDef();
        $userRegistrations->setTitle('New User Registrations');
        $userRegistrations->setSourceType(MyAppConstants::SOURCE_TYPE_USER);
        $userRegistrations->setLogLevel(LogLevel::INFO);
        $userRegistrations->setMessage('New user registered');
        $userRegistrations->setType(DailySummaryType::COUNT);
        $definitions[] = $userRegistrations;
        
        // Count errors
        $errors = new DailySumDef();
        $errors->setTitle('System Errors');
        $errors->setLogLevel(LogLevel::ERROR);
        $errors->setType(DailySummaryType::COUNT);
        $definitions[] = $errors;
        
        // List critical issues
        $critical = new DailySumDef();
        $critical->setTitle('Critical Issues');
        $critical->setLogLevel(LogLevel::CRITICAL);
        $critical->setType(DailySummaryType::LIST);
        $definitions[] = $critical;
        
        return $definitions;
    }
}
```

2. Configure the daily summary:

```yaml
# config/packages/svc_log.yaml
svc_log:
    daily_summary:
        definition_class: App\Service\MyDailySummaryDefinition
        destination_email: 'admin@example.com'
        mail_subject: 'Daily System Summary'
```

### Running Daily Summaries

Use the console command to generate and send summaries:

```bash
# Generate summary for yesterday
php bin/console svc:log:mail-daily-summary

# Generate summary for specific date
php bin/console svc:log:mail-daily-summary --date=2024-01-15

# Preview without sending email
php bin/console svc:log:mail-daily-summary --dry-run
```

## Web Interface Integration

### Log Viewer

The bundle provides a web interface for viewing logs at `/svc-log/viewer`.

**Controller**: `LogViewerController`
**Route Name**: `svc_log_viewer`

**Access Control**: By default, requires `ROLE_ADMIN`. Configure with:

```yaml
svc_log:
    need_admin_for_view: false  # Allow any authenticated user
```

### Daily Summary Web View

View daily summaries in the browser at `/svc-log/daily-summary`.

**Route Name**: `svc_log_daily_summary_view`

### EasyAdmin Integration

If you use EasyAdmin, the bundle provides CRUD controllers:

- `EaLogCrudController` - Manage log entries
- `EaLogStatMonthlyCrudController` - View monthly statistics

Register them in your EasyAdmin dashboard:

```php
public function configureMenuItems(): iterable
{
    yield MenuItem::linkToCrud('System Logs', 'fa fa-list', SvcLog::class);
    yield MenuItem::linkToCrud('Log Statistics', 'fa fa-chart-bar', SvcLogStatMonthly::class);
}
```

## Console Commands

### Generate Monthly Statistics

```bash
# Generate stats for current month
php bin/console svc:log:stat-monthly

# Generate stats for specific month
php bin/console svc:log:stat-monthly --year=2024 --month=1
```

### Purge Old Logs

```bash
# Purge logs older than 90 days
php bin/console svc:log:purge --days=90

# Dry run (preview what would be deleted)
php bin/console svc:log:purge --days=90 --dry-run
```

### Batch Fill Location Data

```bash
# Process location data for IP addresses
php bin/console svc:log:batch-fill-location
```

## Testing

### Test Environment Setup

The bundle includes a testing kernel for isolated testing:

```php
use Svc\LogBundle\Tests\SvcLogTestingKernel;

class MyTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return SvcLogTestingKernel::class;
    }
    
    public function testEventLogging(): void
    {
        self::bootKernel();
        $eventLog = self::getContainer()->get(EventLog::class);
        
        $result = $eventLog->writeLog(
            sourceID: 123,
            level: LogLevel::INFO,
            message: 'Test message'
        );
        
        $this->assertTrue($result);
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test class
vendor/bin/phpunit tests/Service/EventLogTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Static Analysis

```bash
# Run PHPStan
composer phpstan

# Run with specific level
vendor/bin/phpstan analyse -l 8
```

## Contributing

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Run static analysis: `composer phpstan`

### Code Standards

- Follow PSR-12 coding standards
- Use PHP 8.4+ features
- Write comprehensive tests for new features
- Update documentation for API changes

### Pull Request Process

1. Create a feature branch from `main`
2. Write tests for your changes
3. Ensure all tests pass
4. Update documentation if needed
5. Submit a pull request with a clear description

### Architecture Guidelines

- Keep services focused and single-purpose
- Use dependency injection for all dependencies
- Follow Symfony best practices
- Maintain backward compatibility when possible
- Use type hints and return types consistently

## Configuration Reference

### Complete Configuration Example

```yaml
# config/packages/svc_log.yaml
svc_log:
    # Minimum log level to store (1-8)
    min_log_level: 3
    
    # Privacy settings
    enable_ip_saving: false
    enable_user_saving: true
    
    # UI settings
    need_admin_for_view: true
    need_admin_for_stats: true
    offset_param_name: 'offset'
    
    # Data provider for enriching log display
    data_provider: App\Service\MyLogDataProvider
    
    # External integrations
    logger:
        use_logger: true
        logger_min_log_level: 4
    
    # Exception handling
    kernel_exception_logger:
        use_kernel_logger: true
        default_level: 5
        critical_level: 6
        disable_404_to_logger: true
```

For more details on configuration options, see [Usage Documentation](usage.md).