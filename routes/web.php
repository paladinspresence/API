<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('test', function() use ($router) {
    return $router->app->paladins->getPlayerStatus(16643473);
});

$router->get('{playerId}', function($playerId) use ($router) {
    $template = [
        'data' => [
            'status' => null,
            'match' => null,
        ],

        'rich' => [
            'state' => null,
            'details' => null,
            'large_image_key' => null,
            'large_image_text' => null,
            'small_image_key' => 'presence-logo',
            'small_image_text' => null
        ]
    ];
    $status = $router->app->paladins->getPlayerStatus($playerId)[0];
    $match = null;
    $player = null;

    if ($status['status'] === 3 && $status['Match'] > 0) {
        $match = $router->app->paladins->getActiveMatchDetails($status['Match']);

        foreach($match as $p) {
            if ($p['playerId'] === $playerId) {
                $player = $p;
                break;
            }
        }
    }

    $template['data']['status'] = $status;
    $template['data']['match'] = $match;

    if (isset($match)) {
        $template['rich']['details'] = 'Playing ' . getGamemodeName($status['match_queue_id']);
        $template['rich']['state'] = 'Playing as ' . $player['ChampionName'];
        $template['rich']['large_image_key'] = getGamemodeImageKey($status['match_queue_id']);
        $template['rich']['large_image_text'] = $template['rich']['details'] . ' - Team ' . $player['taskForce'];
        $template['rich']['small_image_text'] = 'Account Level ' . $player['Account_Level'];
    } elseif ($status['status'] === 2) {
        $template['rich']['details'] = 'Champion Selection';
        $template['rich']['small_image_key'] = null;
        $template['rich']['large_image_key'] = 'presence-logo';
    } else {
        $template['rich']['details'] = 'Main Menu';
        $template['rich']['small_image_key'] = null;
        $template['rich']['large_image_key'] = 'presence-logo';
    }

    return $template;
});

function getGamemodeName($queue) {
    switch ($queue) {
        case 428: case 486:
            return 'Ranked';
        case 424: 
            return 'Siege';
        case 469:
            return 'Team Deathmatch';
        case 452:
            return 'Onslaught';
        case 470: case 425:
            return 'Training (Bots)';
        case 488:
            return 'End Times';
        case 445: 
            return 'Test Maps';
        default:
            return 'Custom';
    }
}

function getGamemodeImageKey($queue) {
    switch ($queue) {
        case 428: case 486:
            return 'gm-ranked';
        case 424: 
            return 'gm-siege';
        case 469:
            return 'gm-team-deathmatch';
        case 452:
            return 'gm-onslaught';
        case 470: case 425:
            return 'gm-custom';
        case 488:
            return 'gm-end-times';
        case 445: 
            return 'gm-custom';
        default:
            return 'gm-custom';
    }
}