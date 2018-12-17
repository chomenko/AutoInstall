# AutoInstall

## Install

````sh
composer require chomenko/auto-install
````

## Configuration

```yaml
autoInstall:
	dirs:
	  - %rootDir%/app
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

## Use witch factory

Search class with prefix I

```php

use Chomenko\AutoInstall\AutoInstall;

class MyService implements AutoInstall
{
	
}

interface IMyService
{
	/**
	 * @return MyService
	 */
	public function create();
}

```

## Add service tag

```php

use Chomenko\AutoInstall\Tag;
use Chomenko\AutoInstall\AutoInstall;


/**
 * @Tag({"My.tag", "My.nextag"})
 */
class MyService implements AutoInstall
{
	
}
```