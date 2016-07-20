<?php

namespace Mautic\WechatBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MessageType
 *
 * @package Mautic\WechatBundle\Form\Type
 */
class MessageType extends AbstractType
{
    private $translator;
    private $em;
    private $request;
    private $repo;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator   = $factory->getTranslator();
        $this->em           = $factory->getEntityManager();
        $this->request      = $factory->getRequest();
        $this->repo      = $factory->getModel('wechat.message')->getRepository();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(array('content' => 'html', 'customHtml' => 'html')));
        $builder->addEventSubscriber(new FormExitSubscriber('wechat.message', $options));

        $builder->add(
            'name',
            'text',
            array(
                'label'      => 'mautic.wechat.form.internal.name',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array('class' => 'form-control')
            )
        );
        $builder->add(
            'title',
            'text',
            array(
                'label'      => 'mautic.wechat.form.title',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array('class' => 'form-control')
            )
        );
        $builder->add(
            'description',
            'text',
            array(
                'label'      => 'mautic.wechat.form.description',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array('class' => 'form-control')
            )
        );
        $builder->add(
            'content',
            'text',
            array(
                'label'      => 'mautic.wechat.form.content',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array('class' => 'form-control')
            )
        );
        $builder->add(
            'tags',
            'text',
            array(
                'label'      => 'mautic.wechat.form.tags',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array('class' => 'form-control')
            )
        );

        $builder->add(
            'buttons',
            'form_buttons'
        );

        if (!empty($options["action"])) {
            $builder->setAction($options["action"]);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    // public function setDefaultOptions(OptionsResolverInterface $resolver)
    // {
    //     $resolver->setDefaults(
    //         array(
    //             'data_class' => 'Mautic\WechatBundle\Entity\Message'
    //         )
    //     );

    //     $resolver->setOptional(array('update_select'));
    // }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $repo      = $this->repo;

        $resolver->setDefaults(
            array(
                'choices'     => function (Options $options) use ($repo) {
                    static $choices;

                    if (is_array($choices)) {
                        return $choices;
                    }

                    $choices = array();

                    $accounts  = $repo->getAccountList('', 0, 0);
                    foreach ($accounts as $account) {
                        $choices[$account['id']] = $account['name'];
                    }

                    return $choices;
                },
                'expanded'    => false,
                'multiple'    => true,
                'required'    => false,
                'empty_value' => function (Options $options) {
                    return (empty($options['choices'])) ? 'mautic.wechat.no.accounts.note' : 'mautic.core.form.chooseone';
                },
                'disabled'    => function (Options $options) {
                    return (empty($options['choices']));
                },
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "message";
    }
}
