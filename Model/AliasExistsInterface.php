<?php

namespace Ibrows\SimpleSeoBundle\Model;

/**
 * Interface AliasExistsInterface.
 */
interface AliasExistsInterface
{
    /**
     * @param string $alias
     *
     * @return bool
     */
    public function aliasExists($alias);
}
