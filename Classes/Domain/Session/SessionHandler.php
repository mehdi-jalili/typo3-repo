<?php

declare(strict_types=1);

namespace CodingMs\ViewStatistics\Domain\Session;

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 Mehdi Jalili <typo3@coding.ms>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Session handling
 */
class SessionHandler implements SingletonInterface
{
    /**
     * Return stored session data
     * @param string $extension
     * @return array
     */
    public function restoreFromSession($extension = 'shop')
    {
        $sessionData = [];
        if ($frontendUserAuthentication = $this->getFrontendUserAuthentication()) {
            $serialized = $frontendUserAuthentication->getKey('ses', 'tx_' . $extension);
            if (isset($serialized)) {
                $unserialized = unserialize($serialized);
                if (is_array($unserialized)) {
                    $sessionData = $unserialized;
                }
            }
        }
        return $sessionData;
    }

    /**
     * Write session data
     * @param array|object $object any serializable object to store into the session
     * @param string $extension
     * @return    SessionHandler this
     */
    public function writeToSession($object, $extension = 'shop')
    {
        if ($frontendUserAuthentication = $this->getFrontendUserAuthentication()) {
            $frontendUserAuthentication->setKey('ses', 'tx_' . $extension, serialize($object));
            $frontendUserAuthentication->storeSessionData();
        }
        return $this;
    }

    /**
     * Clean up session
     * @param string $extension
     * @return    SessionHandler this
     */
    public function cleanUpSession($extension = 'shop')
    {
        if ($frontendUserAuthentication = $this->getFrontendUserAuthentication()) {
            $frontendUserAuthentication->setKey('ses', 'tx_' . $extension, null);
            $frontendUserAuthentication->storeSessionData();
        }
        return $this;
    }

    /**
     * @return FrontendUserAuthentication|null
     */
    protected function getFrontendUserAuthentication(): ?FrontendUserAuthentication
    {
        /** @var FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = $this->getServerRequest()->getAttribute('frontend.user');
        return $frontendUserAuthentication;
    }

    /**
     * @return ServerRequest
     */
    protected function getServerRequest(): ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
