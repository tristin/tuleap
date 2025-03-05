<?php
/**
 * Copyright (c) Enalean, 2023 - present. All Rights Reserved.
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

namespace Tuleap\PullRequest\GitReference;

use Tuleap\Test\PHPUnit\TestCase;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class BypassBrokenGitReferenceCheckTest extends TestCase
{
    public function testItReturnsAnOptionObjectWithValueWhenCheckIsRequired(): void
    {
        self::assertTrue(BypassBrokenGitReferenceCheck::check()->isValue());
    }

    public function testItReturnsAnOptionObjectWithNoValueWhenCheckIsSkipped(): void
    {
        self::assertFalse(BypassBrokenGitReferenceCheck::skip()->isValue());
    }
}
