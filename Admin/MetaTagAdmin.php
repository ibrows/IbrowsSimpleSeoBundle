<?php

namespace Ibrows\SimpleSeoBundle\Admin;


use Ibrows\SimpleSeoBundle\Entity\MetaTagContent;
use Ibrows\SimpleSeoBundle\Model\ContentInterface;
use Ibrows\SimpleSeoBundle\Routing\KeyGenerator;
use Ibrows\SimpleSeoBundle\Routing\UrlGenerator;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class MetaTagAdmin
 * @package Ibrows\SimpleSeoBundle\Admin
 */
class MetaTagAdmin extends Admin
{
    /**
     * @var string
     */
    protected $uriTemplate = 'IbrowsSimpleSeoBundle:Admin:list_metatag_uri.html.twig';
    /**
     * @var KeyGenerator
     */
    protected $keyGenerator;

    /**
     * @param KeyGenerator $keyGenerator
     */
    public function setKeyGenerator($keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param mixed $object
     */
    public function prePersist($object)
    {
        /** @var $object MetaTagContent */
        $path = $this->getForm()->get('path')->getData();
        if (strpos('/', $path) !== false) {
            $path = '/' . $path;
        }
        try {
            $key = $this->keyGenerator->generateMetaTagKeyFromRelativePath($path, $this->getRouter(), $this->translator->getLocale());
            $object->setKeyword($key);
        } catch (\Exception $e) {
            return;
        }

    }

    /**
     * @return string
     */
    public function getUriTemplate()
    {
        return $this->uriTemplate;
    }

    /**
     * @param string $uriTemplate
     */
    public function setUriTemplate($uriTemplate)
    {
        $this->uriTemplate = $uriTemplate;
    }

    /**
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->getConfigurationPool()->getContainer()->get('router');
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
    }

    /**
     * @param ContentInterface $content
     * @param bool             $originUrl
     * @return string
     */
    public function getUrl(ContentInterface $content, $originUrl = true)
    {
        $info = $content->getPathInfo();
        $router = $this->getRouter();

        try {
            $parameters = $info['__defaults'];
            foreach ($info as $key => $value) {
                if (strpos($key, '_') === 0) {
                    continue;
                }
                $parameters[$key] = $value;
            }
            if ($originUrl) {
                $parameters[UrlGenerator::GENERATE_NORMAL_ROUTE] = true;
            }
            return $router->generate($info['_route'], $parameters);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * (non-PHPdoc)
     *
     * @see \Sonata\AdminBundle\Admin\Admin::configureListFields()
     */
    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier(
            "id",
            'integer',
            array(
                'route' => array(
                    'name' => 'edit'
                )
            )
        );
        $list->add("alias");
        $list->add("originUrl", 'text', array('mapped' => false, 'template' => $this->getUriTemplate(), 'originUrl' => true));
        $list->add("url", 'text', array('mapped' => false, 'template' => $this->getUriTemplate(), 'originUrl' => false));
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Sonata\AdminBundle\Admin\Admin::configureDatagridFilters()
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add("alias");
    }


    /**
     * (non-PHPdoc)
     *
     * @see \Sonata\AdminBundle\Admin\Admin::configureFormFields()
     */
    protected function configureFormFields(FormMapper $form)
    {
        if ($this->getSubject() == null || $this->getSubject()->getId() === null) {
            $form->add('path', 'text', array('mapped' => false));
        }
        $form->add('alias', 'text');
        $form->add('title', 'text');
        $form->add('keywords', 'text');
        $form->add('description', 'textarea', array());
        $form->add('metatags', 'textarea', array('label' => 'additional metatags'));
        $form->setHelps(array('metatags'=>'help.metatags'));
    }
} 