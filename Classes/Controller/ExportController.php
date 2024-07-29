<?php
namespace CPSIT\T3importExport\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;

/***************************************************************
 *  Copyright notice
 *  (c) 2015 Dirk Wenzel <dirk.wenzel@cps-it.de>
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
class ExportController extends BaseController implements TransferControllerInterface
{
    final public const SETTINGS_KEY = 'export';
    public const TEMPLATE_PATH = 'Export/Index';

    /**
     * Export task action
     *
     * @param string $identifier
     *
     * @throws InvalidConfigurationException
     */
    public function exportTaskAction($identifier): ResponseInterface
    {
        $this->taskAction($identifier);
        $this->moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Export
     *
     * @param string $identifier
     *
     * @throws InvalidConfigurationException
     */
    public function exportSetAction($identifier): ResponseInterface
    {
        $this->setAction($identifier);
        $this->moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Gets the settings key
     *
     * @return string
     */
    public function getSettingsKey()
    {
        return self::SETTINGS_KEY;
    }
}
