<?php
namespace CPSIT\T3importExport\Service;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
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

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

/**
 * Interface DomainObjectTranslatorInterface
 *
 * @package CPSIT\T3importExport\Service
 */
interface DomainObjectTranslatorInterface
{
    /**
     * Translates a domain object
     *
     * @param DomainObjectInterface $origin
     * @param DomainObjectInterface $translation
     * @param int $language
     * @return void
     */
    public function translate(DomainObjectInterface $origin, DomainObjectInterface $translation, int $language): void;
}
