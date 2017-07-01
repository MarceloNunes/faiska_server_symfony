<?php
/**
 * Created by PhpStorm.
 * User: marcelo
 * Date: 30/06/17
 * Time: 12:56
 */

namespace AppBundle\Controller\Helper;


class HttpServerVars
{

    public static function getHttpHost() {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $prefix = 'https://';
        } else {
            $prefix = 'http://';
        }

        return $prefix . $_SERVER['HTTP_HOST'];
    }

    public static function getLinkToPage($page) {
        $query         = $_GET;
        $query['page'] = $page;

        $routeArray = explode('?', $_SERVER['REQUEST_URI']);
        $route      = $routeArray[0];

        return self::getHttpHost().$route.'?'.http_build_query($query);
    }

}