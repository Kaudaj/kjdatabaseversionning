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

use Kaudaj\Module\DBVCS\Form\Settings\GeneralConfiguration;
use Kaudaj\Module\DBVCS\Repository\ChangeLangRepository;
use Kaudaj\Module\DBVCS\Repository\ChangeRepository;
use Kaudaj\Module\DBVCS\VersionControlManager;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KJDBVCS extends Module
{
    /**
     * @var array<string, string> Configuration values
     */
    public const CONFIGURATION_VALUES = [
        GeneralConfiguration::EXAMPLE_SETTING_KEY => 'default_value',
    ];

    /**
     * @var string[] Hooks to register
     */
    public const HOOKS = [
        'actionModuleInstallAfter',
    ];

    /**
     * @var Configuration<string, mixed> Configuration
     */
    private $configuration;

    /**
     * @var VersionControlManager
     */
    private $versionControlManager;

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
                'name' => 'DB VCS Settings',
                'class_name' => 'DBVCSSettings',
                'parent_class_name' => 'CONFIGURE',
                'visible' => false,
                'wording' => 'DB VCS Settings',
                'wording_domain' => 'Modules.Kjdbvcs.Admin',
            ],
        ];

        $this->configuration = new Configuration();

        try {
            /** @var VersionControlManager */
            $versionControlManager = $this->get('kaudaj.module.kjdbvcs.version_control_manager');

            $this->versionControlManager = $versionControlManager;
        } catch (Exception $e) {
        }
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
            foreach (self::CONFIGURATION_VALUES as $key => $default_value) {
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
        $sql = '
            CREATE TABLE IF NOT EXISTS `' . pSQL(_DB_PREFIX_) . ChangeRepository::TABLE_NAME . '` (
                `id_change` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `id_commit` INT UNSIGNED,
                `date_add` DATETIME NOT NULL
            ) ENGINE=' . pSQL(_MYSQL_ENGINE_) . ' COLLATE=utf8mb4_general_ci;

            CREATE TABLE IF NOT EXISTS `' . pSQL(_DB_PREFIX_) . ChangeLangRepository::TABLE_NAME . '` (
                `id_change` INT UNSIGNED NOT NULL,
                `id_lang` INT UNSIGNED NOT NULL,
                `description` INT UNSIGNED NOT NULL,
                PRIMARY KEY (id_change, id_lang),
                FOREIGN KEY (`id_change`)
                REFERENCES `' . pSQL(_DB_PREFIX_) . 'kj_dbvcs_change` (`id_change`) 
                ON DELETE CASCADE,
                FOREIGN KEY (`id_lang`)
                REFERENCES `' . pSQL(_DB_PREFIX_) . 'lang` (`id_lang`) 
                ON DELETE CASCADE
            ) ENGINE=' . pSQL(_MYSQL_ENGINE_) . ' COLLATE=utf8mb4_general_ci;
        ';

        return Db::getInstance()->execute($sql);
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
            foreach (array_keys(self::CONFIGURATION_VALUES) as $key) {
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
        $sql = 'DROP TABLE IF EXISTS `' . pSQL(_DB_PREFIX_) . 'kj_dbvcs_change`';

        return Db::getInstance()->execute($sql);
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
        $this->versionControlManager->registerChange();
    }
}
