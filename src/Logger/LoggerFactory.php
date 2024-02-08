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

namespace Hhennes\ModulesManager\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class LoggerFactory
{
    /** @var string Module name */
    public const MODULE_NAME = 'hhmodulesmanager';
    /** @var int Max file to preserve */
    public const MAX_FILES = 30;

    public function build()
    {
        return new Logger(
            self::MODULE_NAME,
            [
                $this->getHandler(),
            ],
            [
                new PsrLogMessageProcessor(),
            ]
        );
    }

    /**
     * Get a basic File Handler
     *
     * @return HandlerInterface
     */
    protected function getHandler(): HandlerInterface
    {
        return new RotatingFileHandler(
            $this->getLogFileName(),
            self::MAX_FILES
        );
    }

    /**
     * Get log file name
     *
     * @return string
     */
    protected function getLogFileName(): string
    {
        return _PS_ROOT_DIR_ . '/var/logs/' . self::MODULE_NAME . '/' . self::MODULE_NAME . '.log';
    }
}
