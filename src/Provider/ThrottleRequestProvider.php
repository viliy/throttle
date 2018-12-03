<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/11/28
 */

namespace Viliy\Throttle\Provider;


use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use Predis\Client;
use Viliy\Throttle\Middleware\ThrottleRequest;

class ThrottleRequestProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container
     */
    public function register(Container $container)
    {
        $throttle = ['throttle' => (app()->getPath() . '/config/throttle.php')];
        if (file_exists(app()->getPath() . '/.throttle.yml')) {
            $throttle = array_merge(
                $throttle,
                ['throttle' => load(app()->getPath() . '/.throttle.yml')]
            );
        }

        config()->merge($throttle);

        isset($throttle['attempts.redis']) &&
        $container->add('throttle.cache', new Client($throttle['attempts.redis']));
        $container->get('dispatcher')->before(new ThrottleRequest());

        unset($throttle);
    }
}
