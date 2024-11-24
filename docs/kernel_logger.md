# Kernel exception logger

## Usage
A event listener for logging kernel exceptions is installed by default (but not enabled). You can enable and configure it with the configuration parameters.

## Configuration
```yaml
# /config/packages/svc_log.yaml

# Configuration for the (optional) kernel exception logger
kernel_exception_logger:

    # enable the kernel exception logger
    use_kernel_logger:    false

    # Default log level (only 4..8 allowed)
    default_level:        5

    # Log level for critical errors - http code 500 (only 4..8 allowed)
    critical_level:       6
```