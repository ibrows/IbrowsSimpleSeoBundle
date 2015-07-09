<?php

namespace Ibrows\SimpleSeoBundle\Form;

use Ibrows\SimpleSeoBundle\Form\AliasFormMapper;
use Ibrows\SimpleSeoBundle\Model\ContentMapperInterface;

class MetaTagContentFormMapper extends AliasFormMapper implements ContentMapperInterface
{

    /**
     * @var string
     */
    protected $titleProperty = 'metaTagTitle';

    /**
     * @var string
     */
    protected $keywordsProperty = 'metaTagKeywords';

    /**
     * @var string
     */
    protected $descriptionProperty = 'metaTagDescription';

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getMetaTagTitleSubForm()->getData();
    }

    public function setTitle($title)
    {
        $this->getMetaTagTitleSubForm()->setData($title);
    }

    public function setKeywords($keywords)
    {
        $this->getMetaTagKeywordsSubForm()->setData($keywords);
    }

    public function setDescription($description)
    {
        $this->getMetaTagDescriptionSubForm()->setData($description);
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->getMetaTagKeywordsSubForm()->getData();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getMetaTagDescriptionSubForm()->getData();
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getMetaTagTitleSubForm()
    {
        return $this->form->get($this->titleProperty);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getMetaTagKeywordsSubForm()
    {
        return $this->form->get($this->keywordsProperty);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getMetaTagDescriptionSubForm()
    {
        return $this->form->get($this->descriptionProperty);
    }

}