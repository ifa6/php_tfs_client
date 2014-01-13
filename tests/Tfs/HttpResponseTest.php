<?php
namespace Tfs;

use Tfs\HttpResponse;

/**
 * TestCase for HttpResponse
 */
class HttpResponseTest extends \TestCase
{
    function testGetResult()
    {
        $r = new HttpResponse(201, '{"TFS_FILE_NAME": "abc"}');
        $this->assertEquals('abc', $r->getResult()->TFS_FILE_NAME);
    }
}
