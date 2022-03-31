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

use PrestaShop\PrestaShop\Core\Configuration\AbstractMultistoreConfiguration;
use PrestaShopBundle\Service\Form\MultistoreCheckboxEnabler;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Manages the configuration data about Changes Registration options.
 */
class ChangesRegistrationConfiguration extends AbstractMultistoreConfiguration
{
    private const CONFIGURATION_FIELDS = [
        ChangesRegistrationType::FIELD_MODULES_REGISTRATION,
        ChangesRegistrationType::FIELD_CONFIGURATION_REGISTRATION,
        ChangesRegistrationType::FIELD_HOOKS_MODULES_REGISTRATION,
    ];

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getConfiguration()
    {
        $shopConstraint = $this->getShopConstraint();

        $getConfigurationValue = function (string $fieldName) use ($shopConstraint) {
            return $this->configuration->get($this->getConfigurationKey($fieldName), null, $shopConstraint);
        };

        return [
            ChangesRegistrationType::FIELD_MODULES_REGISTRATION => (bool) $getConfigurationValue(ChangesRegistrationType::FIELD_MODULES_REGISTRATION),
            ChangesRegistrationType::FIELD_CONFIGURATION_REGISTRATION => (bool) $getConfigurationValue(ChangesRegistrationType::FIELD_CONFIGURATION_REGISTRATION),
            ChangesRegistrationType::FIELD_HOOKS_MODULES_REGISTRATION => (bool) $getConfigurationValue(ChangesRegistrationType::FIELD_HOOKS_MODULES_REGISTRATION),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $configuration
     *
     * @return array<int, array<string, mixed>>
     */
    public function updateConfiguration(array $configuration)
    {
        if ($this->validateConfiguration($configuration)) {
            $shopConstraint = $this->getShopConstraint();

            $updateConfigurationValue = function (string $fieldName) use ($configuration, $shopConstraint): void {
                $this->updateConfigurationValue($this->getConfigurationKey($fieldName), $fieldName, $configuration, $shopConstraint);
            };

            $updateConfigurationValue(ChangesRegistrationType::FIELD_MODULES_REGISTRATION);
            $updateConfigurationValue(ChangesRegistrationType::FIELD_CONFIGURATION_REGISTRATION);
            $updateConfigurationValue(ChangesRegistrationType::FIELD_HOOKS_MODULES_REGISTRATION);
        }

        return [];
    }

    /**
     * Ensure the parameters passed are valid.
     *
     * @param array<string, mixed> $configuration
     *
     * @return bool Returns true if no exception are thrown
     */
    public function validateConfiguration(array $configuration): bool
    {
        $fields = [];

        foreach (self::CONFIGURATION_FIELDS as $field) {
            $multistoreField = MultistoreCheckboxEnabler::MULTISTORE_FIELD_PREFIX . $field;
            $fields[] = $field;
            $fields[] = $multistoreField;
        }

        foreach ($configuration as $key => $value) {
            if (!in_array($key, $fields)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return OptionsResolver
     */
    protected function buildResolver(): OptionsResolver
    {
        $resolver = (new OptionsResolver())
            ->setDefined(self::CONFIGURATION_FIELDS)
            ->setAllowedTypes(ChangesRegistrationType::FIELD_MODULES_REGISTRATION, ['bool'])
            ->setAllowedTypes(ChangesRegistrationType::FIELD_CONFIGURATION_REGISTRATION, ['bool'])
            ->setAllowedTypes(ChangesRegistrationType::FIELD_HOOKS_MODULES_REGISTRATION, ['bool'])
        ;

        return $resolver;
    }

    public static function getConfigurationKey(string $fieldName): string
    {
        return 'KJ_DBVCS_' . strtoupper($fieldName);
    }
}
