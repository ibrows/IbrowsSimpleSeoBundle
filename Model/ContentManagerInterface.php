<?php

namespace Ibrows\SimpleSeoBundle\Model;


/**
 * Interface ContentManagerInterface
 */
interface ContentManagerInterface
{
    /**
     * @return ContentInterface[]
     */
    public function findAllAlias();

    /**
     * @param $key
     * @param $locale
     * @return ContentInterface
     */
    public function findMetaTag($key, $locale);
}
