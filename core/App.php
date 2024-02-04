<?php

namespace Core;

use Config;

class App
{
    public function run(Request $request): Response
    {
        $routes = Router::getRoutes();
        header('Content-Type: application/json');
        if($request->header('Authorization') != 'Basic ' . Config::AUTH_TOKEN)
        {
            return (new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10001"
            ], 400));
        }

        $method = $request->method();
        if($method != 'POST')
        {
            return (new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10003"
            ], 400));
        }

        if(!$request->exists(['serviceId', 'timestamp']))
        {
            return (new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10005"
            ], 400));
        }

        if($request->get('serviceId') != Config::SERVICE_ID)
        {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10006"
            ], 400);
        }

        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $uri = explode('?', $uri)[0];
        if (array_key_exists($uri, $routes)) {
            $data = $routes[$uri];
            $controller = $data[0];
            $method = $data[1];
            $controllerInstance = new $controller();
            return $controllerInstance->$method($request);
        } else {
            return (new Response(['error' => 'Not found'], 404));
        }
    }
}