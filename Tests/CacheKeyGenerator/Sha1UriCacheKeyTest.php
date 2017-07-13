<?php
namespace Dittto\CachedRequestBundle\Logger;

use Dittto\CachedRequestBundle\CacheKeyGenerator\Sha1UriCacheKey;
use GuzzleHttp\Psr7\Request;
use Psr\Log\AbstractLogger;

class Sha1UriCacheKeyTest extends \PHPUnit_Framework_TestCase
{
    private $logger;

    public function setUp()
    {
        $this->logger = new class extends AbstractLogger {
            public $logs = [];
            public function log($level, $message, array $context = []) { $this->logs[] = [$level, $message, $context]; }
        };
    }

    public function testCacheKeyIsSha1edUri()
    {
        $request = new Request('GET', 'test-uri');
        $cacheKey = new Sha1UriCacheKey();

        $this->assertEquals(sha1('test-uri'), $cacheKey->getCacheKey($request));
    }

    public function testCacheKeyCreationIsLogged()
    {
        $request = new Request('GET', 'test-uri');
        $cacheKey = new Sha1UriCacheKey();
        $cacheKey->setLogger($this->logger);

        $cacheKey->getCacheKey($request);

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "' . sha1('test-uri') . '" created from "test-uri"', $this->logger->logs[0][1]);
    }
}