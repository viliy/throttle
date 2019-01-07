<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/11/28
 */

namespace Viliy\Throttle\Middleware;

use FastD\Middleware\DelegateInterface;
use FastD\Middleware\Middleware;
use Viliy\Throttle\Exception\ThrottleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ThrottleRequest
 * @package FastD\Throttle\Middleware
 */
class ThrottleRequest extends Middleware
{

    protected $ip = null;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $next
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, DelegateInterface $next)
    {
        $this->ip = get_client_ip($request);

        $this->checkWhiteAndBackList();

        $request = $this->resolveAttempts($request);

        $this->ip = null;

        return $next->process($request);
    }

    protected function checkWhiteAndBackList()
    {
        $this->backList();
        $this->whiteList();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function resolveAttempts(ServerRequestInterface $request)
    {
        if (false === config()->get('throttle.attempts.status', false)) {
            return $request;
        }

        $key = app()->getName() . ':request' .
            sha1($request->getMethod() . '|' . $request->getUri()->getPath() . '|' . $this->ip);

        if (throttle_cache()->get($key) >= config()->get('throttle.attempts.limit')) {
            throw new ThrottleException('too many request');
        }

        if (1 === ($counter = throttle_cache()->incr($key))) {
            throttle_cache()->expire($key, config()->get('throttle.attempts.expire'));
        }

        $request->withAddedHeader('X-Attempts-Limit', config()->get('throttle.attempts.limit'));
        $request->withAddedHeader('X-Attempts-Offset', $counter);

        unset($key);

        return $request;
    }

    /**
     * @return bool
     */
    protected function whiteList()
    {
        if (false === config()->get('throttle.whites.status')) {
            return true;
        }

        $whites = config()->get('throttle.whitelist.lists');
        list($one, $two, $three) = explode('.', $this->ip);

        if (isset($whites['level_one']) && in_array($one, $whites['level_one'])) {
        } elseif (isset($whites['level_two']) && in_array($one . '.' . $two, $whites['level_two'])) {
        } elseif (isset($whites['level_three']) && in_array($one . '.' . $two . '.' . $three, $whites['level_three'])) {
        } elseif (isset($whites['all']) && in_array($this->ip, $whites['all'])) {
        } else {

            throw new ThrottleException('http forbidden', 403);
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function backList()
    {
        if (false === config()->get('throttle.blacklist.status', false)) {
            return true;
        }

        $lists = config()->get('throttle.blacklist.lists');
        list($one, $two, $three) = explode('.', $this->ip);

        if (isset($whites['level_one']) && in_array($one, $lists['level_one'])) {
        } elseif (isset($whites['level_two']) && in_array($one . '.' . $two, $lists['level_two'])) {
        } elseif (isset($whites['level_three']) && in_array($one . '.' . $two . '.' . $three, $lists['level_three'])) {
        } elseif (isset($whites['all']) && in_array($this->ip, $lists['all'])) {
        } else {
            return true;
        }

        throw new ThrottleException('http forbidden', 403);
    }
}
