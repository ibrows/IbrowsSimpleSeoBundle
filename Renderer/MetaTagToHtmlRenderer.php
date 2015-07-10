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
     *
     * @return string
     */
    public function toHTML(ContentInterface $content, array $args)
    {
        if (isset($args['output'])) {
            return $args['output'];
        }

        $tags = array_merge($args, $content->getMetaTagArray());
        if (!isset($tags['pre'])) {
            $pre = "\n       ";
        } else {
            $pre = $tags['pre'];
            unset($tags['pre']);
        }

        return self::createMetaTags($pre, $tags, $this->filter);
    }

    public static function createMetaTags($pre, $tags, HtmlFilterInterface $filter = null)
    {
        $output = '';
        foreach ($tags as $key => $tag) {
            if ($filter) {
                $tag = $filter->filterHtml($tag);
                $key = $filter->filterHtml($key);
            }
            if ($key == 'title') {
                $output .= $pre.'<title>'.$tag.'</title>';
                continue;
            }
            $output .= $pre.self::createMetaTag($key, $tag);
        }

        return $output;
    }

    /**
     * @param string $name
     * @param string $content
     * @param array  $extras
     *
     * @return string
     */
    public static function createMetaTag($name, $content, $extras = array())
    {
        $metaString = '';
        $metaString .= '<meta name="'.$name.'"';
        foreach ($extras as $key => $extra) {
            $metaString .= " $key=\"$extra\"";
        }
        $metaString .= ' content="'.$content.'" />';

        return $metaString;
    }
}
