<?php

namespace Ibrows\SimpleSeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SimpleSeoBundle\Model\ContentInterface;
use Ibrows\SimpleSeoBundle\Routing\AliasHandler;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 *
 * @ORM\Table(name="scms_metatagscontent")
 * @ORM\Entity(repositoryClass="Ibrows\SimpleSeoBundle\Repository\MetaTagRepository")
 * @DoctrineAssert\UniqueEntity("alias")
 * @ORM\HasLifecycleCallbacks
 */
class MetaTagContent implements ContentInterface
{

    /**
     * @var $metatags
     * @ORM\Column(type="array")
     *
     */
    protected $metatags;
    protected static $preventvars = array('title', 'keywords', 'description');

    /**
     * @var string $alias
     *
     * @ORM\Column(name="alias", type="string", length=255, unique=true, nullable=true)
     */
    protected $alias = null;

    /**
     * @var $pathinfo
     * @ORM\Column(type="array")
     *
     */
    protected $pathinfo;


    /**
     * @var string $keyword
     *
     * @ORM\Column(name="keyword", type="string", length=255, unique=true)
     */
    protected $keyword;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $params
     *
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

    public function setChangedPathInfo(array $pathInfo){
        $this->pathinfo = $pathInfo;
    }

    /**
     * @return boolean
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
                if (!in_array($key, self::$preventvars)) {
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
            if (!in_array($key, self::$preventvars)) {
                $this->metatags[$key] = substr($val, ++$pos);
            }
        }
    }

    public function getMetatag($metatag)
    {
        if (!isset($this->metatags[$metatag])) {
            return null;
        }
        return $this->metatags[$metatag];
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

    public function toHTML($filter, array $args)
    {
        throw new \Exception("use HTML Renderer");
    }

    public function setParameters(\Symfony\Component\DependencyInjection\ContainerInterface $params)
    {
        $this->params = $params;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set keyword
     *
     * @param string $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Get keyword
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }
}