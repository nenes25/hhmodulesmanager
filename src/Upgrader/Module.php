<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@h-hennes.fr so we can send you a copy immediately.
 *
 * @author    Hervé HENNES <contact@h-hhennes.fr>
 * @copyright since 2023 Hervé HENNES
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 */

namespace Hhennes\ModulesManager\Upgrader;

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use Symfony\Component\Config\Definition\Exception\Exception;

class Module implements UpgraderInterface
{
    /** @var string Type d'upgrade */
    public const TYPE = 'modules';

    public const KEY_ENABLE = 'enable';
    public const KEY_DISABLE = 'disable';
    public const KEY_INSTALL = 'install';
    public const KEY_UNINSTALL = 'uninstall';
    public const KEY_UPDATE = 'update';

    /** @var \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManager */
    protected $moduleManager;
    protected array $errors = [];
    protected array $success = [];

    /**
     * @param array $data
     */
    public function upgrade(array $data): void
    {
        if  ( !array_key_exists(self::TYPE,$data)){
            return;
        }
        $data = $data[self::TYPE];

        $configParts = [
            self::KEY_ENABLE,
            self::KEY_DISABLE,
            self::KEY_INSTALL,
            self::KEY_UNINSTALL,
            self::KEY_UPDATE,
        ];

        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager = $moduleManagerBuilder->build();

        foreach ($configParts as $configPart) {
            if (array_key_exists($configPart, $data) && is_array($data[$configPart]) && count($data[$configPart])) {
                $method = 'upgrade' . ucfirst($configPart);
                $this->$method($data[$configPart]);
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function upgradeEnable(array $data): void
    {
        foreach ($data as $moduleName) {
            if ($this->moduleManager->isInstalled($moduleName)) {
                if (!$this->moduleManager->isEnabled($moduleName)) {
                    $this->moduleManager->enable($moduleName);
                    $this->success[] = 'Module ' . $moduleName . ' enabled with success';
                } else {
                    $this->errors[] = "Can't enable module " . $moduleName . ' is already enabled';
                }
            } else {
                $this->errors[] = "Can't enable module " . $moduleName . ' as not installed';
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function upgradeDisable(array $data): void
    {
        foreach ($data as $moduleName) {
            if ($this->moduleManager->isInstalled($moduleName) && $this->moduleManager->isEnabled($moduleName)) {
                try {
                    if ($this->moduleManager->disable($moduleName)) {
                    } else {
                        $this->errors[] = "Can't disable module " . $moduleName . ' unknow error';
                    }
                    $this->success[] = 'Module ' . $moduleName . ' disabled with success';
                } catch (Exception $e) {
                    $this->errors[] = "Can't disable module " . $moduleName . ' ' . $e->getMessage();
                }
            } else {
                $this->errors[] = "Can't disable module " . $moduleName . ' as not installed or not enabled';
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function upgradeInstall(array $data): void
    {
        foreach ($data as $moduleName) {
            if (!$this->moduleManager->isInstalled($moduleName)) {
                try {
                    if ($this->moduleManager->install($moduleName)) {
                        $this->success[] = 'Module ' . $moduleName . ' installed with success';
                    } else {
                        $this->errors[] = "Can't install module " . $moduleName . ' unknow error';
                    }
                } catch (Exception $e) {
                    $this->errors[] = "Can't install module " . $moduleName . ' ' . $e->getMessage();
                }
            } else {
                $this->errors[] = "Can't install module " . $moduleName . ' is already installed';
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function upgradeUninstall(array $data): void
    {
        foreach ($data as $moduleName) {
            if ($this->moduleManager->isInstalled($moduleName)) {
                try {
                    if ($this->moduleManager->uninstall($moduleName)) {
                        $this->success[] = 'Module ' . $moduleName . ' uninstalled with success';
                    } else {
                        $this->errors[] = "Can't uninstall module " . $moduleName . ' unknow error';
                    }
                } catch (\Exception $e) {
                    $this->errors[] = "Can't uninstall module " . $moduleName . ' ' . $e->getMessage();
                }
            } else {
                $this->errors[] = "Can't uninstall module " . $moduleName . ' is not installed';
            }
        }
    }

    /**
     * @param array $data
     */
    public function upgradeUpdate(array $data): void
    {
        foreach ($data as $moduleName) {
            if ($this->moduleManager->isInstalled($moduleName)) {
                try {
                    if ($this->moduleManager->upgrade($moduleName)) {
                        $this->success[] = 'Module ' . $moduleName . ' upgraded with success';
                    } else {
                        $this->errors[] = "Can't upgrade module " . $moduleName . ' unknow error';
                    }
                } catch (Exception $e) {
                    $this->errors[] = "Can't upgrade module " . $moduleName . ' ' . $e->getMessage();
                }
            } else {
                $this->errors[] = "Can't upgrade module " . $moduleName . ' is not installed';
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
