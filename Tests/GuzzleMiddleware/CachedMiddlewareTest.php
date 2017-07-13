<?php
namespace Dittto\CachedRequestBundle\GuzzleMiddleware;

use Dittto\CachedRequestBundle\CacheKeyGenerator\CacheKeyInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\SimpleCache\CacheInterface;

class CachedMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    private $cacheKey;

    public function setUp()
    {
        $this->cacheKey = new class implements CacheKeyInterface {
            public function getCacheKey(RequestInterface $request, array $options = []) { return 'test-cache-key'; }
        };
    }

    public function testCachedResponseIsRetrieved()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) { return 'found-test-cache'; }
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $middleware = new CachedMiddleware();
        $action = $middleware->onRequest($cache, $this->cacheKey);
        $handledAction = $action(function () {
            return new Promise\FulfilledPromise(new Response());
        });

        /** @var Promise\FulfilledPromise $result */
        $result = $handledAction(new Request('GET', 'test_uri'), []);

        $self = $this;
        $result->then(function (string $cachedData) use ($self) {
            $self->assertEquals('found-data-cache', $cachedData);
        }, function () use ($self) {
            $self->fail('Promise should have been successful');
        });

        Promise\queue()->run();
    }

    public function testUncachedResponseIsStored()
    {
        $cache = new class implements CacheInterface {
            public $storedCache = [];
            public function get($key, $default = null) { return null; }
            public function set($key, $value, $ttl = null) { $this->storedCache[] = [$key, $value, $ttl]; }
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $middleware = new CachedMiddleware();
        $action = $middleware->onRequest($cache, $this->cacheKey);
        $handledAction = $action(function () {
            return new Promise\FulfilledPromise(new Response());
        });
        $handledAction(new Request('GET', 'test_uri'), []);

        Promise\queue()->run();

        $this->assertCount(1, $cache->storedCache);
        $this->assertEquals('test-cache-key', $cache->storedCache[0][0]);
    }

    public function testFailureIsIgnored()
    {
        $cache = new class implements CacheInterface {
            public $storedCache = [];
            public function get($key, $default = null) { return null; }
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $middleware = new CachedMiddleware();
        $action = $middleware->onRequest($cache, $this->cacheKey);
        $handledAction = $action(function () {
            return new Promise\RejectedPromise(new TransferException('test-message', 500));
        });
        $handledAction(new Request('GET', 'test_uri'), []);

        Promise\queue()->run();

        $this->assertCount(0, $cache->storedCache);
    }

    public function testCacheTimeIsOverridden()
    {
        $cache = new class implements CacheInterface {
            public $storedCache = [];
            public function get($key, $default = null) { return null; }
            public function set($key, $value, $ttl = null) { $this->storedCache[] = [$key, $value, $ttl]; }
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $middleware = new CachedMiddleware();
        $action = $middleware->onRequest($cache, $this->cacheKey);
        $handledAction = $action(function () {
            return new Promise\FulfilledPromise(new Response());
        });
        $handledAction(new Request('GET', 'test_uri'), [CachedMiddleware::CACHE_TIME_IN_S => 16]);

        Promise\queue()->run();

        $this->assertCount(1, $cache->storedCache);
        $this->assertEquals(16, $cache->storedCache[0][2]);
    }
}