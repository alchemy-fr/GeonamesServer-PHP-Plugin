<?php

namespace Alchemy\Tests\Geonames;

use Alchemy\Geonames\GeonamesServiceProvider;
use Silex\Application;
use \PHPUnit\Framework\TestCase;

class geonamesServiceProviderTest extends TestCase
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
     * @expectedException \RuntimeException
     */
    public function testBootThrowsAnExceptionIfNoServerUri()
    {
        $app = new Application();
        $app->register(new GeonamesServiceProvider());
        $app->boot();
    }
}
