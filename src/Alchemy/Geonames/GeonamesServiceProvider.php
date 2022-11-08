<?php

/*
 * This file is part of Geonames API Connector.
 *
 * (c) Alchemy <dev.team@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Geonames;

use Guzzle\Http\Client;
use Guzzle\Log\MessageFormatter;
use Guzzle\Log\PsrLogAdapter;
use Guzzle\Plugin\Log\LogPlugin;
use Silex\Application;
use Silex\ServiceProviderInterface;

class GeonamesServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['geonames.connector'] = $app->share(function (Application $app) {
            return new Connector($app['geonames.guzzle-client'], $app['geonames.server-uri']);
        });
        $app['geonames.guzzle-client'] = $app->share(function (Application $app) {
            return new Client();
        });
    }

    public function boot(Application $app)
    {
        if (!isset($app['geonames.server-uri'])) {
            throw new \RuntimeException('You must set `geonames.server-uri`.');
        }

//        if (isset($app['monolog'])) {
//            $app['geonames.guzzle-client'] = $app->share(
//                $app->extend('geonames.guzzle-client', function (Client $client, Application $app) {
//                    $client->addSubscriber(new LogPlugin(new PsrLogAdapter($app['monolog']), MessageFormatter::DEBUG_FORMAT));
//
//                    return $client;
//                })
//            );
//        }
    }
}
