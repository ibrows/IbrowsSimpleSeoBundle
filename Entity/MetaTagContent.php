<?php

namespace Ibrows\SimpleSeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SimpleSeoBundle\Model\ContentInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @DoctrineAssert\UniqueEntity("alias")
 */
class MetaTagContent implements ContentInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var
     * @ORM\Column(type="array")
     */
    protected $metatags;
    protected static $metatagPreventKeys = array('title', 'keywords', 'description');

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255, unique=true, nullable=true)
     */
    protected $alias = null;

    /**
     * @var
     * @ORM\Column(name="pathinfo", type="array")
     */
    protected $pathinfo;

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="string", length=255, unique=true)
     */
    protected $keyword;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $params;

    protected $changedPathInfo = false;

    public function getPathinfo()
    {
        return $this->pathinfo;
    }

    private function setPathinfo()
    {
        $this->changedPathInfo = true;
    }

    public function setChangedPathInfo(array $pathInfo)
    {
        $this->pathinfo = $pathInfo;
    }

    /**
     * @return bool
     */
    public function isChangedPathInfo()
    {
        return $this->changedPathInfo;
    }

    public function setRouteDefaults(array $defaults)
    {
        $this->pathinfo['__defaults'] = $defaults;
    }

    public function getRouteDefaults()
    {
        if (array_key_exists('__defaults', $this->pathinfo) && is_array($this->pathinfo['__defaults'])) {
            return $this->pathinfo['__defaults'];
        }

        return array();
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias)
    {
        if ($alias == $this->alias) {
            //nothing changed
            return;
        }

        if (empty($alias)) {
            $this->alias = null;
        } else {
            $this->alias = $alias;
        }
        $this->setPathinfo();
    }

    /**
     * @return array
     */
    public function getMetaTagArray()
    {
        if (!is_array($this->metatags)) {
            return array();
        }

        return $this->metatags;
    }

    public function getMetatags()
    {
        $return = '';
        if (is_array($this->metatags)) {
            foreach ($this->metatags as $key => $val) {
                if (!in_array($key, self::$metatagPreventKeys)) {
                    $return .= "$key=$val\n";
                }
            }
        }

        return $return;
    }

    public function setMetatags($metatags)
    {
        foreach (explode("\n", $metatags) as $val) {
            $pos = strpos($val, '=');
            if ($pos === false) {
                continue;
            }
            $key = substr($val, 0, $pos);
            if (!in_array($key, self::$metatagPreventKeys)) {
                $this->metatags[$key] = substr($val, ++$pos);
            }
        }
    }

    public function getMetatag($metatag)
    {
        if (!isset($this->metatags[$metatag])) {
            return;
        }

        return $this->metatags[$metatag];
    }

    public function getHtmlRenderServiceId()
    {
        return 'ibrows_simple_seo.meta_tag_renderer';
    }

    // <editor-fold desc="Simple Getter Setter" defaultstate="collapsed" >

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param string $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    public function setMetatag($metatag, $value)
    {
        $this->metatags[$metatag] = $value;
    }

    public function getTitle()
    {
        return $this->getMetatag('title');
    }

    public function setTitle($title)
    {
        $this->setMetatag('title', $title);
    }

    public function getKeywords()
    {
        return $this->getMetatag('keywords');
    }

    public function setKeywords($keywords)
    {
        $this->setMetatag('keywords', $keywords);
    }

    public function getDescription()
    {
        return $this->getMetatag('description');
    }

    public function setDescription($description)
    {
        $this->setMetatag('description', $description);
    }

    // </editor-fold>


    // old scms

    public function setParameters(\Symfony\Component\DependencyInjection\ContainerInterface $params)
    {
        $this->params = $params;
    }

    public function toHTML($filter, array $args)
    {
        throw new \Exception('use HTML Renderer');
    }
}
