<?php
namespace Mapbender\CoreBundle\Element\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegendAdminType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'application' => null,
        ));
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('elementType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'required' => true,
                'choices' => array(
                    "dialog" => "dialog",
                    "blockelement" => "blockelement",
                ),
                'choices_as_values' => true,
            ))
            ->add('autoOpen', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.legend.label.autoopen',
            ))
            ->add('displayType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                array(
                'required' => true,
                'choices' => array(
                    "list" => "list",
                ),
                'choices_as_values' => true,
            ))
            ->add('target', 'Mapbender\CoreBundle\Element\Type\TargetElementType', array(
                'element_class' => 'Mapbender\\CoreBundle\\Element\\Map',
                'application' => $options['application'],
                'required' => false,
            ))
            ->add('showSourceTitle', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.legend.label.showsourcetitle',
            ))
            ->add('showLayerTitle', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.legend.label.showlayertitle',
            ))
            ->add('showGroupedLayerTitle', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.legend.label.showgroupedlayertitle',
            ))
            ->add('dynamicLegends', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.core.admin.legend.label.dynamicLegends',
            ))
        ;
    }

}
