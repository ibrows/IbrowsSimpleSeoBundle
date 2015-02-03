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