<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FacetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('label' => 'name'));
        $builder->add(
            'isVisibleByOwner',
            'choice', array(
                'choices' => array('1' => 'yes', '0' => 'no'),
                'label' => 'visible',
                'expanded' => false,
                'multiple' => false
            )
        );
        $builder->add(
            'forceCreationForm',
            'checkbox', array(
                'label'     => 'display_at_registration',
                'required'  => false,
            ));
    }

    public function getName()
    {
        return 'facet_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
