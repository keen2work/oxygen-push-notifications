# Change Log


## Version Compatibility

| Laravel Version | Package Version | Branch       |
|-----------------|-----------------|--------------|
| v10             | 4.x             | master       | 
| v9              | 3.x             | master       |
| v8              | 2.x             | 2.x          |
| v7              | 1.x             | version/v1.x |
| v6              | 0.2.x           |              |
| v5.x            | 0.1.x           |              |


## v3.0.0
- PHP 8 support
- Added support for Laravel 9
  
## v4.0.0
- Added support for Laravel 10

## v1.1

- For improve privacy, `device_id`, `device_type`, `device_push_token` moved to header params as `x-device-id`, `x-device-push-token`, `x-device-id`
- These parameters used to be on QUERY, so change them to be accepted as header parameters.
