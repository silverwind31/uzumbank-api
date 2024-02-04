<?php

namespace Core;

class Router
{
    private static array $routes = [];
    public static function register(string $route, array $controller)
    {
        self::$routes[$route] = $controller;
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }
}