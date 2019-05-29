<?php

namespace Mapbender\WmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * WmsSourceSimpleType class
 */
class WmsSourceSimpleType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'wmssource';
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('originUrl', 'text', array(
                'required' => true,
                'label' => 'mb.manager.source.serviceurl',
                'attr' => array(
                    'title' => 'The wms GetCapabilities url',
                ),
            ))
            ->add('username', 'text', array(
                'required' => false,
                'label' => 'mb.manager.source.username',
                'attr' => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('password', 'password', array(
                'required' => false,
                'label' => 'mb.manager.source.password',
                'attr' => array(
                    'autocomplete' => 'off',
                ),
            ))
        ;
    }

}
