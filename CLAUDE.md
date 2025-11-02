# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing
- Run all tests: `composer test` (alias for `vendor/bin/phpunit --testdox`)
- Run PHPUnit directly: `vendor/bin/phpunit --testdox`
- Run specific test: `vendor/bin/phpunit --filter TestClassName`
- Run specific test file: `vendor/bin/phpunit tests/path/to/TestFile.php`

### Static Analysis
- Run PHPStan: `composer phpstan` (alias for `php -d memory_limit=-1 vendor/bin/phpstan analyse -c .phpstan.neon`)
- PHPStan analyzes `bin/`, `config/`, `src/`, and `tests/` directories at level 7
- Run PHP CS Fixer: `/opt/homebrew/bin/php-cs-fixer fix` (check: add `--dry-run --diff` flags)

### Console Commands (Bundle)
- Run monthly statistics: `bin/console svc:log:stats-monthly`
- Purge old logs: `bin/console svc:log:purge`
- Send daily summary email: `bin/console svc:log:mail-daily-summary`
- Batch fill location data: `bin/console svc:log:batch-fill-location`

### Other Commands
- Install dependencies: `composer install`
- Update dependencies: `composer update`

## Architecture Overview

### Core Components

**SvcLogBundle** is a Symfony bundle for logging events and statistics to a database. The bundle provides comprehensive logging functionality with device detection, user tracking (optional), and statistical analysis.

#### Main Services
- **EventLog** (`src/Service/EventLog.php`): Primary service for writing log entries to database
- **LoggerHelper** (`src/Service/LoggerHelper.php`): Handles integration with Symfony logger and external services
- **DailySummaryHelper** (`src/Service/DailySummaryHelper.php`): Generates daily summary reports and emails

#### Data Model
- **SvcLog** (`src/Entity/SvcLog.php`): Main entity storing log entries with device detection data, user info, and metadata
- **SvcLogStatMonthly** (`src/Entity/SvcLogStatMonthly.php`): Monthly statistics aggregation
- **DailySumDef** (`src/Entity/DailySumDef.php`): Daily summary definitions

#### Key Features
- **Log Levels**: Uses enum `LogLevel` with levels: DEBUG, INFO, DATA, WARN, ERROR, FATAL/CRITICAL, ALERT, EMERGENCY
- **Device Detection**: Automatic browser, OS, platform detection using matomo/device-detector
- **Privacy Controls**: Optional IP and user data saving (configurable via settings)
- **Data Provider Pattern**: Extensible system for enriching log entries with application-specific data
  - `DataProviderInterface` defines contract for custom data enrichment
  - `GeneralDataProvider` as default implementation
  - Custom providers configurable via bundle settings
- **Daily Summaries**: Automated daily summary generation and email notifications
- **Statistics**: Monthly statistics with purging capabilities
- **Frontend Integration**: Symfony UX components with Stimulus controllers for interactive log viewer

#### Console Commands
- **StatMonthlyCommand**: Generate monthly statistics
- **PurgeLogsCommand**: Clean up old log entries
- **MailDailySummary**: Send daily summary emails
- **BatchFillLocationCommand**: Batch process location data

#### Controllers
- **LogViewerController**: Web interface for viewing logs with filtering and search
- **DailySummaryController**: Display daily summaries
- **EaLogCrudController**: EasyAdmin integration for log management

### Configuration System
The bundle uses jbtronics/settings-bundle instead of traditional Symfony YAML configuration for persistent, runtime-configurable settings. Key configuration areas:
- **Log Level Filtering**: `min_log_level` setting controls which log levels are recorded
- **Privacy Settings**: `enable_ip_saving`, `enable_user_saving` for GDPR compliance
- **External Integrations**: Default logger forwarding
- **Exception Handling**: Kernel exception logging with customizable HTTP code handling
- **Data Provider**: Configurable class for custom log entry enrichment
- **Daily Summaries**: Email configuration and scheduling settings

### Testing Infrastructure
- **Unit Tests**: Pure unit tests for entities, enums, services in `tests/Unit/`
- **Integration Tests**: Controller and service integration tests in `tests/Controller/` and `tests/Service/`
- **Custom Testing Kernel**: `SvcLogTestingKernel` provides isolated test environment with SQLite in-memory database
- **PHPStan Configuration**: Level 7 analysis with specific exclusions for EasyAdmin controllers (see `.phpstan.neon`)
- **Coverage**: Comprehensive test coverage including edge cases and exception handling

## Bundle Integration Notes

This is a Symfony bundle that should be installed via Composer and registered in `config/bundles.php`. It requires:
- **PHP 8.4+** and **Symfony 7.3+**
- **Doctrine ORM** (supports v2.18+ and v3+)
- **jbtronics/settings-bundle** for configuration persistence
- **matomo/device-detector** for browser/OS detection
- **Symfony UX** components (Stimulus, Twig Components) for frontend functionality

### Bundle Features
- **Routes**: Provides routes under `/svc-log/` prefix for web interface (configured in `config/routes.php`)
- **Services**: Service container configuration (configured in `config/services.php`)
- **Templates**: Comprehensive Twig templates for log viewing and daily summaries
- **Assets**: Stimulus controllers for interactive frontend functionality (`assets/src/viewer_controller.js`)
- **EasyAdmin Integration**: Optional CRUD controllers (suggest easycorp/easyadmin-bundle)
- **Privacy by Design**: Configurable data retention and GDPR-compliant privacy controls

## Development Guidelines

### Code Quality Requirements
- **Testing**: All changes must pass `composer test` (PHPUnit with --testdox)
- **Static Analysis**: Code must pass `composer phpstan` (level 7 analysis)
- **PHPStan Exclusions**: EasyAdmin controllers are excluded from static analysis
- **Test Coverage**: New features require comprehensive unit and integration tests

### Architecture Patterns
- **Settings-Based Configuration**: Uses jbtronics/settings-bundle instead of YAML configs
- **Data Provider Pattern**: Implement `DataProviderInterface` for custom log enrichment
- **Privacy by Design**: Consider GDPR implications when adding user/IP tracking features
- **Frontend Integration**: Use Symfony UX patterns for interactive components

### Development Environment
- **Testing Database**: SQLite in-memory database via `SvcLogTestingKernel`
- **Frontend Assets**: Managed via Symfony AssetMapper with Stimulus controllers
- **Bundle Dependencies**: Requires multiple Symfony components and external libraries (see composer.json)
- **PHP/Symfony Versions**: Targets PHP 8.4+ and Symfony 7.3+

## Important Implementation Notes

### Configuration Architecture
The bundle uses a hybrid configuration approach:
- **Bundle Configuration**: Defined in `SvcLogBundle::configure()` using Symfony's configuration tree builder with `stringNode()`, `booleanNode()`, and `integerNode()` (not `scalarNode()`)
- **Runtime Settings**: Persistent configuration via jbtronics/settings-bundle stored in database or files
- **Examples**: Configuration nodes include example values using `->example()` method

### Bundle Route Integration
Routes are configured in PHP (not YAML) at `config/routes.php`. Projects must manually import routes:
```php
// config/routes/svc_log.yaml
_svc_log:
    resource: '@SvcLogBundle/config/routes.php'
    prefix: /admin/svc-log/
```

### Data Provider Pattern Details
The Data Provider system allows enrichment of log entries with human-readable descriptions:
- Interface: `DataProviderInterface` defines the contract
- Default: `GeneralDataProvider` provides base functionality with array-based caching
- Custom: Extend `GeneralDataProvider` and override `getSourceTypeText()` and `getSourceIDText()`
- Configuration: Set via `data_provider` bundle parameter, automatically aliased in DI container

### Testing Strategy
- **Unit Tests**: Focus on individual components in isolation (`tests/Unit/`)
- **Integration Tests**: Test service interactions and database operations (`tests/Service/`, `tests/Controller/`)
- **Custom Kernel**: `SvcLogTestingKernel` provides minimal test environment with all required bundles
- **Database**: Uses SQLite in-memory for fast test execution
- **Mocking**: Extensive use of PHPUnit mocks for external dependencies

### Key Design Patterns
- **Enum-based Log Levels**: Uses `LogLevel` enum instead of constants for type safety
- **Service-oriented Architecture**: Core functionality split into focused services
- **Event-driven Logging**: Kernel exception listener for automatic error capture
- **Template Component System**: Symfony UX Twig Components for reusable UI elements
- **Settings Persistence**: Runtime-configurable behavior without code deployment

### Email Template Development
Daily summary emails (`templates/daily_summary/`) are designed for maximum email client compatibility:
- **Inline Styles**: All critical styles must be inline (Outlook doesn't support `<style>` tags reliably)
- **No Gradients**: Use solid colors only - Outlook doesn't support CSS gradients
- **No :not() Selectors**: Outlook doesn't support advanced CSS selectors
- **Mobile Card Layout**: Tables with `log-detail-table` class convert to cards on mobile (≤768px)
- **Simple Tables**: Tables with `simple-table` class remain as tables on all devices
- **Legacy HTML Attributes**: Use `bgcolor` and `<font color="">` for best Outlook compatibility
- **Mobile Labels**: Use `<span class="mobile-label">` that shows only on mobile in card layout