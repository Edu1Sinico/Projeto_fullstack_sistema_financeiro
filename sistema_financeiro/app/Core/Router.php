<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method: string, path: string, handler: callable|array{class-string, string}, auth: bool, web: bool}> */
    private array $routes = [];

    public function get(string $path, callable|array $handler, bool $auth = false, bool $web = false): void
    {
        $this->add('GET', $path, $handler, $auth, $web);
    }

    public function post(string $path, callable|array $handler, bool $auth = false): void
    {
        $this->add('POST', $path, $handler, $auth, false);
    }

    public function put(string $path, callable|array $handler, bool $auth = false): void
    {
        $this->add('PUT', $path, $handler, $auth, false);
    }

    public function delete(string $path, callable|array $handler, bool $auth = false): void
    {
        $this->add('DELETE', $path, $handler, $auth, false);
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $route['path']);
            if (!preg_match('#^'.$pattern.'/?$#', $request->path(), $matches)) {
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $request->setParams($params);
            if ($route['auth']) {
                Auth::authenticate($request, $route['web']);
            }

            $handler = $route['handler'];
            if (is_array($handler)) {
                $controller = new $handler[0]();
                $controller->{$handler[1]}($request);
            } else {
                $handler($request);
            }
            return;
        }

        throw new HttpException(404, 'Rota não encontrada.');
    }

    private function add(string $method, string $path, callable|array $handler, bool $auth, bool $web): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'auth', 'web');
    }
}
