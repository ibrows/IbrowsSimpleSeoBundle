<?php

namespace Ibrows\SimpleSeoBundle\Model;

interface AliasMapperInterface
{

    /**
     * @return string
     */
    public function getAlias();

    /**
     * @return string
     */
    public function getFrontendViewRouteName();

    /**
     * @return string
     */
    public function getFrontendViewParameters();

    /**
     * @param string $key
     * @return bool
     */
    public function hasFrontendViewParameter($key);

    /**
     * @param string $key
     * @param null|int|string $default
     * @return null|int|string
     */
    public function getFrontendViewParameter($key, $default = null);

    /**
     * @return string
     */
    public function getFrontendViewRouteLocale();

    /**
     * @param string $message
     */
    public function addError($message);

    /**
     * @param string $alias
     */
    public function setAlias($alias);

}