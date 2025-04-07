<?php

namespace CodingMs\ViewStatistics\ViewHelpers\Page;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use CodingMs\ViewStatistics\Service\PageService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to get the rootline of a page.
 */
class RootlineViewHelper extends AbstractViewHelper
{

    /**
     * @var PageService
     */
    protected $pageService;

    /**
     * @param PageService $pageService
     */
    public function injectPageService(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'string', 'Template variable name to assign; if not specified the ViewHelper returns the variable instead.');
        $this->registerArgument('pageUid', 'int', 'Optional page uid to use.', false, 0);
    }

    /**
     * @return mixed
     */
    public function render()
    {
        $pageUid = (int)$this->arguments['pageUid'];
        if (0 === $pageUid) {
            /** @var ServerRequest $request */
            $request = $this->renderingContext->getRequest();
            $pageArguments = $request->getAttribute('routing');
            //$pageUid = $pageArguments->getPageId();
        }
        $rootLineData = $this->pageService->getRootLine($pageUid);
        $as = $this->arguments['as'];
        // If rootline variable already exists, remove it firstly
        if (true === $this->templateVariableContainer->exists($as)) {
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $rootLineData);
    }
}
