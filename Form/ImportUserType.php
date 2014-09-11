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
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Claroline\CoreBundle\Validator\Constraints\CsvUser;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportUserType extends AbstractType
{
    private $authenticationDrivers;

    public function __construct($authenticationDrivers = null)
    {
        $this->authenticationDrivers = $authenticationDrivers;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            'file',
            array(
                'required' => true,
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(),
                    new File(),
                    new CsvUser()
                )
            )
        )
        ->add(
            'authentication',
            'choice',
            array(
                'choices' => $this->authenticationDrivers,
                'mapped' => false,
                'required' => false,
                'label' => 'authentication'
            )
        )
        ->add(
            'sendMail',
            'checkbox',
            array(
                'label' => 'send_mail',
                'required' => false
            )
        );
    }

    public function getName()
    {
        return 'import_user_file';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
                )
        );
    }
}
