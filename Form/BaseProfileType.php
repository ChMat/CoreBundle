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
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Symfony\Component\Translation\TranslatorInterface;

class BaseProfileType extends AbstractType
{
    private $langs;
    private $termsOfService;
    private $facets;
    private $translator;

    public function __construct(
        LocaleManager $localeManager,
        TermsOfServiceManager $termsOfService,
        TranslatorInterface $translator,
        array $facets = array()
    )
    {
        $this->langs          = $localeManager->getAvailableLocales();
        $this->termsOfService = $termsOfService;
        $this->facets         = $facets;
        $this->translator     = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = $this->translator->trans('date_form_datepicker_format', array(), 'platform');
        $attr['autocomplete'] = 'off';

        $builder->add('firstName', 'text', array('label' => 'First name'))
            ->add('lastName', 'text', array('label' => 'Last name'))
            ->add('username', 'text', array('label' => 'User name'))
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type' => 'password',
                    'first_options' => array('label' => 'password'),
                    'second_options' => array('label' => 'verification')
                )
            )
            ->add('mail', 'email', array('label' => 'email'))
            ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'Language'));

        $content = $this->termsOfService->getTermsOfService(false);

        if ($this->termsOfService->isActive() and $content instanceof Content) {

            $builder->add(
                'scroll',
                'scroll',
                array(
                    'label' => 'Terms of service',
                    'data' => $content->getContent()
                )
            )
            ->add('accepted_terms', 'checkbox', array('label' => 'I accept the terms of service'));
        }

        foreach ($this->facets as $facet) {
            foreach ($facet->getPanelFacets() as $panel) {
                foreach ($panel->getFieldsFacet() as $field) {
                    switch($field->getType()) {
                        case FieldFacet::STRING_TYPE:
                            $builder->add(
                                $field->getName(),
                                'text',
                                array(
                                    'label'  = $this->translator->trans($field->getName(), array(), 'platform'),
                                    'mapped' => false
                                )
                            );
                            break;
                        case FieldFacet::DATE_TYPE:
                            $builder->add(
                                $field->getName(),
                                'number',
                                array(
                                    'label'  => $this->translator->trans($field->getName(), array(), 'platform'),
                                    'mapped' => false
                                )
                            );
                            break;
                        case FieldFacet::FLOAT_TYPE:
                            $builder->add(
                                $field->getName(),
                                'datepicker',
                                array(
                                    'label'     => $this->translator->trans($field->getName(), array(), 'platform'),
                                    'required'  => false,
                                    'widget'    => 'single_text',
                                    'format'    => $this->translator->trans('date_agenda_display_format_for_form', array(), 'platform'),
                                    'attr'      => $attr,
                                    'autoclose' => true,
                                    'mapped'    => false
                                )
                            );
                            break;
                    }

                }
            }
        }
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform',
                'validation_groups' => array('registration', 'Default')
            )
        );
    }
}
