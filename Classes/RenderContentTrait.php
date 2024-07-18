<?php

namespace CPSIT\T3importExport;

use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class RenderContentTrait
 *
 * @package CPSIT\T3importExport
 */
trait RenderContentTrait
{
    /**
     * Get a ContentObjectRenderer
     */
    public function getContentObjectRenderer(): ContentObjectRenderer
    {
        if(!$this->contentObjectRenderer instanceof ContentObjectRenderer)
        {
            $this->assertTypoScriptFrontendController();
            $this->contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        }

        return $this->contentObjectRenderer;
    }


    public function getTypoScriptService(): TypoScriptService
    {
        if (!$this->typoScriptService instanceof TypoScriptService)
        {
            $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        }

        return $this->typoScriptService;
    }
    /**
     * Renders content using TypoScript objects
     * @param array $record Optional data array
     * @param array $configuration Plain or TypoScript array
     * @return mixed|null Returns rendered content for each valid TypoScript object or null.
     * @throws ContentRenderingException
     */
    public function renderContent(array $record, array $configuration)
    {
        $typoScriptConf = $this->getTypoScriptService()
            ->convertPlainArrayToTypoScriptArray($configuration);
        /** @var AbstractContentObject $contentObject */
        $contentObject = $this->getContentObjectRenderer()
            ->getContentObject($configuration['_typoScriptNodeValue']);

        if ($contentObject !== null) {
            $this->contentObjectRenderer->start($record);

            return $contentObject->render($typoScriptConf);
        }

        return null;
    }

    /**
     * Gets the TypoScriptFrontendController
     * only for testing
     *
     * @return TypoScriptFrontendController
     */
    public function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    protected function assertTypoScriptFrontendController(): void
    {
        /**
         * initialize TypoScriptFrontendController (with page and type 0)
         * This is necessary for PreProcessor\RenderContent if configuration contains COA objects
         * ContentObjectRenderer fails in method cObjGetSingle since
         * getTypoScriptFrontendController return NULL instead of $GLOBALS['TSFE']
         */
        if (!$this->getTypoScriptFrontendController() instanceof TypoScriptFrontendController) {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByRootPageId(1);
            $fakeUri = new Uri('https://domain.org/page');
            $siteLanguage = GeneralUtility::makeInstance(
                SiteLanguage::class,
                0,
                'en-EN',
                $fakeUri,
                []
            );
            $pageArguments = GeneralUtility::makeInstance(PageArguments::class, 1, '1', []);
            $nullFrontend = GeneralUtility::makeInstance(NullFrontend::class, 'pages');
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
            try {
                $cacheManager->registerCache($nullFrontend);
            } catch (\Exception $exception) {
                unset($exception);
            }

            $fakeRequest = new ServerRequest($fakeUri);
            $originalRequest = $GLOBALS['TYPO3_REQUEST'];
            $GLOBALS['TYPO3_REQUEST'] = $fakeRequest;
            $GLOBALS['TSFE'] = new TypoScriptFrontendController(
                GeneralUtility::makeInstance(Context::class),
                $site,
                $siteLanguage,
                $pageArguments,
                $frontendUser
            );

            $GLOBALS['TYPO3_REQUEST'] = $originalRequest;

        }
    }
}
