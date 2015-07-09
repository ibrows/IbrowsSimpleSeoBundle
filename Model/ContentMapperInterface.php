<?php

namespace Ibrows\SimpleSeoBundle\Model;

interface ContentMapperInterface extends AliasMapperInterface
{

    /**
     * @return string
     */
    public function getTitle();
    public function setTitle($title);

    /**
     * @return string
     */
    public function getKeywords();
    public function setKeywords($keywords);

    /**
     * @return string
     */
    public function getDescription();
    public function setDescription($description);


}