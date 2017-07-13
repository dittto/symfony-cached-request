<?php
namespace Dittto\CachedRequestBundle\Logger;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class LoggedCacheDecorator implements LoggerAwareInterface, CacheInterface
{
    private $cache;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function get($key, $default = null)
    {
        $result = $this->cache->get($key, $default);

        if ($this->logger) {
            if ($result !== $default) {
                $this->logger->info(sprintf('Cache key "%s" has successfully retrieved data', $key));
            } else {
                $this->logger->notice(sprintf('Cache key "%s" has failed to retrieve data', $key));
            }
        }

        return $result;
    }

    public function set($key, $value, $ttl = null)
    {
        $result = $this->cache->set($key, $value, $ttl);

        if ($this->logger) {
            if ($result) {
                $this->logger->info(sprintf('Cache key "%s" has been successfully updated', $key));
            } else {
                $this->logger->notice(sprintf('Cache key "%s" has failed to update', $key));
            }
        }

        return $result;
    }

    public function delete($key)
    {
        $result = $this->cache->delete($key);

        if ($this->logger) {
            if ($result) {
                $this->logger->info(sprintf('Cache key "%s" has been successfully deleted', $key));
            } else {
                $this->logger->notice(sprintf('Cache key "%s" has failed to delete', $key));
            }
        }

        return $result;
    }

    public function clear()
    {
        $result = $this->cache->clear();

        if ($this->logger) {
            if ($result) {
                $this->logger->info('Cache has been successfully cleared');
            } else {
                $this->logger->notice('Cache has failed to clear');
            }
        }

        return $result;
    }

    public function getMultiple($keys, $default = null)
    {
        $result = $this->cache->getMultiple($keys, $default);

        if ($this->logger) {
            $this->logger->info(sprintf('%d requested cache keys have returned %d responses', count($keys), count($result)));
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        $result = $this->cache->setMultiple($values, $ttl);

        if ($this->logger) {
            if ($result) {
                $this->logger->info(sprintf('%d requested cache keys have been successfully updated', count($values)));
            } else {
                $this->logger->notice(sprintf('%d requested cache keys have failed to update', count($values)));
            }
        }

        return $result;
    }

    public function deleteMultiple($keys)
    {
        $result = $this->cache->deleteMultiple($keys);

        if ($this->logger) {
            if ($result) {
                $this->logger->info(sprintf('%d requested cache keys have been successfully deleted', count($keys)));
            } else {
                $this->logger->notice(sprintf('%d requested cache keys have failed to delete', count($keys)));
            }
        }

        return $result;
    }

    public function has($key)
    {
        $result = $this->cache->has($key);

        if ($this->logger) {
            if ($result) {
                $this->logger->info(sprintf('Cache key "%s" exists', $key));
            } else {
                $this->logger->info(sprintf('Cache key "%s" does not exist', $key));
            }
        }

        return $result;
    }


}