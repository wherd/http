# Http

Yet another http wrapper.

## Installation

Install using composer:

```bash
composer require wherd/http
```

# Usage

```php
use Wherd\Http\Kernel;
use Wherd\Http\Router;
use Wherd\Http\Request;
use Wherd\Http\Response;

// ...

$router = new Router();
$router->addRoutes(/* ... */);

$kernel = new Kernel();
$kernel->register($router);
$kernel->dispatch(new Request, new Response)->send();
```