<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class GroupType extends AbstractType
{
    public function __construct()
    {
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'name',
                'constraints' => new NotBlank()
            )
        );
    }

    public function getName()
    {
        return 'group_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = array('translation_domain' => 'platform');
        if ($this->forApi) $default['csrf_protection'] = false;

        $resolver->setDefaults($default);
    }

    public function enableApi()
    {
        $this->forApi = true;
    }
}
