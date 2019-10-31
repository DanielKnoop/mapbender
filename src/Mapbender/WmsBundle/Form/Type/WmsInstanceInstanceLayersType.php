<?php

namespace Mapbender\WmsBundle\Form\Type;

use Mapbender\WmsBundle\Entity\WmsInstance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class WmsInstanceInstanceLayersType extends AbstractType
{
    /** @var bool */
    protected $exposeLayerOrder;

    /**
     * @param bool $exposeLayerOrder to expose layer order controls; from parameter mapbender.preview.layer_order.wms
     */
    public function __construct($exposeLayerOrder = false)
    {
        $this->exposeLayerOrder = $exposeLayerOrder;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'wmsinstanceinstancelayers';
    }

    public function getBlockPrefix()
    {
        return 'source_instance';
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var WmsInstance $wmsinstance */
        $wmsinstance = $options["data"];
        $source = $wmsinstance->getSource();

        $getMapFormatChoices = array();
        foreach ($source->getGetMap()->getFormats() ?: array() as $value) {
            $getMapFormatChoices[$value] = $value;
        }
        $featureInfoFormatChoices = array();
        if ($gfi = $source->getGetFeatureInfo()) {
            foreach ($gfi->getFormats() ?: array() as $value) {
                $featureInfoFormatChoices[$value] = $value;
            }
        }
        $exceptionFormatChoices = array();
        foreach ($source->getExceptionFormats() ?: array() as $value) {
            $exceptionFormatChoices[$value] = $value;
        }

        $builder
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
                'required' => true,
            ))
            ->add('format', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
                'choices' => $getMapFormatChoices,
                'choices_as_values' => true,
                'required' => true,
            ))
            ->add('infoformat', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
                'choices' => $featureInfoFormatChoices,
                'choices_as_values' => true,
                'required' => false,
            ))
            ->add('exceptionformat', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
                'choices' => $exceptionFormatChoices,
                'choices_as_values' => true,
                'required' => false,
            ))
        ;
        $builder
            ->add('basesource', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.wms.wmsloader.repo.instance.label.basesource',
            ))
            ->add('proxy', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.wms.wmsloader.repo.instance.label.proxy',
            ))
            ->add('opacity', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', array(
                'attr' => array(
                    'min' => 0,
                    'max' => 100,
                    'step' => 10,
                ),
                'constraints' => array(
                    new Constraints\Range(array(
                        'min' => 0,
                        'max' => 100,
                    )),
                ),
                'required' => true,
            ))
            ->add('transparency', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.wms.wmsloader.repo.instance.label.transparency',
            ))
            ->add('tiled', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'required' => false,
                'label' => 'mb.wms.wmsloader.repo.instance.label.tiled',
            ))
            ->add('ratio', 'Symfony\Component\Form\Extension\Core\Type\NumberType', array(
                'required' => false,
                'precision' => 2,
                'label' => 'mb.wms.wmsloader.repo.instance.label.ratio',
            ))
            ->add('buffer', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', array(
                'required' => false,
                'label' => 'mb.wms.wmsloader.repo.instance.label.buffer',
            ))
            ->add('dimensions', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', array(
                'required' => false,
                'entry_type' => 'Mapbender\WmsBundle\Form\Type\DimensionInstType',
                'allow_add' => false,
                'allow_delete' => false,
            ))
            ->add('vendorspecifics', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', array(
                'required' => false,
                'entry_type' => 'Mapbender\WmsBundle\Form\Type\VendorSpecificType',
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('layers', 'Mapbender\ManagerBundle\Form\Type\SourceInstanceLayerCollectionType', array(
                'entry_type' => 'Mapbender\WmsBundle\Form\Type\WmsInstanceLayerType',
                'options' => array(
                    'data_class' => 'Mapbender\WmsBundle\Entity\WmsInstanceLayer',
                ),
            ))
        ;

        if ($this->exposeLayerOrder) {
            $layerOrderChoices = array();
            foreach (WmsInstance::validLayerOrderChoices() as $validChoice) {
                $translationKey = "mb.wms.wmsloader.repo.instance.label.layerOrder.$validChoice";
                $layerOrderChoices[$translationKey] = $validChoice;
            }
            $builder->add('layerOrder', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
                'choices' => $layerOrderChoices,
                'choices_as_values' => true,
                'required' => true,
                'auto_initialize' => true,
                'label' => 'mb.wms.wmsloader.repo.instance.label.layerOrder',
            ));
        }
    }
}
