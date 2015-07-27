<?php

namespace Ibrows\SimpleSeoBundle\Routing;

use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * @api
 */
class UrlGenerator extends \Symfony\Component\Routing\Generator\UrlGenerator
{
    const GENERATE_NORMAL_ROUTE = '!!!';

    /**
     * @param string   $name
     * @param array    $mergedParams
     * @param   string $locale
     * @return array
     */
    protected function generateRouteNamesLocalized($name, array $mergedParams, $locale)
    {
        $routeNames = array();
        if ($locale) {
            $localizedParams = $mergedParams;
            $localizedParams['_locale'] = $locale;
            $routeNames[] = RouteLoader::getRouteName($name, $localizedParams);
        }
        $routeNames[] = RouteLoader::getRouteName($name, $mergedParams);
        return $routeNames;
    }

    /**
     * @param   string $name
     * @param array    $context
     * @param array    $defaults
     * @param array    $parameters
     * @param array    $variables
     * @param array    $requirements
     * @return array
     */
    protected function generateRouteNames($name, array $context, array $defaults, array $parameters, array $variables, array $requirements)
    {
        $mergedParams = array_replace($context, $defaults, $parameters);
        $locale = null;
        if (isset($mergedParams['_locale'])) {
            $locale = $mergedParams['_locale'];
        }
        unset($mergedParams['_locale']);
        $routeNames = $this->generateRouteNamesLocalized($name, $mergedParams, $locale);

        //check route without unknown params
        foreach ($mergedParams as $key => $val) {
            if (!in_array($key, $variables) && $key != '_controller' && $key != '_locale') {
                unset($mergedParams[$key]);
            }
        }
        $routeNames = array_merge($routeNames, $this->generateRouteNamesLocalized($name, $mergedParams, $locale));

        //check route without defaults
        foreach ($mergedParams as $key => $val) {
            if (array_key_exists($key, $defaults) && $key != '_controller' && $key != '_locale') {
                unset($mergedParams[$key]);
            }
        }
        $routeNames = array_merge($routeNames, $this->generateRouteNamesLocalized($name, $mergedParams, $locale));

        //check route with only requirements
        foreach ($mergedParams as $key => $val) {
            if (!array_key_exists($key, $requirements) && $key != '_controller' && $key != '_locale') {
                unset($mergedParams[$key]);
            }
        }
        $routeNames = array_merge($routeNames, $this->generateRouteNamesLocalized($name, $mergedParams, $locale));

        return $routeNames;
    }

    /**
     * @param       $variables
     * @param       $defaults
     * @param       $requirements
     * @param       $tokens
     * @param       $parameters
     * @param       $name
     * @param       $referenceType
     * @param       $hostTokens
     * @param array $requiredSchemes
     * @return string
     */
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
    {
        if (array_key_exists(self::GENERATE_NORMAL_ROUTE, $parameters)) {
            unset($parameters[self::GENERATE_NORMAL_ROUTE]);

            return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
        }

        //use the cached version with my name - do the standard request if its not work
        // dont generate assets                
        if (stripos($name, RouteLoader::ROUTE_BEGIN) !== 0 && stripos($name, '_assetic') !== 0) {

            $routeNames = $this->generateRouteNames($name, $this->context->getParameters(), $defaults, $parameters, $variables, $requirements);

            foreach ($routeNames as $routeName) {
                try {
                    return $this->generate($routeName, $parameters, $referenceType);
                } catch (RouteNotFoundException $e) {
                }
            }

        }

        return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
    }
}
