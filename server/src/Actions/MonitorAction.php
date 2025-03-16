<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MonitorAction
{
    /**
     * Show index page
     */
    public function view(Request $request, Response $response)
    {
        return view($response, 'index');
    }

    /**
     * Get weather info
     */
    public function weather(Request $request, Response $response)
    {
        $content = file_get_contents('https://yandex.ru/pogoda/samara?lat=53.195876&lon=50.100199');

        // Request OK
        if (is_string($content))
            $response->getBody()->write($content);
        // Request failed
        else
            $response->getBody()->write("");

        return $response;
    }

    /**
     * Get transport info
     */
    public function transport(Request $request, Response $response)
    {
        $stop = $request->getQueryParams()['stop'];

        $ch = curl_init("ytapi:3001/$stop");
        $content = curl_exec($ch);

        // Request OK
        if (is_string($content))
            $response->getBody()->write($content);
        // Request failed
        else
            $response->getBody()->write("");

        return $response;
    }
}
