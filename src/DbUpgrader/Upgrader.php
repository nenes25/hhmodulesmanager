<?php
/**
 * Hervé HENNES
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement
 *
 * @author    Hervé HENNES <contact@h-hennes.fr>
 * @copyright since 2024 Hervé HENNES
 * @license   Commercial license (You can not resell or redistribute this software.)
 *
 */

namespace Hhennes\ModulesManager\DbUpgrader;

use PrestaShop\Module\AutoUpgrade\UpgradeException;
use Psr\Log\LoggerInterface;

class Upgrader
{

    private string $pathToUpgradeScripts;
    private string $destinationUpgradeVersion;
    private LoggerInterface $logger;
    private \Db $db;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->pathToUpgradeScripts = _PS_MODULE_DIR_.'autoupgrade/upgrade';
        $this->db = \Db::getInstance();
        $this->logger = $logger;
    }

    public function upgradeDb($oldversion,$destinatationVersion)
    {
        $this->destinationUpgradeVersion = $destinatationVersion;
        $upgrade_dir_sql = $this->pathToUpgradeScripts . '/sql/';
        $sqlContentVersion = $this->applySqlParams(
            $this->getUpgradeSqlFilesListToApply($upgrade_dir_sql, $oldversion)
        );
        dump($sqlContentVersion);

        foreach ($sqlContentVersion as $upgrade_file => $sqlContent) {
            foreach ($sqlContent as $query) {
                $this->runQuery($upgrade_file, $query);
            }
        }
    }

    protected function getUpgradeSqlFilesListToApply($upgrade_dir_sql, $oldversion)
    {
        if (!file_exists($upgrade_dir_sql)) {
            throw new UpgradeException('Unable to find upgrade directory in the installation path.');
        }

        $upgradeFiles = $neededUpgradeFiles = [];
        if ($handle = opendir($upgrade_dir_sql)) {
            while (false !== ($file = readdir($handle))) {
                if ($file[0] === '.') {
                    continue;
                }
                if (!is_readable($upgrade_dir_sql . $file)) {
                    throw new UpgradeException(sprintf('Error while loading SQL upgrade file "%s".', [$file]));
                }
                $upgradeFiles[] = str_replace('.sql', '', $file);
            }
            closedir($handle);
        }
        if (empty($upgradeFiles)) {
            throw new UpgradeException(sprintf('Cannot find the SQL upgrade files. Please check that the %s folder is not empty.',$upgrade_dir_sql));
        }
        natcasesort($upgradeFiles);

        foreach ($upgradeFiles as $version) {
            if (version_compare($version, $oldversion) == 1 && version_compare($this->destinationUpgradeVersion, $version) != -1) {
                $neededUpgradeFiles[$version] = $upgrade_dir_sql . $version . '.sql';
            }
        }

        return $neededUpgradeFiles;
    }

    /**
     * Replace some placeholders in the SQL upgrade files (prefix, engine...).
     *
     * @param array $sqlFiles
     *
     * @return array of SQL requests per version
     */
    protected function applySqlParams(array $sqlFiles)
    {
        $search = ['PREFIX_', 'ENGINE_TYPE', 'DB_NAME'];
        $replace = [_DB_PREFIX_, (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'MyISAM'), _DB_NAME_];

        $sqlRequests = [];

        foreach ($sqlFiles as $version => $file) {
            $sqlContent = file_get_contents($file) . "\n";
            $sqlContent = str_replace($search, $replace, $sqlContent);
            $sqlContent = preg_split("/;\s*[\r\n]+/", $sqlContent);
            $sqlRequests[$version] = $sqlContent;
        }

        return $sqlRequests;
    }

    /**
     * ToDo, check to move this in a database class.
     *
     * @param string $upgrade_file File in which the request is stored (for logs)
     * @param string $query
     */
    protected function runQuery($upgrade_file, $query)
    {
        $query = trim($query);
        if (empty($query)) {
            return;
        }
        // If php code have to be executed
        if (strpos($query, '/* PHP:') !== false) {
            return $this->runPhpQuery($upgrade_file, $query);
        }
        $this->runSqlQuery($upgrade_file, $query);
    }

    protected function runPhpQuery($upgrade_file, $query)
    {
        // Parsing php code
        $pos = strpos($query, '/* PHP:') + strlen('/* PHP:');
        $phpString = substr($query, $pos, strlen($query) - $pos - strlen(' */;'));
        $php = explode('::', $phpString);
        preg_match('/\((.*)\)/', $phpString, $pattern);
        $paramsString = trim($pattern[0], '()');
        preg_match_all('/([^,]+),? ?/', $paramsString, $parameters);
        $parameters = (isset($parameters[1]) && is_array($parameters[1])) ?
            $parameters[1] :
            [];
        foreach ($parameters as &$parameter) {
            $parameter = str_replace('\'', '', $parameter);
        }

        // reset phpRes to a null value
        $phpRes = null;
        // Call a simple function
        if (strpos($phpString, '::') === false) {
            $func_name = str_replace($pattern[0], '', $php[0]);
            $pathToPhpDirectory = $this->pathToUpgradeScripts . 'php/';

            if (!file_exists($pathToPhpDirectory . strtolower($func_name) . '.php')) {
                $this->logger->error('[ERROR] ' . $pathToPhpDirectory . strtolower($func_name) . ' PHP - missing file ' . $query);
                return;
            }

            require_once $pathToPhpDirectory . strtolower($func_name) . '.php';
            $phpRes = call_user_func_array($func_name, $parameters);
        }
        // Or an object method
        else {
            $func_name = [$php[0], str_replace($pattern[0], '', $php[1])];
            $this->logger->error('[ERROR] ' . $upgrade_file . ' PHP - Object Method call is forbidden (' . $php[0] . '::' . str_replace($pattern[0], '', $php[1]) . ')');
            $this->container->getState()->setWarningExists(true);

            return;
        }

        if (isset($phpRes) && (is_array($phpRes) && !empty($phpRes['error'])) || $phpRes === false) {
            $this->logger->error('
                [ERROR] PHP ' . $upgrade_file . ' ' . $query . "\n" . '
                ' . (empty($phpRes['error']) ? '' : $phpRes['error'] . "\n") . '
                ' . (empty($phpRes['msg']) ? '' : ' - ' . $phpRes['msg'] . "\n"));
        } else {
            $this->logger->debug('<div class="upgradeDbOk">[OK] PHP ' . $upgrade_file . ' : ' . $query . '</div>');
        }
    }

    protected function runSqlQuery($upgrade_file, $query)
    {
        if (strstr($query, 'CREATE TABLE') !== false) {
            $pattern = '/CREATE TABLE.*[`]*' . _DB_PREFIX_ . '([^`]*)[`]*\s\(/';
            preg_match($pattern, $query, $matches);
            if (!empty($matches[1])) {
                $drop = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $matches[1] . '`;';
                if ($this->db->execute($drop, false)) {
                    $this->logger->debug('<div class="upgradeDbOk">' . $this->container->getTranslator()->trans('[DROP] SQL %s table has been dropped.', ['`' . _DB_PREFIX_ . $matches[1] . '`'], 'Modules.Autoupgrade.Admin') . '</div>');
                }
            }
        }

        if ($this->db->execute($query, false)) {
            $this->logger->debug('<div class="upgradeDbOk">[OK] SQL ' . $upgrade_file . ' ' . $query . '</div>');

            return;
        }

        $error = $this->db->getMsgError();
        $error_number = $this->db->getNumberError();
        $this->logger->warning('
            <div class="upgradeDbError">
            [WARNING] SQL ' . $upgrade_file . '
            ' . $error_number . ' in ' . $query . ': ' . $error . '</div>');

        $duplicates = ['1050', '1054', '1060', '1061', '1062', '1091'];
        if (!in_array($error_number, $duplicates)) {
            $this->logger->error('SQL ' . $upgrade_file . ' ' . $error_number . ' in ' . $query . ': ' . $error);
        }
    }

}