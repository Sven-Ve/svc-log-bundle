# Daily Summary

The Daily Summary feature allows you to generate automated reports of logged events and send them via email.

## Overview

Daily summaries provide:
- Automated counting of specific log events
- List generation of important logs (errors, critical issues)
- Email notifications with summary data
- Web interface for viewing summaries

## Creating Daily Summary Definitions

To use daily summaries, you need to create a class that implements `DailySummaryDefinitionInterface`:

```php
use Svc\LogBundle\Service\DailySummaryDefinitionInterface;
use Svc\LogBundle\Entity\DailySumDef;
use Svc\LogBundle\Enum\LogLevel;
use Svc\LogBundle\Enum\DailySummaryType;

class MyDailySummaryDefinition implements DailySummaryDefinitionInterface
{
    /**
     * @return DailySumDef[]
     */
    public function getDefinition(): array
    {
        $definitions = [];
        
        // Count user registrations
        $userRegistrations = new DailySumDef();
        $userRegistrations->setTitle('New User Registrations');
        $userRegistrations->setSourceType(1); // User entity type
        $userRegistrations->setLogLevel(LogLevel::INFO);
        $userRegistrations->setMessage('New user registered');
        $userRegistrations->setType(DailySummaryType::COUNT);
        $definitions[] = $userRegistrations;
        
        // List all errors
        $errors = new DailySumDef();
        $errors->setTitle('System Errors');
        $errors->setLogLevel(LogLevel::ERROR);
        $errors->setType(DailySummaryType::LIST);
        $definitions[] = $errors;
        
        return $definitions;
    }
}
```

## Configuration

Configure the daily summary system in your configuration:

```yaml
# config/packages/svc_log.yaml
svc_log:
    daily_summary:
        definition_class: App\Service\MyDailySummaryDefinition
        email_to: 'admin@example.com'
        email_from: 'noreply@example.com'
        email_subject: 'Daily System Summary'
```

## DailySumDef Entity Properties

The `DailySumDef` entity supports these properties:

- `title`: Display title for the summary item
- `sourceType`: Filter by source type (optional)
- `sourceID`: Filter by specific source ID (optional)
- `logLevel`: Filter by log level (optional)
- `message`: Filter by message content (optional)
- `type`: Summary type (`COUNT` or `LIST`)

## Summary Types

### COUNT Type
Counts the number of matching log entries:

```php
$countDef = new DailySumDef();
$countDef->setTitle('Page Views Today');
$countDef->setLogLevel(LogLevel::DATA);
$countDef->setType(DailySummaryType::COUNT);
```

### LIST Type
Lists individual matching log entries:

```php
$listDef = new DailySumDef();
$listDef->setTitle('Critical Issues');
$listDef->setLogLevel(LogLevel::CRITICAL);
$listDef->setType(DailySummaryType::LIST);
```

## Console Commands

### Send Daily Summary Email

```bash
# Send summary for yesterday
php bin/console svc:log:mail-daily-summary

# Send summary for specific date  
php bin/console svc:log:mail-daily-summary --date=2024-01-15

# Preview without sending (dry run)
php bin/console svc:log:mail-daily-summary --dry-run
```

## Web Interface

### Viewing Daily Summaries

Access daily summaries in your browser:

**Route**: `/svc-log/daily-summary`  
**Route Name**: `svc_log_daily_summary_view`

### Template Integration

Link to daily summary view in your templates:

```twig
{# Basic link #}
<a href="{{ path('svc_log_daily_summary_view') }}">View Daily Summary</a>

{# Raw format (without styling) #}
<a href="{{ path('svc_log_daily_summary_view', {'raw': true}) }}">Raw Daily Summary</a>

{# Specific date #}
<a href="{{ path('svc_log_daily_summary_view', {'date': '2024-01-15'}) }}">Summary for Jan 15, 2024</a>
```

### Parameters

The daily summary controller accepts these parameters:

- `date`: Specific date (YYYY-MM-DD format, defaults to yesterday)
- `raw`: Boolean, show raw format without styling (default: false)

## Advanced Examples

### Complex Filtering

```php
// Count failed login attempts
$failedLogins = new DailySumDef();
$failedLogins->setTitle('Failed Login Attempts');
$failedLogins->setSourceType(1); // User type
$failedLogins->setLogLevel(LogLevel::WARN);
$failedLogins->setMessage('Login failed');
$failedLogins->setType(DailySummaryType::COUNT);

// List database errors
$dbErrors = new DailySumDef();
$dbErrors->setTitle('Database Connection Errors');
$dbErrors->setLogLevel(LogLevel::ERROR);
$dbErrors->setMessage('Database connection failed');
$dbErrors->setType(DailySummaryType::LIST);
```

### Custom Email Templates

The daily summary emails use Twig templates. You can override them by creating:

- `@SvcLog/daily_summary/summary.html.twig` - Main email template
- `@SvcLog/daily_summary/_daily_counts.html.twig` - Count items
- `@SvcLog/daily_summary/_daily_list.html.twig` - List items

## Scheduling

Set up automated daily summaries using cron or Symfony Scheduler:

```bash
# Crontab entry (send at 8 AM daily)
0 8 * * * cd /path/to/project && php bin/console svc:log:mail-daily-summary
```

## Troubleshooting

### Common Issues

1. **No email received**: Check your email configuration and that the summary definition class is properly configured
2. **Empty summaries**: Verify that your log level filters and message patterns match actual log entries
3. **Definition not found**: Ensure your definition class implements `DailySummaryDefinitionInterface` and is properly registered

### Debug Mode

Use the `--dry-run` flag to test your configuration without sending emails:

```bash
php bin/console svc:log:mail-daily-summary --dry-run
```

This will output the summary content to the console instead of sending an email.
