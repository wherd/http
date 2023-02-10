<?php

declare(strict_types=1);

namespace Wherd\Http;

class Route
{
    /**
     * @param string|callable $callback
     * @param array<string|int> $arguments
     */
    public function __construct(protected $callback, protected array $arguments=[])
    {
    }

    public function dispatch(Request $request, Response $response): Response
    {
        $callback = $this->callback;

        if (is_string($callback) && false !== strpos($callback, '@')) {
            $parts = explode('@', $callback);

            $class = new $parts[0]($request, $response);
            $callback = [$class, $parts[1]];
        }

        if (is_callable($callback)) {
            return $callback(...$this->arguments);
        }

        $response->setStatusCode(404);
        $response->setContent('Not Found');
        return $response;
    }
}
