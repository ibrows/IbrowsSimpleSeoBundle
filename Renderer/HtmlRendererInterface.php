<?php

namespace Ibrows\SimpleSeoBundle\Renderer;

use Ibrows\SimpleSeoBundle\Model\ContentInterface;


interface HtmlRendererInterface
{

    /**
     * @param HtmlFilterInterface $filter
     */
    public function setFilter(HtmlFilterInterface $filter = null);


    /**
     * @param ContentInterface $content
     * @param array            $args
     * @return string
     */
    public function toHTML(ContentInterface $content, array $args);

}