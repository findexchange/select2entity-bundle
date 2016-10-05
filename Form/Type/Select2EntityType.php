<?php

namespace Tetranz\Select2EntityBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;
use Tetranz\Select2EntityBundle\Form\DataTransformer\EntitiesToPropertyTransformer;
use Tetranz\Select2EntityBundle\Form\DataTransformer\EntityToPropertyTransformer;

/**
 *
 *
 * Class Select2EntityType
 * @package Tetranz\Select2EntityBundle\Form\Type
 */
class Select2EntityType extends AbstractType
{
    const DEFAULT_ROUTE_NAME = 'tetranz_select2entity_autocomplete';

    protected $em;
    protected $router;
    protected $pageLimit;
    protected $minimumInputLength;
    protected $dataType;

    public function __construct(EntityManager $em, Router $router, $minimumInputLength, $pageLimit, $dataType)
    {
        $this->em = $em;
        $this->router = $router;
        $this->minimumInputLength = $minimumInputLength;
        $this->pageLimit = $pageLimit;
        $this->dataType = $dataType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add the appropriate data transformer
        $transformer = $options['multiple']
                ? new EntitiesToPropertyTransformer($this->em, $options['class'], $options['text_property'])
                : new EntityToPropertyTransformer($this->em, $options['class'], $options['text_property']);

        $builder->addViewTransformer($transformer, true);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        if ($options['remote_route'] === self::DEFAULT_ROUTE_NAME) {
            $options['remote_params']['class'] = $options['class'];
            $options['remote_params']['text_property'] = $options['text_property'];
        }

        // make variables available to the view
        $view->vars['remote_path'] = $options['remote_path']
                ?: $this->router->generate($options['remote_route'], $options['remote_params']);

        $varNames = ['multiple', 'minimum_input_length', 'page_limit', 'data_type', 'placeholder', 'allow_new'];

        foreach($varNames as $varName) {
            $view->vars[$varName] = $options[$varName];
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'class' => null,
                'remote_path' => null,
                'remote_route' => self::DEFAULT_ROUTE_NAME,
                'remote_params' => array(),
                'multiple' => false,
                'compound' => false,
                'minimum_input_length' => $this->minimumInputLength,
                'page_limit' => $this->pageLimit,
                'data_type' => $this->dataType,
                'text_property' => null,
                'placeholder' => '',
                'allow_new' => false
            ));
    }

    public function getName()
    {
        return 'tetranz_select2entity';
    }
}
