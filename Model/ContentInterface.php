<?php

namespace Ibrows\SimpleSeoBundle\Model;

interface ContentInterface
{
    /**
     * @return array
     */
    public function getMetaTagArray();

    public function isChangedPathInfo();

    public function getKeyword();

    public function getPathInfo();
    public function setChangedPathInfo(array $pathInfo);

    public function setRouteDefaults(array $defaults);

    public function getHtmlRenderServiceId();
//
//    public function getRouteDefaults();
//
//    public function getAlias();
//
//    public function setAlias($alias);
//
//    public function getMetaTags();
//
//    public function setMetaTags($metaTags);
//
    public function getMetaTag($metaTag);
//
//    public function setMetaTag($metaTag, $value);
//
//    public function getTitle();
//
//    public function setTitle($title);
//
//    public function getKeywords();
//
//    public function setKeywords($keywords);
//
//    public function getDescription();
//
//    public function setDescription($description);
//
//    public function toHTML($filter, array $args);
//
//    public function setParameters(\Symfony\Component\DependencyInjection\ContainerInterface $params);

    /**
     * Get id
     *
     * @return integer
     */
//    public function getId();

    /**
     * Set keyword
     *
     * @param string $keyword
     */
//    public function setKeyword($keyword);

    /**
     * Get keyword
     *
     * @return string
     */
//    public function getKeyword();

}