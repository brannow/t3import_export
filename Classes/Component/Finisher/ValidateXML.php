<?php

namespace CPSIT\T3importExport\Component\Finisher;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Dirk Wenzel <wenzel@cps-it.de>
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use CPSIT\T3importExport\LoggingInterface;
use CPSIT\T3importExport\LoggingTrait;
use CPSIT\T3importExport\Messaging\MessageContainer;
use CPSIT\T3importExport\Resource\ResourceTrait;
use CPSIT\T3importExport\Validation\Configuration\ResourcePathConfigurationValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use XMLReader;

/**
 * Class ValidateXML
 */
class ValidateXML extends AbstractFinisher
    implements FinisherInterface, LoggingInterface
{
    use ResourceTrait, LoggingTrait;

    protected ResourcePathConfigurationValidator $pathValidator;

    /**
     * Notice by id
     * <unique id> => ['Title', ['Message']
     */
    final public const NOTICE_CODES = [
        1_508_776_068 => ['Validation failed', 'XML is invalid. There %1s %d %2s.'],
        1_508_914_030 => ['Validation succeed', 'XML is valid.'],
    ];

    /**
     * Error by id
     * <unique id> => ['Title', ['Message']
     */
    final public const ERROR_CODES = [
        1_508_774_170 => ['Invalid type for target schema', 'config[\'target\'][\'schema\'] must be a string, %s given.'],
        1_508_914_547 => ['Empty resource', 'Could not load resource or resource empty'],
    ];

    /**
     * ValidateXML constructor.
     * @param XMLReader|null $xmlReader
     * @param ResourcePathConfigurationValidator|null $validator
     * @param MessageContainer|null $messageContainer
     */
    public function __construct(
        XMLReader $xmlReader = null,
        ResourcePathConfigurationValidator $validator = null,
        MessageContainer $messageContainer = null
    ) {
        $this->xmlReader = $xmlReader ?? GeneralUtility::makeInstance(XMLReader::class);
        $this->pathValidator = $validator ?? GeneralUtility::makeInstance(ResourcePathConfigurationValidator::class);
        $this->messageContainer = $messageContainer ?? GeneralUtility::makeInstance(MessageContainer::class);
    }

    /**
     * @var XMLReader
     */
    protected $xmlReader;

    /**
     * Returns notice codes for current component.
     * Must be an array in the form
     * [
     *  <id> => ['title', 'description']
     * ]
     * 'description' may contain placeholder (%s) for arguments.
     * @return array
     */
    public function getNoticeCodes()
    {
        return static::NOTICE_CODES;
    }

    /**
     * Tells whether a given configuration is valid
     * Override this method in order to perform validation of
     * configuration
     *
     * @param array $configuration
     * @return bool
     */
    public function isConfigurationValid(array $configuration): bool
    {
        if (!$this->pathValidator->isValid($configuration)) {
            return false;
        }
        if (isset($configuration['target']['schema'])
            && !is_string($configuration['target']['schema'])) {
            $this->logError(1_508_774_170, [gettype($configuration['target']['schema'])]);
            return false;
        }

        return true;
    }

    /**
     * @param array $configuration
     * @param array $records Array with prepared records
     * @param array $result Array with result records
     * @return bool
     */
    public function process(array $configuration, array &$records, &$result): bool
    {

        $resource = $this->loadResource($configuration);
        if (empty($resource)) {
            $this->logError(1_508_914_547, null, [$configuration]);

            return false;
        }
        libxml_use_internal_errors(true);

        $this->xmlReader::XML($resource, null, LIBXML_DTDVALID);
        $this->xmlReader->setParserProperty(XMLReader::VALIDATE, true);

        if (!empty($configuration['schema']['file'])) {
            $schema = $this->getAbsoluteFilePath($configuration['schema']['file']);
            $this->xmlReader->setSchema($schema);
        }

        $this->xmlReader->read();
        $this->xmlReader->close();

        if (!$isValid = $this->xmlReader->isValid()) {
            $validationErrors = libxml_get_errors();
            $errorCount = count($validationErrors);
            $string1 = ($errorCount > 1)? 'were' : 'was';
            $string2 = ($errorCount > 1)? 'errors' : 'error';
            $this->logNotice(1_508_776_068, [$string1, $errorCount, $string2], $validationErrors);
        } else {
            // notification about validation success
            $this->logNotice(1_508_914_030);
        }
        //disable user error handling - will also clear any existing libxml errors
        libxml_use_internal_errors(false);


        return true;
    }
}
