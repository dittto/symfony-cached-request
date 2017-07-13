<?php
namespace Dittto\CachedRequestBundle\CacheKeyGenerator;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Sha1UriCacheKey implements CacheKeyInterface, LoggerAwareInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function setLogger(LoggerInterface $logger):void
    {
        $this->logger = $logger;
    }

    public function getCacheKey(RequestInterface $request, array $options = []):string
    {
        $key = sha1((string) $request->getUri());

        if ($this->logger) {
            $this->logger->info(sprintf('Cache key "%s" created from "%s"', $key, $request->getUri()));
        }

        return $key;
    }
}