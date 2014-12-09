<?php

namespace Ibrows\SimpleSeoBundle\Extension;

use Ibrows\SimpleSeoBundle\Model\ContentInterface;
use Ibrows\SimpleSeoBundle\Model\ContentManagerInterface;
use Ibrows\SimpleSeoBundle\Renderer\HtmlFilterInterface;
use Ibrows\SimpleSeoBundle\Renderer\HtmlRendererInterface;
use Ibrows\SimpleSeoBundle\Routing\KeyGenerator;
use Ibrows\SimpleSeoBundle\Routing\RouteLoader;

/**
 * Class TwigExtension
 */
class TwigExtension extends \Twig_Extension implements HtmlFilterInterface
{

    /**
     * @var ContentManagerInterface
     */
    private $manager;

    /**
     * @var \Twig_Environment
     */
    protected $env;

    /**
     *
     * @var  \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var  \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     *
     * @var  \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var KeyGenerator
     */
    protected $keyGenerator;

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->env = $environment;
    }

    public function __construct(ContentManagerInterface $manager, \Symfony\Component\Translation\TranslatorInterface $translator, \Symfony\Component\Routing\RouterInterface $router, \Symfony\Component\DependencyInjection\Container $container)
    {
        $this->manager = $manager;
        $this->translator = $translator;
        $this->router = $router;
        $this->container = $container;
        $this->keyGenerator = new KeyGenerator();
    }

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
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }


    public function getFunctions()
    {
        return array(
            'sseo_metatags'     => new \Twig_Function_Method($this, 'metaTagsHtml', array('is_safe' => array('html'))),
            'sseo_metatag'      => new \Twig_Function_Method($this, 'metaTag', array('is_safe' => array('html'))),
            'scms_metatags'     => new \Twig_Function_Method($this, 'metaTagsHtml', array('is_safe' => array('html'))),
            'scms_metatag'      => new \Twig_Function_Method($this, 'metaTag', array('is_safe' => array('html'))),
            'scms_canonicaltag' => new \Twig_Function_Method($this, 'canonicalTagsHtml', array('is_safe' => array('html'))),
            'scms_canonical'    => new \Twig_Function_Method($this, 'canonical'),
        );
    }

    public function canonical($pathinfo, $uri = null)
    {
        if ($uri) {
            if (!$this->keyGenerator->isAddQueryString()) {
                //remove query string
                if ($pos = strpos($uri, '?')) {
                    $uri = substr($uri, 0, $pos);
                }
            }
            //remove if rewrite not used
            $uri = str_replace('app_dev.php/', '', $uri);
            $uri = str_replace('app.php/', '', $uri);
            return $uri;
        }

        $infos = $this->router->match($pathinfo);
        if ($infos === false) {
            return null;
        }
        if (strpos($infos['_route'], RouteLoader::ROUTE_BEGIN) === 0) {
            $infos = RouteLoader::getPathinfo($infos['_route']);
        }
        $route = $infos['_route'];
        unset($infos['_route']);
        unset($infos['_controller']);
        return ($this->router->generate($route, $infos, true));
    }

    public function canonicalTagsHtml($pathinfo)
    {
        $uri = $this->canonical($pathinfo);
        if ($uri == null) {
            return '';
        }
        if (strpos($uri, 'http://') === 0) {
            $uri = 'https://' . substr($uri, 7);
        }
        return '<link rel="canonical" href="' . $uri . '" >';
    }

    public function metaTag($tagName = 'title')
    {
        $locale = $this->translator->getLocale();
        if (!isset($arguments['pre'])) {
            $arguments['pre'] = sprintf("\n%8s", ' ');
        }
        $key = $this->keyGenerator->generateMetaTagKey($this->getRequest(), $this->router, $locale);
        $obj = $this->manager->findMetaTag($key, $locale);
        if ($obj) {
            return $obj->getMetatag($tagName);
        }
        return null;
    }


    public function metaTagsHtml($defaults = true, array $arguments = array(), $canonical = true)
    {

        $locale = $this->translator->getLocale();
        $currentLang = substr($locale, 0, 2);
        if (!isset($arguments['pre'])) {
            $arguments['pre'] = sprintf("\n%8s", ' ');
        }

        $headers = self::initMetaTagString();
        if ($defaults) {
            $headers .= $arguments['pre'] . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $headers .= $arguments['pre'] . '<meta http-equiv="content-language" content="' . $currentLang . '" />';
            $headers .= $arguments['pre'] . '<meta name="DC.language" scheme="RFC3066" content="' . $currentLang . '" />';
        }
        $key = $this->keyGenerator->generateMetaTagKey($this->container->get('request'), $this->container->get('router'), $locale);
        $obj = $this->manager->findMetaTag($key, $locale);
        if ($obj) {
            $headers .= $this->metaTagToHtml($obj, $arguments);
        }
        if ($canonical) {
            $headers .=  $arguments['pre'] . $this->canonicalTagsHtml($this->getRequest()->getPathInfo());
        }
        return $headers;
    }

    protected function metaTagToHtml(ContentInterface $content, $arguments)
    {
        $serviceId = $content->getHtmlRenderServiceId();
        $renderer = $this->container->get($serviceId);
        if (!$renderer instanceof HtmlRendererInterface) {
            throw new \Exception("RenderService($serviceId) must implements HtmlRendererInterface");
        }
        $renderer->setFilter($this);
        return $renderer->toHTML($content, $arguments);
    }


    public static function initMetaTagString()
    {
        return "<!--scms-metatags-->";
    }

    public function filterHtml($string)
    {
        return twig_escape_filter($this->env, $string);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'simpleseo';
    }
}