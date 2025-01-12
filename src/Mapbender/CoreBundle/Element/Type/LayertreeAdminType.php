<?php
namespace Mapbender\CoreBundle\Element\Type;

use Mapbender\CoreBundle\Element\EventListener\LayertreeSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * LayertreeAdminType
 */
class LayertreeAdminType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('target', 'Mapbender\ManagerBundle\Form\Type\Element\MapTargetType')
            ->add('autoOpen', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.layertree.label.autoopen',
            ))
            ->add('useTheme', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.layertree.label.usetheme',
            ))
            ->add('allowReorder', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.wms.wmsloader.repo.instancelayerform.label.allowreordertoc',
            ))
            ->add('showBaseSource', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.layertree.label.showbasesources',
            ))
            ->add('hideInfo', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.layertree.label.hideinfo',
            ))
            ->add('hideSelect', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.layertree.label.hideselect',
            ))
            ->add('menu', 'Mapbender\CoreBundle\Element\Type\LayerTreeMenuType', array(
                'required' => false,
            ))
        ;
        $builder->get('target')->addEventSubscriber(new LayertreeSubscriber());
    }
}
