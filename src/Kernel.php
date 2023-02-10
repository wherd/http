<?php

declare(strict_types=1);

namespace Wherd\Http;

use SplPriorityQueue;

class Kernel
{
    /** @var SplPriorityQueue<int,callable> */
    protected SplPriorityQueue $middleware;

    public function __construct()
    {
        $this->middleware = new SplPriorityQueue();
    }

    public function register(callable $callback, int $priority = 10): void
    {
        $this->middleware->insert($callback, $priority);
    }

    /** Handle incoming request. */
    public function dispatch(Request $request, Response $response): Response
    {
        while ($this->middleware->valid()) {
            $handler = $this->middleware->current();

            if (is_callable($handler)) {
                $response = $handler($request, $response);
            }

            $this->middleware->next();
        }

        return $response;
    }
}
