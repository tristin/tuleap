<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\Project\Admin\Categories;

use CSRFSynchronizerToken;
use Project;
use Tuleap\Test\PHPUnit\TestCase;

final class UpdateCategoriesProcessorTest extends TestCase
{
    private UpdateCategoriesProcessor $processor;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&CategoryCollectionConsistencyChecker
     */
    private $category_collection_consistency_checker;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ProjectCategoriesUpdater
     */
    private $updater;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category_collection_consistency_checker = $this->createMock(CategoryCollectionConsistencyChecker::class);
        $this->updater                                 = $this->createMock(ProjectCategoriesUpdater::class);

        $this->processor = new UpdateCategoriesProcessor(
            $this->category_collection_consistency_checker,
            $this->updater
        );
    }

    public function testItDoesNothingIfCollectionIsNotChecked(): void
    {
        $csrf = $this->createMock(CSRFSynchronizerToken::class);

        $csrf
            ->expects(self::once())
            ->method('check');

        $exception = new class extends ProjectCategoriesException
        {
            public function getI18NMessage(): string
            {
                return '';
            }
        };

        $this->category_collection_consistency_checker
            ->expects(self::once())
            ->method('checkCollectionConsistency')
            ->willThrowException($exception);

        $this->updater
            ->expects(self::never())
            ->method('update');

        $this->expectException(get_class($exception));

        $this->processor->processUpdate(
            Project::buildForTest(),
            $csrf,
            CategoryCollection::buildFromWebPayload([])
        );
    }

    public function testItUpdatesTheProjectCategories(): void
    {
        $csrf = $this->createMock(CSRFSynchronizerToken::class);

        $csrf
            ->expects(self::once())
            ->method('check');

        $category_collection = CategoryCollection::buildFromWebPayload([]);
        $project             = Project::buildForTest();

        $this->category_collection_consistency_checker
            ->expects(self::once())
            ->method('checkCollectionConsistency');

        $this->updater
            ->expects(self::once())
            ->method('update')
            ->with(
                $project,
                $category_collection
            );

        $this->processor->processUpdate(
            $project,
            $csrf,
            $category_collection
        );
    }
}
