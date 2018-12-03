<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/12/3
 */

if (!function_exists('get_client_ip')) {
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return mixed|string
     */
    function get_client_ip(\Psr\Http\Message\ServerRequestInterface $request)
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        $ip = '0.0.0.0';
        $headers = $request->getHeaders();

        if (isset($headers['x_forwarded_for'])) {
            is_array($headers['x_forwarded_for']) ?
                list($ip) = explode(',', current($headers['x_forwarded_for'])) :
                list($ip) = explode(',', $headers['x_forwarded_for']);
        } elseif (isset($headers['x_real_ip'])) {
            $ip = is_array($headers['x_real_ip']) ? current($headers['x_real_ip']) : $headers['x_real_ip'];
        }

        unset($headers);

        return $ip;
    }
}

if (!function_exists('throttle_cache')) {
    /**
     * @return \Predis\Client
     */
    function throttle_cache()
    {
        return app()->get('throttle.cache');
    }
}
