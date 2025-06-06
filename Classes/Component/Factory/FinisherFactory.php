<?php
namespace CPSIT\T3importExport\Component\Factory;

use CPSIT\T3importExport\Component\Finisher\FinisherInterface;
use CPSIT\T3importExport\Factory\AbstractFactory;
use CPSIT\T3importExport\Factory\FactoryInterface;
use CPSIT\T3importExport\InvalidConfigurationException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class FinisherFactory extends AbstractFactory implements FactoryInterface
{
    /**
     * Builds a Finisher object
     *
     * @param array $settings
     * @param string $identifier
     * @throws InvalidConfigurationException
     * @return FinisherInterface
     */
    public function get(array $settings = [], $identifier = null): FinisherInterface
    {
        $additionalInformation = '.';
        if (!is_null($identifier)) {
            $additionalInformation = ' for ' . $identifier;
        }
        if (!isset($settings['class'])) {
            throw new InvalidConfigurationException(
                'Missing class in finisher configuration' . $additionalInformation,
                1_454_187_892
            );
        }
        $className = $settings['class'];

        if (!class_exists($className)) {
            throw new InvalidConfigurationException(
                'Finisher class ' . $className . ' in configuration for' . $additionalInformation
                . ' does not exist.',
                1_454_187_903
            );
        }

        if (!in_array(FinisherInterface::class, class_implements($className))) {
            throw new InvalidConfigurationException(
                'Finisher class ' . $className . ' in configuration for' . $additionalInformation
                . ' must implement FinisherInterface.',
                1_454_187_910
            );
        }

        // note: we want an independend instance for each component
        return clone GeneralUtility::makeInstance($className);
    }
}
