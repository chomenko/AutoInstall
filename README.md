# AutoInstall

## Install

````sh
composer require chomenko/auto-install
````

## Configuration

```yaml
extensions:
    autoInstall: Chomenko\AutoInstall\AutoInstallExtension
```

## Use

```php
use Chomenko\AutoInstall\AutoInstall;

class MyService implements AutoInstall
{

}
```
