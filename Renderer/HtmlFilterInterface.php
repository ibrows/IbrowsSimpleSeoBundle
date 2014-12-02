<?php

namespace Ibrows\SimpleSeoBundle\Renderer;


interface HtmlFilterInterface
{

    /**
     * @param string $stringToFilter
     * @return string filteredHtml
     */
    public function filterHtml($stringToFilter);

}

?>
