<?php

namespace Ibrows\SimpleSeoBundle\Renderer;

use Ibrows\SimpleSeoBundle\Model\ContentInterface;


class MetaTagToHtmlRenderer implements HtmlRendererInterface
{

    /**
     * @var HtmlFilterInterface
     */
    protected $filter;

    /**
     * @param HtmlFilterInterface $filter
     */
    public function setFilter(HtmlFilterInterface $filter = null)
    {
        $this->filter = $filter;
    }


    /**
     * @param ContentInterface $content
     * @param array            $args
     * @return string
     */
    public function toHTML(ContentInterface $content, array $args)
    {
        if (isset($args['output'])) {
            return $args['output'];
        }
        $output = '';

        $tags = array_merge($args,$content->getMetaTagArray());
        if (!isset($tags['pre'])) {
            $pre = "\n       ";
        } else {
            $pre = $tags['pre'];
            unset($tags['pre']);
        }
        foreach ($tags as $key => $tag) {
            if ($key == 'title') {
                $output .= $pre . "<title>" . $this->filterHtml($tag) . "</title>";
                continue;
            }
            $output .= $pre . self::createMetaTag($this->filterHtml($key), $this->filterHtml($tag));
        }
        return $output;
    }

    /**
     * @param $string
     * @return string
     */
    protected function filterHtml($string)
    {
        if ($this->filter) {
            return $this->filter->filterHtml($string);
        }
        return $string;

    }

    /**
     * @param string $name
     * @param string $content
     * @param array  $extras
     * @return string
     */
    protected function createMetaTag($name, $content, $extras = array())
    {
        $metaString = '';
        $metaString .= '<meta name="' . $name . '"';
        foreach ($extras as $key => $extra) {
            $metaString .= " $key=\"$extra\"";
        }
        $metaString .= ' content="' . $content . '" />';
        return $metaString;
    }

}