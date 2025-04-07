<?php

namespace CodingMs\ViewStatistics\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/***************************************************************
 *
 * Copyright notice
 *
 
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
 * Page view summary
 */
class PageViewSummary extends AbstractEntity
{

    protected string $creationDate;
    protected int $total;
    protected int $frontendUserTotal;

    public function getCreationDate(): string
    {
        return $this->creationDate;
    }

    public function setCreationDate(string $creationDate): void
    {
        $this->creationDate = $creationDate;
    }


    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getFrontendUserTotal(): int
    {
        return $this->frontendUserTotal;
    }

    public function setFrontendUserTotal(int $frontendUserTotal): void
    {
        $this->frontendUserTotal = $frontendUserTotal;
    }


}
