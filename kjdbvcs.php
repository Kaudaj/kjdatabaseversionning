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

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

use Kaudaj\Module\DBVCS\Builder\Change\ChangeBuilderInterface;
use Kaudaj\Module\DBVCS\Builder\Change\Module\ModuleInstallChangeBuilder;
use Kaudaj\Module\DBVCS\Form\Settings\ChangesRegistration\ChangesRegistrationConfiguration;
use Kaudaj\Module\DBVCS\Form\Settings\ChangesRegistration\ChangesRegistrationType;
use Kaudaj\Module\DBVCS\Repository\ChangeLangRepository;
use Kaudaj\Module\DBVCS\Repository\ChangeRepository;
use Kaudaj\Module\DBVCS\Repository\CommitLangRepository;
use Kaudaj\Module\DBVCS\Repository\CommitRepository;
use Kaudaj\Module\DBVCS\Utils\VersionControlManager;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\Shop\Context as ShopContext;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcher;
use PrestaShopBundle\Entity\Repository\LangRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KJDBVCS extends Module
{
    public const CONFIGURATION_KEYS_PREFIX = 'KJ_DBVCS_';
    public const REGISTERING_CONFIGURATION_KEY = self::CONFIGURATION_KEYS_PREFIX . 'REGISTERING_CHANGES';

    public const DATABASE_CHANGE_HOOK = 'actionDatabaseChange';
    public const CHANGE_BUILDER_HOOK_KEY = 'change_builder';
    public const SHOP_CONSTRAINT_HOOK_KEY = 'shop_constraint';

    /**
     * @var array<string, mixed> Configuration values
     */
    private $configurationValues = [];

    /**
     * @var string[] Hooks to register
     */
    public const HOOKS = [
        'actionModuleInstallAfter',
        self::DATABASE_CHANGE_HOOK,
    ];

    /**
     * @var Configuration<string, mixed> Configuration
     */
    private $configuration;

    /**
     * @var VersionControlManager
     */
    private $versionControlManager;

    /**
     * @var ShopContext
     */
    private $shopContext;

    /**
     * @var LangRepository
     */
    private $langRepository;

    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    public function __construct()
    {
        $this->name = 'kjdbvcs';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Kaudaj';
        $this->ps_versions_compliancy = ['min' => '1.7.8.0', 'max' => _PS_VERSION_];

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Database Version Control System', [], 'Modules.Kjdbvcs.Admin');
        $this->description = $this->trans(<<<EOF
        Provide version control for database by generating scripts with changes and applying them on demand.
EOF
            ,
            [],
            'Modules.Kjdbvcs.Admin'
        );

        $this->tabs = [
            [
                'name' => 'Database Version Control System',
                'class_name' => 'KJDBVCS',
                'parent_class_name' => 'AdminParentModulesSf',
                'visible' => false,
                'wording' => 'Database Version Control System',
                'wording_domain' => 'Modules.Kjdbvcs.Admin',
            ],
            [
                'name' => 'Version Control',
                'class_name' => 'KJDBVCSVersionControl',
                'route_name' => 'kj_dbvcs_version_control',
                'parent_class_name' => 'KJDBVCS',
                'visible' => true,
                'wording' => 'Version Control',
                'wording_domain' => 'Modules.Kjdbvcs.Admin',
            ],
            [
                'name' => 'Settings',
                'class_name' => 'KJDBVCSSettings',
                'route_name' => 'kj_dbvcs_settings',
                'parent_class_name' => 'KJDBVCS',
                'visible' => true,
                'wording' => 'Settings',
                'wording_domain' => 'Modules.Kjdbvcs.Admin',
            ],
        ];

        $this->configuration = new Configuration();
        $this->configurationValues = [
            self::REGISTERING_CONFIGURATION_KEY => false,
            ChangesRegistrationConfiguration::getConfigurationKey(ChangesRegistrationType::FIELD_CONFIGURATION_REGISTRATION) => true,
            ChangesRegistrationConfiguration::getConfigurationKey(ChangesRegistrationType::FIELD_MODULES_REGISTRATION) => true,
            ChangesRegistrationConfiguration::getConfigurationKey(ChangesRegistrationType::FIELD_HOOKS_MODULES_REGISTRATION) => true,
        ];

        /** @var ShopContext */
        $shopContext = $this->get('prestashop.adapter.shop.context');

        /** @var LangRepository */
        $langRepository = $this->get('prestashop.core.admin.lang.repository');

        /** @var HookDispatcher */
        $hookDispatcher = $this->get('prestashop.core.hook.dispatcher');

        $this->shopContext = $shopContext;
        $this->langRepository = $langRepository;
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function install(): bool
    {
        return parent::install()
            && $this->installConfiguration()
            && $this->registerHook(self::HOOKS)
            && $this->installTables()
        ;
    }

    /**
     * Install configuration values
     */
    private function installConfiguration(): bool
    {
        try {
            foreach ($this->configurationValues as $key => $default_value) {
                $this->configuration->set($key, $default_value);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Install database tables
     *
     * @return bool
     */
    private function installTables()
    {
        $sql = [];

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . ChangeRepository::TABLE_NAME . '` (
                `id_change` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `id_shop_group` INT UNSIGNED,
                `id_shop` INT UNSIGNED,
                `id_commit` INT UNSIGNED,
                `date_add` DATETIME NOT NULL
            ) ENGINE=' . pSQL(_MYSQL_ENGINE_) . ' COLLATE=utf8mb4_general_ci;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . ChangeLangRepository::TABLE_NAME . '` (
                `id_change` INT UNSIGNED NOT NULL,
                `id_lang` INT NOT NULL,
                `description` VARCHAR(255) NOT NULL,
                PRIMARY KEY (id_change, id_lang),
                FOREIGN KEY (`id_change`)
                REFERENCES `' . ChangeRepository::TABLE_NAME . '` (`id_change`) 
                ON DELETE CASCADE,
                FOREIGN KEY (`id_lang`)
                REFERENCES `' . pSQL(_DB_PREFIX_) . 'lang` (`id_lang`) 
                ON DELETE CASCADE
            ) ENGINE=' . pSQL(_MYSQL_ENGINE_) . ' COLLATE=utf8mb4_general_ci;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . CommitRepository::TABLE_NAME . '` (
                `id_commit` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `date_add` DATETIME NOT NULL
            ) ENGINE=' . pSQL(_MYSQL_ENGINE_) . ' COLLATE=utf8mb4_general_ci;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . CommitLangRepository::TABLE_NAME . '` (
                `id_commit` INT UNSIGNED NOT NULL,
                `id_lang` INT NOT NULL,
                `description` VARCHAR(255) NOT NULL,
                PRIMARY KEY (id_commit, id_lang),
                FOREIGN KEY (`id_commit`)
                REFERENCES `' . CommitRepository::TABLE_NAME . '` (`id_commit`) 
                ON DELETE CASCADE,
                FOREIGN KEY (`id_lang`)
                REFERENCES `' . pSQL(_DB_PREFIX_) . 'lang` (`id_lang`)
                ON DELETE CASCADE
            ) ENGINE=' . pSQL(_MYSQL_ENGINE_) . ' COLLATE=utf8mb4_general_ci;
        ';

        $result = true;
        foreach ($sql as $query) {
            $result = $result && Db::getInstance()->execute($query);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(): bool
    {
        return parent::uninstall()
            && $this->uninstallConfiguration()
            && $this->uninstallTables()
        ;
    }

    /**
     * Uninstall configuration values
     */
    private function uninstallConfiguration(): bool
    {
        try {
            foreach (array_keys($this->configurationValues) as $key) {
                $this->configuration->remove($key);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall database tables
     *
     * @return bool
     */
    private function uninstallTables()
    {
        $sql = [];

        $sql[] = '
            DROP TABLE IF EXISTS `' . ChangeLangRepository::TABLE_NAME . '`
        ';

        $sql[] = '
            DROP TABLE IF EXISTS `' . ChangeRepository::TABLE_NAME . '`
        ';

        $sql[] = '
            DROP TABLE IF EXISTS `' . CommitLangRepository::TABLE_NAME . '`
        ';

        $sql[] = '
            DROP TABLE IF EXISTS `' . CommitRepository::TABLE_NAME . '`
        ';

        $result = true;
        foreach ($sql as $query) {
            $result = $result && Db::getInstance()->execute($query);
        }

        return $result;
    }

    /**
     * Get module configuration page content
     */
    public function getContent(): void
    {
        $container = SymfonyContainer::getInstance();

        if ($container != null) {
            /** @var UrlGeneratorInterface */
            $router = $container->get('router');

            Tools::redirectAdmin($router->generate('kj_dbvcs_settings'));
        }
    }

    /**
     * @param array<string, mixed> $params Hook parameters
     */
    public function hookActionModuleInstallAfter(array $params): void
    {
        if (!key_exists('object', $params) || !($params['object'] instanceof Module)) {
            throw $this->getInvalidHookParametersException("Parameter 'object' must be an instance of a module.");
        }

        $module = $params['object'];

        if ($module instanceof $this) {
            return;
        }

        $this->triggerDatabaseChangeHook(new ModuleInstallChangeBuilder($this->getTranslator(), $this->langRepository, $module));
    }

    /**
     * @param array<string, mixed> $params Hook parameters
     */
    public function hookActionDatabaseChange(array $params): void
    {
        if (!key_exists(self::CHANGE_BUILDER_HOOK_KEY, $params)) {
            throw $this->getInvalidHookParametersException("Parameter '" . self::CHANGE_BUILDER_HOOK_KEY . "' is mandatory.");
        }

        $changeBuilder = $params[self::CHANGE_BUILDER_HOOK_KEY];

        if (!($changeBuilder instanceof ChangeBuilderInterface)) {
            $message = "Parameter '" . self::CHANGE_BUILDER_HOOK_KEY . "' must be an instance of a " . ChangeBuilderInterface::class . '.';

            throw $this->getInvalidHookParametersException($message);
        }

        $shopConstraint = $params[self::SHOP_CONSTRAINT_HOOK_KEY] ?? null;

        if ($shopConstraint !== null && !($shopConstraint instanceof ShopConstraint)) {
            $message = "Parameter '" . self::SHOP_CONSTRAINT_HOOK_KEY . "' must be an instance of a " . ShopConstraint::class . '.';

            throw $this->getInvalidHookParametersException($message);
        }

        $this->registerChange($changeBuilder, $shopConstraint);
    }

    private function registerChange(ChangeBuilderInterface $changeBuilder, ?ShopConstraint $shopConstraint = null): void
    {
        /*if ($this->configuration->getBoolean(self::REGISTERING_CONFIGURATION_KEY)) {
            return;
        }*/

        $this->configuration->set(self::REGISTERING_CONFIGURATION_KEY, true);
        $this->getVersionControlManager()->registerChange($changeBuilder, $shopConstraint ?? $this->shopContext->getShopConstraint());
        $this->configuration->set(self::REGISTERING_CONFIGURATION_KEY, false);
    }

    private function getVersionControlManager(): VersionControlManager
    {
        if ($this->versionControlManager === null) {
            /** @var VersionControlManager */
            $versionControlManager = $this->get('kaudaj.module.dbvcs.version_control_manager');

            $this->versionControlManager = $versionControlManager;
        }

        return $this->versionControlManager;
    }

    private function getInvalidHookParametersException(?string $message = null): Exception
    {
        if ($message === null) {
            $message = "Expected hook parameters can't be retrieved.";
        }

        return new InvalidArgumentException($message);
    }

    private function triggerDatabaseChangeHook(ChangeBuilderInterface $changeBuilder): void
    {
        $this->hookDispatcher->dispatchWithParameters(self::DATABASE_CHANGE_HOOK,
            [self::CHANGE_BUILDER_HOOK_KEY => $changeBuilder]
        );
    }
}
