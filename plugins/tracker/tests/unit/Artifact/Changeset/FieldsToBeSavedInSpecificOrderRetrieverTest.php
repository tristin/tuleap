<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Artifact\Changeset;

use Tracker_FormElementFactory;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Test\Builders\ArtifactTestBuilder;
use Tuleap\Tracker\Test\Builders\Fields\FileFieldBuilder;
use Tuleap\Tracker\Test\Builders\Fields\IntFieldBuilder;
use Tuleap\Tracker\Test\Builders\Fields\TextFieldBuilder;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class FieldsToBeSavedInSpecificOrderRetrieverTest extends TestCase
{
    public function testGetFiles(): void
    {
        $tracker  = TrackerTestBuilder::aTracker()->build();
        $artifact = ArtifactTestBuilder::anArtifact(135)->inTracker($tracker)->build();

        $text_field = TextFieldBuilder::aTextField(125)->build();
        $file_field = FileFieldBuilder::aFileField(126)->build();
        $int_field  = IntFieldBuilder::anIntField(127)->build();

        $factory = $this->createPartialMock(Tracker_FormElementFactory::class, ['getUsedFields']);
        $factory->method('getUsedFields')->willReturn([$text_field, $file_field, $int_field]);

        $retriever = new FieldsToBeSavedInSpecificOrderRetriever($factory);
        self::assertEquals([$file_field, $text_field, $int_field], $retriever->getFields($artifact));
    }
}
