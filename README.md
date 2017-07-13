# Symfony cached request

## What is it?
 
A simple plugin that automatically handles caching any requests you make via Guzzle. If you've a project that's making a lot of calls to third-parties, such as Amazon or eBay, it's useful to cache the data even for a small amount of time, and to not have to re-write that code every time you want to do it.

There's quite a bit of service setup with this plugin to allow flexibility over what you want to do, but it shouldn't take long to add this to your project.
 
## How to use

First up, you need to choose what you're going to use for your caching these requests. This bundle uses the PSR-16 simple cache interface, so you'll need to use a system that's compatible with this. Symfony 3.3 and later have [some adapters](https://symfony.com/doc/current/components/cache.html) already built in.
 
For our example, we're going to use [Symfony's RedisCache](http://api.symfony.com/3.3/Symfony/Component/Cache/Simple/RedisCache.html). To make this work, you need to install something that'll allow us to communicate with our Redis server:

```sh
composer require predis/predis:^1.1
```

Now we've got that, it's just setting up some services:

```yaml
services:
    http_client:
        class: GuzzleHttp\Client
        arguments:
            - handler: '@http_client.handlerstack'
              connect_timeout: 5
              timeout: 5

    http_client.handlerstack:
        class: GuzzleHttp\HandlerStack
        factory: [ GuzzleHttp\HandlerStack, 'create' ]
        calls:
            - [ 'push', [ '@dittto.cached_request.middleware.request' ] ]

    dittto.cached_request.middleware.request:
        class: Closure
        factory: [ '@dittto.cached_request.middleware', 'onRequest' ]
        arguments: [ '@cache_adapter', '@dittto.cached_request.generator.sha1_uri', 5 ]

    redis_cache:
        class: Predis\Client
        arguments:
          - scheme: 'tcp'
            host:   'redis_box'
            port:   6379

    cache_adapter:
        class: Symfony\Component\Cache\Simple\RedisCache
        arguments: [ '@redis_cache' ]
```

The first couple of these are setting up `Guzzle` with a custom `Handler Stack`. To this, we push our `cached_request` middleware.

We init Redis and then push it into `cache_adapter`, a PSR-16-compliant CacheInterface. This is then passed to the middleware, together with a cache key generator, and a default cache time in seconds.

That's all we need to make your guzzle requests cache for 5 seconds. 

### Overriding the cache time

The above setup fixes your cache time to 5 seconds for every request, but there may (will) be some requests you make that will need much longer or shorter cache times than others.

To handle this, you can override the cache time per-request by passing an option through a Guzzle request:

```php
try {
    $this->client->request('GET', $url, [
            CachedMiddleware::CACHE_TIME_IN_S => 10
        ]
    );
} catch (TransferException $e) {
    var_dump($e);
    die();
}
```

## Cache key generators

Briefly mentioned above, this plugin ships with a default one called `Sha1UriCacheKey`. They are used by the caching middleware to work out what cache key a request should use.

You can easily make your own, and they accept the entire request object, and any additional Guzzle options passed through for your request. Just remember to swap out the service, above.

The one provided simply takes the full URL of a request and SHA1's it. The encryption is purely because some cache keys can't accept special characters that can be found in URLs.

If you also want to log the cache key's creation, alter the above services to handle:

```yaml
services:
    dittto.cached_request.middleware.request:
        class: Closure
        factory: [ '@dittto.cached_request.middleware', 'onRequest' ]
        arguments: [ '@cache_adapter', '@dittto.cached_request.generator.sha1_uri.logged', 5 ]
        
    dittto.cached_request.generator.sha1_uri.logged:
        parent: dittto.cached_request.generator.sha1_uri
        calls:
            - [ 'setLogger', [ '@logger' ] ]
```

## Logging your cache attempts

You can use multiple Guzzle middlewares at the same time, so you can have another that logs any requests through it, but it's useful to log exactly what your cache is doing. 

To help this, this plugin also comes with a `LoggedCacheDecorator`. This logs whenever anything interacts with the cache and tells you what and why.

To use it, just alter the above services to handle:

```yaml
services:
    dittto.cached_request.middleware.request:
        class: Closure
        factory: [ '@dittto.cached_request.middleware', 'onRequest' ]
        arguments: [ '@logged_cache_adapter', '@dittto.cached_request.generator.sha1_uri', 5 ]

    logged_cache_adapter:
        class: Dittto\CachedRequestBundle\Logger\LoggedCacheDecorator
        arguments: [ '@cache_adapter' ]
        calls:
            - [ 'setLogger', [ '@logger' ] ]
```

Now, whenever a cache item is requested or altered, it'll be added to your logs.