<?php

namespace Ibrows\SimpleSeoBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class MetaTagContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        if ($data && $data->getKeyword()) {
            $builder->add('keyword', 'hidden');
        } else {
            $builder->add('keyword');
        }
        $builder
                ->add('alias', 'text', array())
                ->add('title', 'text', array())
                ->add('keywords', 'text', array())
                ->add('description', 'textarea', array())
                ->add('metatags', 'textarea', array('label' => 'additional metatags'))

        ;
    }

    public function getName()
    {
        return 'ibrows_simpleseobundle_metatagcontenttype';
    }
}
