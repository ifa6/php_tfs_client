<?php
namespace Tfs;

use Tfs\HttpClient;

/**
 * TestCase for HttpClient
 */
class HttpClientTest extends \TestCase
{
    function setUp()
    {
        $client = new HttpClient;
        $client->setBaseUrl('http://localhost');
        $this->client = $client;
    }
    
    function testHead()
    {
        $response = $this->client->head('/http_test.php');
        $this->assertTrue($response->isOk());
    }

    function testGet()
    {
        $response = $this->client->get('/http_test.php');
        $this->assertTrue($response->isOk());
        $ret = json_decode($response->getContent(), true);
        $this->assertEquals('GET', $ret['method']);
    }

    function testDelete()
    {
        $response = $this->client->delete('/http_test.php');
        $this->assertTrue($response->isOk());
        $ret = json_decode($response->getContent(), true);
        $this->assertEquals('DELETE', $ret['method']);
    }

    function testPut()
    {
        $response = $this->client->put('/http_test.php', 'content');
        $this->assertTrue($response->isOk());
        $ret = json_decode($response->getContent(), true);
        $this->assertEquals('PUT', $ret['method']);
        $this->assertEquals('content', $ret['input']);
    }

    function testPost()
    {
        $response = $this->client->post('/http_test.php', 'content');
        $this->assertTrue($response->isOk());
        $ret = json_decode($response->getContent(), true);
        $this->assertEquals('POST', $ret['method']);
        $this->assertEquals('content', $ret['input']);
    }
}
