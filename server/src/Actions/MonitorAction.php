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

        // Remove YAMetrika and other stuff
        $content = mb_ereg_replace('<link rel="alternate".*?>', '', $content);
        $content = mb_ereg_replace('<script>.*?</script>', '', $content);
        $content = mb_ereg_replace('<script src=.*?</script>', '', $content);
        $content = mb_ereg_replace('<script src=.*?</script>', '', $content);

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
        $isUrgent = $request->getQueryParams()['isUrgent'];

        // Check stop
        $list = ['op_u', 'su_u', 'op_v', 'su_v'];
        if (!in_array($stop, $list, true)) return $response;

        $folder = "../storage/stops";

        // Set urgent flag for update
        if ($isUrgent)
            file_put_contents("$folder/$stop.fl", '');

        // Read info
        $info = "$folder/$stop.json";
        $content = "";
        if (file_exists($info))
            $content = file_get_contents($info);

        $response->getBody()->write($content);
        return $response;
    }
}
