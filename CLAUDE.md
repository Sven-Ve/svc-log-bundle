# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing
- Run all tests: `composer test` (alias for `vendor/bin/phpunit --testdox`)
- Run PHPUnit directly: `vendor/bin/phpunit --testdox`
- Run specific test: `vendor/bin/phpunit --filter TestClassName`

### Static Analysis
- Run PHPStan: `composer phpstan` (alias for `php -d memory_limit=-1 vendor/bin/phpstan analyse -c .phpstan.neon`)
- PHPStan analyzes `bin/`, `config/`, `src/`, and `tests/` directories at level 7

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
- **Daily Summaries**: Automated daily summary generation and email notifications
- **Statistics**: Monthly statistics with purging capabilities

#### Console Commands
- **StatMonthlyCommand**: Generate monthly statistics
- **PurgeLogsCommand**: Clean up old log entries
- **MailDailySummary**: Send daily summary emails
- **BatchFillLocationCommand**: Batch process location data

#### Controllers
- **LogViewerController**: Web interface for viewing logs with filtering and search
- **DailySummaryController**: Display daily summaries
- **EaLogCrudController**: EasyAdmin integration for log management

### Configuration
The bundle uses jbtronics/settings-bundle for persistent configuration. Key configuration areas:
- Log level filtering (`min_log_level`)
- Privacy settings (`enable_ip_saving`, `enable_user_saving`)
- External integrations (Sentry, default logger)
- Kernel exception logging
- Data provider class specification

### Testing Structure
- Unit tests for entities and services in `tests/Unit/`
- Integration tests for controllers in `tests/Controller/`
- Custom testing kernel: `SvcLogTestingKernel`
- Test configuration uses SQLite in-memory database

## Bundle Integration Notes

This is a Symfony bundle that should be installed via Composer and registered in `config/bundles.php`. It requires:
- Symfony 7.3+
- PHP 8.4+
- Doctrine ORM
- jbtronics/settings-bundle for configuration persistence

The bundle provides routes under `/svc-log/` prefix and includes Twig templates for the web interface.