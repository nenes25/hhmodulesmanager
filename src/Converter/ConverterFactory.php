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
namespace Hhennes\ModulesManager\Converter;

use Hhennes\ModulesManager\Converter\ConverterInterface;

class ConverterFactory
{

    /** @var ConverterInterface[] */
    private array $converters = [];

    /**
     * @param iterable $converters
     */
    public function __construct(iterable $converters =[])
    {
        foreach ($converters as $converter) {
            $this->addConverter($converter);
        }
    }

    /**
     * Add a new converter
     *
     * @param ConverterInterface $upgrader
     * @return void
     */
    public function addConverter(ConverterInterface $upgrader):void
    {
        $this->converters[] = $upgrader;
    }

    /**
     * Get the list of available converters
     *
     * @return ConverterInterface[]
     */
    public function getConverters():array
    {
        return $this->converters;
    }
}
