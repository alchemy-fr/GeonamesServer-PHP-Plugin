<?php

namespace Alchemy\Tests\Geonames;

use Alchemy\Geonames\GeonamesServiceProvider;
use Silex\Application;

class geonamesServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $app = new Application();
        $app->register(new GeonamesServiceProvider(), array(
            'geonames.server-uri' => 'http://geonames.domain.tld',
        ));
        $app->boot();

        $this->assertInstanceOf('Alchemy\Geonames\Connector', $app['geonames.connector']);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBootThrowsAnExceptionIfNoServerUri()
    {
        $app = new Application();
        $app->register(new GeonamesServiceProvider());
        $app->boot();
    }
}
