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
        if (!isset($args['pre'])) {
            $args['pre'] = "\n       ";
        }
        $output = '';
        foreach ($content->getMetaTagArray() as $key => $tag) {
            if (isset($args[$key])) {
                $tag = $tag . ' ' . $args[$key];
            }
            if ($key == 'title') {
                $output .= $args['pre'] . "<title>" . $this->filterHtml($tag) . "</title>";
                continue;
            }
            $output .= $args['pre'] . self::createMetaTag($this->filterHtml($key), $this->filterHtml($tag));
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
     * @param array $extras
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