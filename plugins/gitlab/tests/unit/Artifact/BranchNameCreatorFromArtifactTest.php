<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Artifact;

use Cocur\Slugify\Slugify;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Artifact\Artifact;

final class BranchNameCreatorFromArtifactTest extends TestCase
{
    private BranchNameCreatorFromArtifact $branch_name_creator_from_artifact;

    protected function setUp(): void
    {
        $this->branch_name_creator_from_artifact = new BranchNameCreatorFromArtifact(new Slugify());
    }

    public function testBranchNameIsCreatedFromAnArtifactWithATitle(): void
    {
        $artifact = $this->getArtifact('art title');

        $branch_name = $this->branch_name_creator_from_artifact->getBranchName($artifact);
        self::assertEquals('tuleap-123-art_title', $branch_name);
    }

    public function testBranchNameIsCreatedFromAnArtifactWithoutTitle(): void
    {
        $artifact = $this->getArtifact(null);

        $branch_name = $this->branch_name_creator_from_artifact->getBranchName($artifact);
        self::assertEquals('tuleap-123', $branch_name);
    }

    public function testBranchNameIsCreatedFromAnArtifactWithAnEmptyTitle(): void
    {
        $artifact = $this->getArtifact('');

        $branch_name = $this->branch_name_creator_from_artifact->getBranchName($artifact);
        self::assertEquals('tuleap-123', $branch_name);
    }

    private function getArtifact(?string $title): Artifact
    {
        $artifact = $this->createMock(Artifact::class);
        $artifact->method('getId')->willReturn(123);
        $artifact->method('getTitle')->willReturn($title);
        return $artifact;
    }
}
