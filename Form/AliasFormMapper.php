<?php

namespace Ibrows\SimpleSeoBundle\Form;


use Ibrows\SimpleSeoBundle\Model\AliasMapperInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * Map a form to an AliasMapper
 */
class AliasFormMapper implements AliasMapperInterface
{
    /**
     * @var FormInterface
     */
    protected $form;
    /**
     * @var string
     */
    protected $aliasProperty = 'alias';
    /**
     * @var string
     */
    protected $frontendViewRouteName;
    /**
     * @var array
     */
    protected $frontendViewParameters;
    /**
     * @var string
     */
    protected $frontendViewRouteLocale;

    /**
     * @param FormInterface $form
     * @param $frontendViewRouteName
     * @param array $frontendViewParameters
     * @param null $frontendViewRouteLocale
     * @param string $aliasProperty
     */
    public function __construct(FormInterface $form, $frontendViewRouteName, array $frontendViewParameters = array(), $frontendViewRouteLocale = null, $aliasProperty = 'alias')
    {
        $this->form = $form;
        $this->frontendViewRouteName = $frontendViewRouteName;
        $this->frontendViewParameters = $frontendViewParameters;
        $this->frontendViewRouteLocale = $frontendViewRouteLocale;
        $this->aliasProperty = $aliasProperty;
    }


    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->getAliasSubForm()->getData();
    }

    /**
     * @return FormInterface
     */
    protected function getAliasSubForm()
    {
        if (!$this->aliasProperty) {
            return $this->form;
        }

        return $this->form->get($this->aliasProperty);
    }

    /**
     * @return string
     */
    public function getFrontendViewRouteName()
    {
        return $this->frontendViewRouteName;
    }

    /**
     * @param string $frontendViewRouteName
     */
    public function setFrontendViewRouteName($frontendViewRouteName)
    {
        $this->frontendViewRouteName = $frontendViewRouteName;
    }

    /**
     * @return array
     */
    public function getFrontendViewParameters()
    {
        return $this->frontendViewParameters;
    }

    /**
     * @param array $frontendViewParameters
     */
    public function setFrontendViewParameters($frontendViewParameters)
    {
        $this->frontendViewParameters = $frontendViewParameters;
    }

    /**
     * @return string
     */
    public function getFrontendViewRouteLocale()
    {
        return $this->frontendViewRouteLocale;
    }

    /**
     * @param string $frontendViewRouteLocale
     */
    public function setFrontendViewRouteLocale($frontendViewRouteLocale)
    {
        $this->frontendViewRouteLocale = $frontendViewRouteLocale;
    }


    /**
     * @param $message
     */
    public function addError($message)
    {
        $this->getAliasSubForm()->addError(new FormError($message));
    }

    /**
     * @param $alias
     */
    public function setAlias($alias)
    {
        $this->getAliasSubForm()->setData($alias);
    }
}