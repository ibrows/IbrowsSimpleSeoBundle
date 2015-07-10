<?php
/**
 * Created by iBROWS AG.
 * User: marcsteiner
 * Date: 02.12.14
 * Time: 10:33
 */

namespace Ibrows\SimpleSeoBundle\Routing;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class KeyGenerator
{

    const LOCALE_DELIMITER = '---';
    protected $addQueryString = false;

    /**
     * @param $addQueryString
     */
    public function __construct($addQueryString = false)
    {
        $this->addQueryString = $addQueryString;
    }


    /**
     * @return boolean
     */
    public function isAddQueryString()
    {
        return $this->addQueryString;
    }

    /**
     * @param boolean $addQueryString
     */
    public function setAddQueryString($addQueryString)
    {
        $this->addQueryString = $addQueryString;
    }


    public function generatePathInfoFromMetaTagKey($key)
    {
        $arr = $this->splitLocaledKeyword($key);
        $key = str_replace('_', '/', $arr[1]);
        $key = str_replace('-00-', '_', $key);
        return $key;
    }

    public function generateMetaTagKeyFromPathInfo($pathinfo, $locale)
    {

        $key = str_replace('_', '-00-', $pathinfo); // replace / replacement
        $key = str_replace('/', '_', $key); //replace /
        $key = $this->generateLocaledKeyword($key, $locale);
        return $key;
    }


    public function generateMetaTagKey(Request $request, RouterInterface $router, $locale)
    {
        $pathInfo = $request->getPathInfo();
        $key = $this->generateMetaTagKeyFromRelativePath($pathInfo,$router,$locale);
        if ($this->addQueryString) {
            $key .= '?' . $request->getQueryString();
        }
        return $key;
    }

    public function generateMetaTagKeyFromRelativePath ($pathInfo, RouterInterface $router, $locale)
    {
        try {
            $info = $router->match($pathInfo);
        } catch (ResourceNotFoundException $e) {
            $info = false;
        } catch (MethodNotAllowedException $e) {
            $info = false;
        }

        if ($info !== false && strpos($info['_route'], RouteLoader::ROUTE_BEGIN) === 0) {
            // allready alias, get the base pathinfo
            $oldInfo = RouteLoader::getPathinfo($info['_route']);
            $oldRoute = $oldInfo['_route'];
            unset($oldInfo['_route']);
            $oldInfo[UrlGenerator::GENERATE_NORMAL_ROUTE] = true;
            $pathInfo = $router->generate($oldRoute, $oldInfo);
            $pathInfo = str_replace('/app_dev.php', '', $pathInfo);
            $pathInfo = preg_replace('!([^?]*)(\?_locale=[^&]*)!', '$1', $pathInfo);
        }
        return $this->generateMetaTagKeyFromPathInfo($pathInfo, $locale);
    }

    public function generateLocaledKeyword($key, $locale)
    {
        $pos = stripos($key, self::LOCALE_DELIMITER);
        if ($pos === false && $locale != null) {
            $key = $locale . self::LOCALE_DELIMITER . $key;
        } else {
            $pos = strlen(self::LOCALE_DELIMITER) + $pos;
            if ($locale == null) {
                // do nothing
            } else {
                $key = $locale . self::LOCALE_DELIMITER . substr($key, $pos);
            }
        }
        return $key;
    }


    public function splitLocaledKeyword($key)
    {
        $arr = explode(self::LOCALE_DELIMITER, $key, 2);
        if (sizeof($arr) == 1) {
            return array(null, $arr[0]);
        }
        return $arr;
    }
} 