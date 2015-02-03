<?php

namespace Ibrows\SimpleSeoBundle\Model;

interface AliasMapperInterface
{

    public function getAlias();

    public function getFrontendViewRouteName();

    public function getFrontendViewParameters();

    public function getFrontendViewRouteLocale();

    public function addError($message);

    public function setAlias($alias);

}