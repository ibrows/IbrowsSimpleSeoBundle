<?php

namespace Ibrows\SimpleSeoBundle\Routing;

use Ibrows\SimpleSeoBundle\Model\ContentManagerInterface;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Loader\FileLoader;

class RouteLoader extends FileLoader
{
    /**
     * @var ContentManagerInterface
     */
    protected $manager;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    const ROUTE_BEGIN = 'scms_';
    const ROUTE_END = '_scms';

    private static $localizedAlias = true;

    public function __construct(ContentManagerInterface $manager, \Symfony\Component\Routing\RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * @param string $resource
     * @param null   $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        if ($type == 'ibrows_router') {
            return true;
        }

        return false;
    }

    /**
     * @param string $resource
     * @param null   $type
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null)
    {
        $collection = new SymfonyRouteCollection();
        try{
            $results = $this->manager->findAllAlias();
        }catch (\Exception $e){
            return $collection;
        }
        foreach ($results as $metatag) {
            if (is_array($metatag['pathinfo'])) {
                $pathinfo = $metatag['pathinfo'];
            } else {
                $pathinfo = @unserialize($metatag['pathinfo']);
            }
            if (!is_array($pathinfo) || !isset($pathinfo['_route']) || !isset($metatag['alias'])) {
                continue;
            }
            $oldroute = $pathinfo['_route'];

            //add defaults to routealias
            if (isset($pathinfo['__defaults']) && is_array($pathinfo['__defaults'])) {
                foreach ($pathinfo['__defaults'] as $key => $value) {
                    if (strpos($key, '_') !== 0) {
                        $metatag['alias'] .= '-{'.$key.'}';
                    }
                }
            }
            $route = new Route($metatag['alias'], $pathinfo, array(), array());
            $collection->add(self::getRouteName($oldroute, $pathinfo), $route);
        }

        return $collection;
    }

    public static function getRouteName($routename, $parameters)
    {
        $routename = self::ROUTE_BEGIN.$routename.self::ROUTE_END;
        $routename .= self::parameters2String($parameters);

        return $routename;
    }

    private static function parameters2String($parameters)
    {
        ksort($parameters);
        $return = '';
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $return .= self::parameters2String($value);
                continue;
            }
            if ($value === null) {
                continue;
            }
            if (strpos($key, '_') !== 0 || ($key == '_locale' && self::$localizedAlias)) {
                //escape '_'
                $key = self::escape($key);
                $value = self::escape($value);
                $return .= "_{$key}_{$value}";
            }
        }

        return $return;
    }

    private static function escape($underlinedstring)
    {
        // only [a-z0-9A-Z_.] are valid...
        $underlinedstring = str_replace('_', '.', $underlinedstring);
        $underlinedstring = str_replace('-', '..', $underlinedstring);
        $underlinedstring = str_replace('%', '...', $underlinedstring);

        return $underlinedstring;
    }

    private static function unescape($string)
    {
        $string = str_replace('...', '%', $string);
        $string = str_replace('..', '-', $string);
        $string = str_replace('.', '_', $string);

        return $string;
    }

    public static function getPathinfo($newroutename)
    {
        $matches = array();
        preg_match('!'.self::ROUTE_BEGIN.'(.*)'.self::ROUTE_END.'(.*)!', $newroutename, $matches);
        $pathinfo = array();
        $pathinfo['_route'] = $matches[1];
        $matches = explode('_', $matches[2]);

        $key = false;
        foreach ($matches as $value) {
            if ($value !== null && $value !== false) {
                if (!$key) {
                    $key = self::unescape($value);
                } else {
                    if ($value === '') {
                        $pathinfo[$key] = null;
                    } else {
                        $pathinfo[$key] = self::unescape($value);
                    }
                    $key = false;
                }
            }
        }

        return $pathinfo;
    }

    public static function setLocalizedAlias($localizedAlias)
    {
        self::$localizedAlias = $localizedAlias;
    }
}
