<?php

namespace Core;

class Request
{
    public function __construct(
        public array $data,
        public string $method = 'GET',
        public string $uri = '/',
    )
    {

    }

    public static function capture(): self
    {
        return new static(
            file_get_contents('php://input') ? json_decode(file_get_contents('php://input'), true) : $_REQUEST,
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
        );
    }

    public function all(): array
    {
        return $this->data;
    }

    public function exists(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->data)) {
                return false;
            }
        }
        return true;
    }

    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return explode('?', $this->uri)[0];
    }

    public function header($key)
    {
        return getallheaders()[$key] ?? null;
    }
}