<?php
namespace Dittto\CachedRequestBundle\Logger;

use Dittto\CachedRequestBundle\CacheKeyGenerator\Sha1UriCacheKey;
use GuzzleHttp\Psr7\Request;

class Sha1UriCacheKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheKeyIsSha1edUri()
    {
        $request = new Request('GET', 'test-uri');
        $cacheKey = new Sha1UriCacheKey();

        $this->assertEquals(sha1('test-uri'), $cacheKey->getCacheKey($request));
    }
}