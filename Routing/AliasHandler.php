<?php

namespace Ibrows\SimpleSeoBundle\Routing;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Ibrows\SimpleSeoBundle\Entity\MetaTagContent;
use Ibrows\SimpleSeoBundle\Model\ContentInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AliasHandler
 * @package Ibrows\SimpleSeoBundle\Routing
 */
class AliasHandler implements  EventSubscriber
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var KeyGenerator
     */
    protected $keyGenerator;

    /**
     * @return KeyGenerator
     */
    public function getKeyGenerator()
    {
        return $this->keyGenerator;
    }

    /**
     * @param KeyGenerator $keyGenerator
     */
    public function setKeyGenerator($keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->keyGenerator = new KeyGenerator();

    }

    public function setAddQueryString($addQueryString){
        $this->keyGenerator->setAddQueryString($addQueryString);
    }

    public function getQueryInfo($key){
        $info = $this->keyGenerator->generatePathInfoFromMetaTagKey($key);
        $pos = strpos($info,'?');
        if($pos === false){
            return array();
        }
        $query =  $info = substr($info,$pos+1);
        $queryArray = array();
        parse_str($query,$queryArray);
        return $queryArray;
    }

    /**
     * @param $key
     * @return array An array of parameters
     */
    public function  getPathInfoFromMetaTagKey($key)
    {
        $info = $this->keyGenerator->generatePathInfoFromMetaTagKey($key);
        $this->router->getContext()->setMethod('GET');
        if($this->keyGenerator->isAddQueryString()){
            $info = substr($info,0,strpos($info,'?'));
            $match =  $this->router->match($info);
            return $match;
        }else{
            return $this->router->match($info);
        }
    }

    /**
     * @param $route
     * @param array $queryArgs
     * @return array
     */
    public function  getDefaults($route)
    {
        $route = $this->router->getRouteCollection()->get($route);
        if ($route) {
            $defaults = $route->getDefaults();
            return $defaults;
        }

        return array();
    }


    private $resetRouterCache = false;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::postUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postFlush
        );
    }



    public function preUpdate(LifecycleEventArgs $args){
        if($args->getEntity() instanceof ContentInterface){
            $this->updatePathInfo($args->getEntity());
        }
    }

    public function prePersist(LifecycleEventArgs $args){
        if($args->getEntity() instanceof ContentInterface){
            $this->updatePathInfo($args->getEntity());
        }
    }

    public function updatePathInfo(ContentInterface $content){
        if(!$content->isChangedPathInfo()){
            return;
        }

        $info = $this->getPathInfoFromMetaTagKey($content->getKeyword());
        $arr = $this->keyGenerator->splitLocaledKeyword($content->getKeyword());
        $contentInfo = $content->getPathInfo();
        $contentInfo['_locale'] = $arr[0];
        // add locale routing info after controller
        foreach ($info as $key => $value) {
            $contentInfo[$key] = $value;
        }
        $contentInfo = array_merge($contentInfo,$this->getQueryInfo($content->getKeyword()));
        $content->setChangedPathInfo($contentInfo);
        $content->setRouteDefaults($this->getDefaults($contentInfo['_route']));
    }


    public function postUpdate(LifecycleEventArgs $args){
        if($args->getEntity() instanceof ContentInterface){
            $this->resetRouterCache = true;
        }
    }

    public function postPersist(LifecycleEventArgs $args){
        if($args->getEntity() instanceof ContentInterface){
            $this->resetRouterCache = true;
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if($this->resetRouterCache){
            $this->resetRouterCache = false;
            $this->resetRouterCache();
        }
    }


    public function resetRouterCache()
    {
        if(!$this->router instanceof Router){
            return;
        }
        $cachedir = $this->router->getOption('cache_dir');
        $cacheclass = $this->router->getOption('matcher_cache_class');
        $cachedebug = $this->router->getOption('debug');

        // here i have to make sure, that cache not will be right with the old in memory routecollection
        $this->router->setOption('cache_dir', null);
        $cache = new ConfigCache($cachedir . '/' . $cacheclass . '.php', $cachedebug);
        if (file_exists($cache->__toString())) {
            unlink($cache->__toString());
        }
        $cacheclass = $this->router->getOption('generator_cache_class');
        $cache = new ConfigCache($cachedir . '/' . $cacheclass . '.php', $cachedebug);
        if (file_exists($cache->__toString())) {
            unlink($cache->__toString());
        }
    }
}