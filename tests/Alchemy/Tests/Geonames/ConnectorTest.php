<?php

namespace Alchemy\Tests\Geonames;

use Alchemy\Geonames\Connector;
use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;
use Alchemy\Geonames\Geoname;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;

class ConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testSearchShouldReturnResults()
    {
        $guzzle = $this->getGuzzleMock(file_get_contents(__DIR__ . '/Fixtures/search.json'));

        $connector = new Connector($guzzle, 'http://geoloc.com');
        $geonames = $connector->search('orleans');

        $this->assertCount(2, $geonames);
        $this->assertEquals(2988507, $geonames[0]->get('geonameid'));
        $this->assertEquals(4717560, $geonames[1]->get('geonameid'));
    }

    public function testSearchShouldQueryWithRightOptions()
    {
        $guzzle = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMock('Guzzle\Http\QueryString');

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->exactly(4))
            ->method('getQuery')
            ->will($this->returnValue($query));

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('[]'));

        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $query->expects($this->exactly(4))
            ->method('add');

        $guzzle->expects($this->once())
            ->method('get')
            ->with('http://geoloc.com/city', array('accept' => 'application/json'))
            ->will($this->returnValue($request));

        $connector = new Connector($guzzle, 'http://geoloc.com');
        $this->assertEquals(array(), $connector->search('orleans', 42, '66.6.66.6'));
    }

    public function testGeonameIdShouldReturnAGeoname()
    {
        $guzzle = $this->getGuzzleMock(file_get_contents(__DIR__ . '/Fixtures/geoname.json'));

        $connector = new Connector($guzzle, 'http://geoloc.com');
        $geoname = $connector->geoname(2992092);

        $this->assertInstanceOf('Alchemy\Geonames\Geoname', $geoname);
        $this->assertEquals(2992092, $geoname->get('geonameid'));
    }

    public function testGeonameIdShouldReturnAGeonameWithRightOptions()
    {
        $guzzle = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->never())
            ->method('getQuery');

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('{"geonameid":"2992092"}'));

        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $guzzle->expects($this->once())
            ->method('get')
            ->with('http://geoloc.com/city/2992092', array('accept' => 'application/json'))
            ->will($this->returnValue($request));

        $connector = new Connector($guzzle, 'http://geoloc.com/');
        $this->assertEquals(new Geoname(array('geonameid' => 2992092)), $connector->geoname(2992092));
    }

    public function testIPShouldReturnAGeoname()
    {
        $guzzle = $this->getGuzzleMock(file_get_contents(__DIR__ . '/Fixtures/ip.json'));

        $connector = new Connector($guzzle, 'http://geoloc.com');
        $geoname = $connector->ip('66.6.6.6');

        $this->assertInstanceOf('Alchemy\Geonames\Geoname', $geoname);
        $this->assertEquals(2992090, $geoname->get('geonameid'));
    }

    public function testIPShouldReturnAGeonameWithOptions()
    {
        $guzzle = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMock('Guzzle\Http\QueryString');

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->exactly(1))
            ->method('getQuery')
            ->will($this->returnValue($query));

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('{"geonameid":2992090}'));

        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $query->expects($this->exactly(1))
            ->method('add')
            ->with('ip', '66.6.6.6');

        $guzzle->expects($this->once())
            ->method('get')
            ->with('http://geoloc.com/ip', array('accept' => 'application/json'))
            ->will($this->returnValue($request));

        $connector = new Connector($guzzle, 'http://geoloc.com/');
        $this->assertEquals(new Geoname(array('geonameid' => 2992090)), $connector->ip('66.6.6.6'));
    }

    /**
     * @expectedException \Alchemy\Geonames\Exception\TransportException
     */
    public function testIPWithInvalidResponse()
    {
        $guzzle = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMock('Guzzle\Http\QueryString');

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->exactly(1))
            ->method('getQuery')
            ->will($this->returnValue($query));

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('invalidJson'));

        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $query->expects($this->exactly(1))
            ->method('add')
            ->with('ip', '66.6.6.6');

        $guzzle->expects($this->once())
            ->method('get')
            ->with('http://geoloc.com/ip', array('accept' => 'application/json'))
            ->will($this->returnValue($request));

        $connector = new Connector($guzzle, 'http://geoloc.com/');
        $connector->ip('66.6.6.6');
    }

    /**
     * @expectedException \Alchemy\Geonames\Exception\NotFoundException
     */
    public function testIPWithGuzzleClientException()
    {
        $guzzle = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMock('Guzzle\Http\QueryString');

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->exactly(1))
            ->method('getQuery')
            ->will($this->returnValue($query));

        $exception = new ClientErrorResponseException('Invalid resource');
        $exception->setResponse(new Response(404));

        $request->expects($this->once())
            ->method('send')
            ->will($this->throwException($exception));

        $query->expects($this->exactly(1))
            ->method('add')
            ->with('ip', '66.6.6.6');

        $guzzle->expects($this->once())
            ->method('get')
            ->with('http://geoloc.com/ip', array('accept' => 'application/json'))
            ->will($this->returnValue($request));

        $connector = new Connector($guzzle, 'http://geoloc.com/');
        $connector->ip('66.6.6.6');
    }

    /**
     * @expectedException \Alchemy\Geonames\Exception\TransportException
     */
    public function testIPWithGuzzleClientExceptionForbidden()
    {
        $guzzle = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMock('Guzzle\Http\QueryString');

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->exactly(1))
            ->method('getQuery')
            ->will($this->returnValue($query));

        $exception = new ClientErrorResponseException('Invalid resource');
        $exception->setResponse(new Response(403));

        $request->expects($this->once())
            ->method('send')
            ->will($this->throwException($exception));

        $query->expects($this->exactly(1))
            ->method('add')
            ->with('ip', '66.6.6.6');

        $guzzle->expects($this->once())
            ->method('get')
            ->with('http://geoloc.com/ip', array('accept' => 'application/json'))
            ->will($this->returnValue($request));

        $connector = new Connector($guzzle, 'http://geoloc.com/');
        $connector->ip('66.6.6.6');
    }

    /**
     * @expectedException \Alchemy\Geonames\Exception\TransportException
     */
    public function testIPWithGuzzleServerException()
    {
        $guzzle = $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMock('Guzzle\Http\QueryString');

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->exactly(1))
            ->method('getQuery')
            ->will($this->returnValue($query));

        $request->expects($this->once())
            ->method('send')
            ->will($this->throwException(new ServerErrorResponseException('Invalid resource')));

        $query->expects($this->exactly(1))
            ->method('add')
            ->with('ip', '66.6.6.6');

        $guzzle->expects($this->once())
            ->method('get')
            ->with('http://geoloc.com/ip', array('accept' => 'application/json'))
            ->will($this->returnValue($request));

        $connector = new Connector($guzzle, 'http://geoloc.com/');
        $connector->ip('66.6.6.6');
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Alchemy\Geonames\Connector', Connector::create('http://geoloc.com'));
    }

    private function getGuzzleMock($items)
    {
        if (!is_array($items)) {
            $items = array($items);
        }

        $client = new Client();

        $plugin = new MockPlugin();
        foreach (array_map(function ($item) {
            return new Response(200, array(), $item);
        }, $items) as $response) {
            $plugin->addResponse($response);
        }

        $client->addSubscriber($plugin);

        return $client;
    }
}
