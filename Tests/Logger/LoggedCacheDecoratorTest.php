<?php
namespace Dittto\CachedRequestBundle\Logger;

use Psr\Log\AbstractLogger;
use Psr\SimpleCache\CacheInterface;

class LoggedCacheDecoratorTest extends \PHPUnit_Framework_TestCase
{
    private $logger;

    public function setUp()
    {
        $this->logger = new class extends AbstractLogger {
            public $logs = [];
            public function log($level, $message, array $context = []) { $this->logs[] = [$level, $message, $context]; }
        };
    }

    public function testGetCache()
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

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertEquals('found-test-cache', $decorator->get('test-key'));
    }

    public function testValidGetCacheResponseIsLogged()
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

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->get('test-key');

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" has successfully retrieved data', $this->logger->logs[0][1]);
    }

    public function testFailedGetCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) { return null; }
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->get('test-key');

        $this->assertEquals('notice', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" has failed to retrieve data', $this->logger->logs[0][1]);
    }

    public function testSetCache()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) { return true; }
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertTrue($decorator->set('test-key', 'test-value'));
    }

    public function testValidSetCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) { return true; }
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->set('test-key', 'test-value');

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" has been successfully updated', $this->logger->logs[0][1]);
    }

    public function testFailedSetCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) { return false; }
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->set('test-key', 'test-value');

        $this->assertEquals('notice', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" has failed to update', $this->logger->logs[0][1]);
    }

    public function testDeleteCache()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) { return true; }
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertTrue($decorator->delete('test-key'));
    }

    public function testValidDeleteCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) { return true; }
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->delete('test-key');

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" has been successfully deleted', $this->logger->logs[0][1]);
    }

    public function testFailedDeleteCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) { return false; }
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->delete('test-key');

        $this->assertEquals('notice', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" has failed to delete', $this->logger->logs[0][1]);
    }

    public function testClearCache()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() { return true; }
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertTrue($decorator->clear());
    }

    public function testValidClearCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() { return true; }
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->clear();

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('Cache has been successfully cleared', $this->logger->logs[0][1]);
    }

    public function testFailedClearCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() { return false; }
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->clear();

        $this->assertEquals('notice', $this->logger->logs[0][0]);
        $this->assertEquals('Cache has failed to clear', $this->logger->logs[0][1]);
    }

    public function testGetMultipleCache()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) { return $keys; }
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertEquals(['one', 'two'], $decorator->getMultiple(['one', 'two']));
    }

    public function testGetMultipleCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) { return $keys; }
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->getMultiple(['one', 'two']);

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('2 requested cache keys have returned 2 responses', $this->logger->logs[0][1]);
    }

    public function testSetMultipleCache()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) { return true; }
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertTrue($decorator->setMultiple(['one', 'two']));
    }

    public function testValidSetMultipleCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) { return true; }
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->setMultiple(['one', 'two']);

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('2 requested cache keys have been successfully updated', $this->logger->logs[0][1]);
    }

    public function testFailedSetMultipleCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) { return false; }
            public function deleteMultiple($keys) {}
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->setMultiple(['one', 'two']);

        $this->assertEquals('notice', $this->logger->logs[0][0]);
        $this->assertEquals('2 requested cache keys have failed to update', $this->logger->logs[0][1]);
    }

    public function testDeleteMultipleCache()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) { return true; }
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertTrue($decorator->deleteMultiple(['one', 'two']));
    }

    public function testValidDeleteMultipleCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) { return true; }
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->deleteMultiple(['one', 'two']);

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('2 requested cache keys have been successfully deleted', $this->logger->logs[0][1]);
    }

    public function testFailedDeleteMultipleCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) { return false; }
            public function has($key) {}
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->deleteMultiple(['one', 'two']);

        $this->assertEquals('notice', $this->logger->logs[0][0]);
        $this->assertEquals('2 requested cache keys have failed to delete', $this->logger->logs[0][1]);
    }

    public function testHasCache()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) { return true; }
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $this->assertTrue($decorator->has('test-key'));
    }

    public function testValidHasCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) { return true; }
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->has('test-key');

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" exists', $this->logger->logs[0][1]);
    }

    public function testFailedHasCacheResponseIsLogged()
    {
        $cache = new class implements CacheInterface {
            public function get($key, $default = null) {}
            public function set($key, $value, $ttl = null) {}
            public function delete($key) {}
            public function clear() {}
            public function getMultiple($keys, $default = null) {}
            public function setMultiple($values, $ttl = null) {}
            public function deleteMultiple($keys) {}
            public function has($key) { return false; }
        };

        $decorator = new LoggedCacheDecorator($cache);
        $decorator->setLogger($this->logger);

        $decorator->has('test-key');

        $this->assertEquals('info', $this->logger->logs[0][0]);
        $this->assertEquals('Cache key "test-key" does not exist', $this->logger->logs[0][1]);
    }
}