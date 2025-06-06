<?php
namespace CPSIT\T3importExport\Property\TypeConverter;

use TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Exception;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class PersistentObjectConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter
{
    /**
     * @var string
     */
    final public const IGNORE_ENABLE_FIELDS = 'IGNORE_ENABLE_FIELDS';

    /**
     * @var string
     */
    final public const RESPECT_STORAGE_PAGE = 'RESPECT_STORAGE_PAGE';

    /**
     * @var string
     */
    final public const RESPECT_SYS_LANGUAGE = 'RESPECT_SYS_LANGUAGE';

    /**
     * @var string
     */
    final public const ENABLE_FIELDS_TO_BE_IGNORED = 'ENABLE_FIELDS_TO_BE_IGNORED';

    /**
     * @var string
     */
    final public const INCLUDE_DELETED = 'INCLUDE_DELETED';

    /**
     * @var string
     */
    final public const SYS_LANGUAGE_UID = 'SYS_LANGUAGE_UID';

    /**
     * @var string
     */
    final public const STORAGE_PAGE_IDS = 'STORAGE_PAGE_IDS';

    /**
     * @var int
     */
    protected $priority = 2;

    /**
     * @var bool
     */
    protected $ignoreEnableFields = false;

    /**
     * @var bool
     */
    protected $respectStoragePage = true;

    /**
     * @var bool
     */
    protected $respectSysLanguage = true;

    /**
     * @var array
     */
    protected $enableFieldsToBeIgnored = [];

    /**
     * @var bool
     */
    protected $includeDeleted = false;

    /**
     * @var int
     */
    protected $sysLanguageUid = 0;

    /**
     * @var array
     */
    protected $storagePageIds = [];

    /**
     * Convert an object from $source to an entity or a value object.
     *
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @throws \InvalidArgumentException
     * @return object the target type
     * @throws InvalidTargetException
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null): ?object
    {
        $this->setConfiguration($configuration);

        return parent::convertFrom($source, $targetType, $convertedChildProperties, $configuration);
    }

    /**
     * set configuration to overload query settings
     *
     * @param PropertyMappingConfigurationInterface $configuration
     */
    protected function setConfiguration(PropertyMappingConfigurationInterface $configuration = null)
    {
        if ($configuration === null) {
            return;
        }
        $class = static::class;
        $ignoreEnableFields = $configuration->getConfigurationValue($class, self::IGNORE_ENABLE_FIELDS);
        if (isset($ignoreEnableFields)) {
            $this->ignoreEnableFields = (bool)$ignoreEnableFields;
        }
        $enableFieldsToBeIgnored = $configuration->getConfigurationValue($class, self::ENABLE_FIELDS_TO_BE_IGNORED);
        if (isset($enableFieldsToBeIgnored)) {
            if (is_string($enableFieldsToBeIgnored)) {
                $this->enableFieldsToBeIgnored = explode(',', $enableFieldsToBeIgnored);
            }
            if (is_array($enableFieldsToBeIgnored)) {
                $this->enableFieldsToBeIgnored = $enableFieldsToBeIgnored;
            }
        }
        $respectStoragePage = $configuration->getConfigurationValue($class, self::RESPECT_STORAGE_PAGE);
        if (isset($respectStoragePage)) {
            $this->respectStoragePage = (bool)$respectStoragePage;
        }
        $respectSysLanguage = $configuration->getConfigurationValue($class, self::RESPECT_SYS_LANGUAGE);
        if (isset($respectSysLanguage)) {
            $this->respectSysLanguage = (bool)$respectSysLanguage;
        }
        $sysLanguageUid = $configuration->getConfigurationValue($class, self::SYS_LANGUAGE_UID);
        if (isset($sysLanguageUid)) {
            $this->sysLanguageUid = (int)$sysLanguageUid;
        }
        $includeDeleted = $configuration->getConfigurationValue($class, self::INCLUDE_DELETED);
        if (isset($includeDeleted)) {
            $this->includeDeleted = $includeDeleted;
        }
        $storagePages = $configuration->getConfigurationValue($class, self::STORAGE_PAGE_IDS);
        if (isset($storagePages)) {
            if (is_string($storagePages)) {
                $this->storagePageIds = GeneralUtility::intExplode(',', $storagePages);
            }
            if (is_array($storagePages)) {
                $this->storagePageIds = $storagePages;
            }
        }
    }

    /**
     * @param $targetType
     * @return QueryInterface
     */
    protected function buildQuery($targetType)
    {
        $query = $this->persistenceManager->createQueryForType($targetType);
        $querySettings = $query->getQuerySettings();

        $querySettings->setIgnoreEnableFields($this->ignoreEnableFields);
        $querySettings->setEnableFieldsToBeIgnored($this->enableFieldsToBeIgnored);
        $querySettings->setRespectStoragePage($this->respectStoragePage);
        $querySettings->setRespectSysLanguage($this->respectSysLanguage);
        $querySettings->setLanguageUid($this->sysLanguageUid);
        $querySettings->setIncludeDeleted($this->includeDeleted);
        $querySettings->setStoragePageIds($this->storagePageIds);
        $query->setQuerySettings($querySettings);

        return $query;
    }

    /**
     * Fetch an object from persistence layer.
     *
     * @param mixed $identity
     * @param string $targetType
     * @throws TargetNotFoundException
     * @throws InvalidSourceException
     * @return object
     */
    protected function fetchObjectFromPersistence($identity, $targetType): object
    {
        $object = null;
        if (ctype_digit((string) $identity)) {
            $object = $this->persistenceManager->getObjectByIdentifier($identity, $targetType);
            try {
                $object = parent::fetchObjectFromPersistence($identity, $targetType);
            } catch (Exception $e) {
                if ($this->respectStoragePage && empty($this->storagePageIds)) {
                    throw $e;
                }
            }
        } else {
            throw new InvalidSourceException('The identity property "' . $identity . '" is no UID.', 1_297_931_020);
        }
        if ($object === null) {
            $query = $this->buildQuery($targetType);
            $object = $query->matching($query->equals('uid', $identity))->execute()->getFirst();
            if ($object === null) {
                throw new TargetNotFoundException(
                    sprintf(
                        'Object of type %s with identity "%s" not found.',
                        $targetType,
                        print_r($identity, true)
                    ),
                    1_297_933_823
                );
            }
        }

        return $object;
    }
}
