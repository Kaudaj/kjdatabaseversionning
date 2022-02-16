<?php
/**
 * Copyright since 2019 Kaudaj
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@kaudaj.com so we can send you a copy immediately.
 *
 * @author    Kaudaj <info@kaudaj.com>
 * @copyright Since 2019 Kaudaj
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace Kaudaj\Module\DBVCS\Form\Settings\ChangesRegistration;

use PrestaShopBundle\Form\Admin\Type\MultistoreConfigurationType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangesRegistrationType extends TranslatorAwareType
{
    public const FIELD_MODULES_REGISTRATION = 'modules_registration';
    public const FIELD_CONFIGURATION_REGISTRATION = 'configuration_registration';
    public const FIELD_HOOKS_MODULES_REGISTRATION = 'hooks_modules_registration';

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface<string, mixed> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::FIELD_MODULES_REGISTRATION, SwitchType::class, [
                'label' => $this->trans('Modules', 'Modules.Kjdbvcs.Admin'),
                'help' => $this->trans('Register modules installation and uninstallation.', 'Modules.Kjdbvcs.Admin'),
                'multistore_configuration_key' => 'KJ_DBVCS_MODULES_REGISTRATION',
            ])
            ->add(self::FIELD_CONFIGURATION_REGISTRATION, SwitchType::class, [
                'label' => $this->trans('Configuration', 'Modules.Kjdbvcs.Admin'),
                'help' => $this->trans('Register configuration values updates.', 'Modules.Kjdbvcs.Admin'),
                'multistore_configuration_key' => 'KJ_DBVCS_CONFIGURATION_REGISTRATION',
            ])
            ->add(self::FIELD_HOOKS_MODULES_REGISTRATION, SwitchType::class, [
                'label' => $this->trans('Hooks modules', 'Modules.Kjdbvcs.Admin'),
                'help' => $this->trans('Register modules hooking, unhooking and position updates.', 'Modules.Kjdbvcs.Admin'),
                'multistore_configuration_key' => 'KJ_DBVCS_HOOKS_MODULES_REGISTRATION',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'Admin.Translation.Domain',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'settings_changes_registration_block';
    }

    /**
     * {@inheritdoc}
     *
     * @see MultistoreConfigurationTypeExtension
     */
    public function getParent(): string
    {
        return MultistoreConfigurationType::class;
    }
}
