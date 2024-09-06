<?php

namespace CPSIT\T3importExport\Component\PostProcessor;

/***************************************************************
 *  Copyright notice
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Database\Connection;
use CPSIT\T3importExport\DatabaseTrait;
use CPSIT\T3importExport\InvalidColumnMapException;
use CPSIT\T3importExport\InvalidConfigurationException;
use CPSIT\T3importExport\MissingClassException;
use CPSIT\T3importExport\Service\DatabaseConnectionService;
use CPSIT\T3importExport\Service\TranslationService;
use CPSIT\T3importExport\Validation\Configuration\SetL10nParentConfigurationValidator;
use CPSIT\T3importExport\Validation\Configuration\TranslateObjectConfigurationValidator;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Class TranslateObject
 * Translates
 *
 * @package CPSIT\T3importExport\Component\PostProcessor
 */
class SetL10nParent extends AbstractPostProcessor implements PostProcessorInterface
{
    use DatabaseTrait;

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected DatabaseConnectionService $connectionService,
        protected SetL10nParentConfigurationValidator $configurationValidator
    ) {

    }

    /**
     * Tells whether a given configuration is valid
     *
     * @param array $configuration
     * @throws InvalidConfigurationException
     * @throws MissingClassException
     */
    public function isConfigurationValid(array $configuration): bool
    {
        return $this->configurationValidator->isValid($configuration);
    }

    /**
     * Finds the localization parent of the converted record
     * and translates it (adding the converted record as translation)
     *
     * @param array $configuration
     * @param array $convertedRecord
     * @param array $record
     * @return bool
     * @throws InvalidColumnMapException
     */
    public function process(array $configuration, &$convertedRecord, array &$record): bool
    {


        $subjectParentField = $configuration['subject']['parentField'];

        if (empty($convertedRecord[$subjectParentField])) {
            return false;
        }
        $subjectParentId = $convertedRecord[$subjectParentField];

        $matchValue = $subjectParentId;
        $prefix = '';
        if (!empty($configuration['parent']['prefix'])
            && is_string($configuration['parent']['prefix'])
        ) {
            $matchValue = $configuration['parent']['prefix'] . $matchValue;
        }

        $parentIdentityField = empty($configuration['parent']['identityField']) ? 'uid' : $configuration['parent']['identityField'];
        $parentMatchField = $configuration['parent']['matchField'];
        $parentTable = $configuration['parent']['table'];
        $queryBuilder = $this->connectionPool->getConnectionForTable($parentTable)
            ->createQueryBuilder();

        $matchValueType = Connection::PARAM_STR;
        if (is_integer($matchValue)) {
            $matchValueType = Connection::PARAM_INT;
        }

        $result = $queryBuilder->select($parentIdentityField)
            ->from($parentTable)
            ->where(
                $queryBuilder->expr()
                    ->eq(
                        $parentMatchField,
                        $queryBuilder->createNamedParameter($matchValue, $matchValueType)
                    )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (empty($result)) {
            return false;
        }
        $parentIdentityField = $configuration['parent']['identityField'];

        $convertedRecord['l10n_parent'] = $result[$parentIdentityField];

        if (!empty($configuration['setL10nSource'])) {
            $convertedRecord['l10n_source'] = $result[$parentIdentityField];
        }

        return true;
    }
}
