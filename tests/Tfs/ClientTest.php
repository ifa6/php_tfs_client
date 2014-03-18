<?php
namespace Tfs;

use Tfs\Client;
use Tfs\HttpResponse;

/**
 * TestCase for Client
 */
class ClientTest extends \TestCase
{
    function provideData()
    {
        $server = '10.232.4.44:3800';
        $c = new Client('appkey');
        $stub = $this->getMock('Tfs\HttpClient');
        $stub->expects($this->once())
            ->method('setBaseUrl')
            ->with('http://'.$server)
            ->will($this->returnValue($stub));
        $c->setHttpClient($stub);
        $c->setAppId('appid');
        $this->getPrivateProperty($c, 'servers')->setValue($c, array($server));
        $this->getPrivateProperty($c, 'maxAccessCount')->setValue($c, 10000);
        return array(
            array($c)
        );
    }

    /**
     * @dataProvider provideData
     */
    function testFetch($client)
    {
        $response = new HttpResponse(200, 'abc');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('get')
            ->with('/v1/appkey/file')
            ->will($this->returnValue($response));
        
        $resp = $client->fetch('file');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testSave($client) 
    {
        $response = new HttpResponse(201, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('post')
            ->with('/v1/appkey')
            ->will($this->returnValue($response));
        
        $resp = $client->save('abc');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testUnlink($client) 
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('delete')
            ->with('/v1/appkey/file')
            ->will($this->returnValue($response));
        
        $resp = $client->unlink('file');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testHide($client) 
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('delete')
            ->with('/v1/appkey/file?hide=1')
            ->will($this->returnValue($response));
        
        $resp = $client->hide('file');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testStat($client) 
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('get')
            ->with('/v1/appkey/metadata/file')
            ->will($this->returnValue($response));
        
        $resp = $client->stat('file');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testCreate($client)
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('post')
            ->with('/v2/appkey/appid/1/file/myfile')
            ->will($this->returnValue($response));
        
        $resp = $client->create('myfile');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testWrite($client)
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('put')
            ->with('/v2/appkey/appid/1/file/myfile')
            ->will($this->returnValue($response));
        
        $resp = $client->write('myfile', 'abc');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testRead($client)
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('get')
            ->with('/v2/appkey/appid/1/file/myfile')
            ->will($this->returnValue($response));
        
        $resp = $client->read('myfile');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testDelete($client) 
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('delete')
            ->with('/v2/appkey/appid/1/file/myfile')
            ->will($this->returnValue($response));
        
        $resp = $client->delete('myfile');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testDeleteDir($client) 
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('delete')
            ->with('/v2/appkey/appid/1/dir/mydir')
            ->will($this->returnValue($response));
        
        $resp = $client->deleteDir('mydir');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testFileExist($client) 
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('head')
            ->with('/v2/appkey/appid/1/file/myfile')
            ->will($this->returnValue($response));
        
        $resp = $client->fileExist('myfile');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testDirexist($client) 
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('head')
            ->with('/v2/appkey/appid/1/dir/mydir')
            ->will($this->returnValue($response));
        
        $resp = $client->dirExist('mydir');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testfileinfo($client)
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('get')
            ->with('/v2/appkey/metadata/appid/1/file/myfile')
            ->will($this->returnValue($response));
        
        $resp = $client->fileinfo('myfile');
        $this->assertEquals($response, $resp);
    }

    /**
     * @dataProvider provideData
     */
    function testDirinfo($client)
    {
        $response = new HttpResponse(200, '');
        $stub = $client->getHttpClient();
        $stub->expects($this->once())
            ->method('get')
            ->with('/v2/appkey/metadata/appid/1/dir/mydir')
            ->will($this->returnValue($response));
        
        $resp = $client->dirinfo('mydir');
        $this->assertEquals($response, $resp);
    }

    function testSetRootServer()
    {
        $client = new Client('appkey', 'http://10.232.42.55:3000');
        $this->assertEquals('10.232.42.55:3000', $client->getRootServer());
    }

    function testGetImageUrl()
    {
        $client = new Client('appkey');
        $url = $client->getImageUrl('T1FY4fXchaXXXXXXXX');
        $this->assertEquals('http://img01.daily.taobaocdn.net/tfscom/T1FY4fXchaXXXXXXXX', $url);
        $url = $client->getImageUrl('T1FY4fXchaXXXXXXXX', 80);
        $this->assertEquals('http://img01.daily.taobaocdn.net/tfscom/T1FY4fXchaXXXXXXXX_80x80.jpg', $url);
    }
}
