<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

\CPSIT\T3importExport\Configuration\Extension::registerAndConfigureModules();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_t3importexport_domain_model_exporttarget');
