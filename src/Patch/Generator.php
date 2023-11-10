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

namespace Hhennes\ModulesManager\Patch;

use Hhennes\ModulesManager\Change;
use Hhennes\ModulesManager\Converter\ConverterFactory;
use Symfony\Component\Yaml\Yaml;

/**
 * Generate a file representing the list of changes
 */
class Generator
{
    private ConverterFactory $converterFactory;

    /**
     * @param ConverterFactory $converterFactory
     */
    public function __construct(ConverterFactory $converterFactory)
    {
        $this->converterFactory = $converterFactory;
    }

    /**
     * Generate the change file
     *
     * @param array $changeIds
     * @param string $changeVersion
     *
     * @return string
     */
    public function generateChangeFile(array $changeIds, string $changeVersion): string
    {
        $orderedChanges = $this->generateChangeFileArray($changeIds);
        $fileName = $this->getUpgradePath() . $changeVersion . '.yml';
        $yaml = Yaml::dump($orderedChanges, 3);
        file_put_contents(
            $fileName,
            $yaml
        );

        return $fileName;
    }

    /**
     * Get the directory where the upgrades files are stored
     *
     * @return string
     */
    public function getUpgradePath(): string
    {
        return _PS_MODULE_DIR_ . 'hhmodulesmanager/upgrades/';
    }

    /**
     * Get an array representing the changes
     *
     * @param int[] $changeIds
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function generateChangeFileArray(array $changeIds): array
    {
        $currentChanges = [];
        foreach ($changeIds as $changeId) {
            $change = new Change($changeId);
            $changeIsProcessed = false;
            try {
                foreach ($this->converterFactory->getConverters() as $converter) {
                    if ($converter->canConvert($change)) {
                        $converter->convert($change, $currentChanges);
                        $changeIsProcessed = true;
                    }
                }
                if (true === $changeIsProcessed) {
                    $change->delete();
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        return $currentChanges;
    }
}
