<?php

declare(strict_types=1);

namespace Wherd\Http;

class Router
{
    /** @var array<string,array<string,string|callable>> */
    protected array $routes = [];

    /** @param array<array<string,mixed>> $routes */
    public function addRoutes(array $routes): self
    {
        foreach ($routes as $route) {
            $this->addRoute(...$route);
        }

        return $this;
    }

    public function dispatch(Request $request, Response $response): ?Response
    {
        $route = $this->findRoute($request->getMethod(), $request->getPath());

        if ($route) {
            return $route->dispatch($request, $response);
        }

        $response->setStatusCode(404);
        $response->setContent('Not Found');
        return $response;
    }

    /** @param string|callable $callback */
    public function addRoute(string $method, string $path, $callback): self
    {
        if (! isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $path = rtrim($path, '/');
        $this->routes[$method][$path] = $callback;
        return $this;
    }

    /** Find route that matches request. */
    public function findRoute(string $method, string $path): ?Route
    {
        $path = rtrim($path, '/');
        $routes = $this->routes[$method] ?? [];

        if (isset($routes[$path])) {
            return new Route($routes[$path]);
        }

        foreach ($routes as $pattern => $route) {
            if (false !== strpos($pattern, '<')) {
                $pattern = '#^' . str_replace(
                    ['<integer>', '<string>', '<any>'],
                    ['(\d*)', '([^\/]*)', '(.*)'],
                    $pattern
                ) . '$#i';

                if (preg_match($pattern, $path, $matches)) {
                    return new Route($route, array_slice($matches, 1));
                }
            }
        }

        if ('ANY' !== $method) {
            return $this->findRoute('ANY', $path);
        }

        return null;
    }

    /** @param string|callable $callback */
    public function get(string $path, $callback): self
    {
        $this->addRoute('HEAD', $path, $callback);
        return $this->addRoute('GET', $path, $callback);
    }

    /** @param string|callable $callback */
    public function post(string $path, $callback): self
    {
        return $this->addRoute('POST', $path, $callback);
    }

    /** @param string|callable $callback */
    public function put(string $path, $callback): self
    {
        return $this->addRoute('PUT', $path, $callback);
    }

    /** @param string|callable $callback */
    public function delete(string $path, $callback): self
    {
        return $this->addRoute('DELETE', $path, $callback);
    }
}
