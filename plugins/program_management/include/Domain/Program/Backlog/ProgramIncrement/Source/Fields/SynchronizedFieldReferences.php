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
 */

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement\Source\Fields;

use Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrementTracker\ProgramIncrementTrackerIdentifier;

/**
 * I hold all synchronized field references (identifier + label) for a given Timebox or Mirrored Timebox.
 * Synchronized fields are: Artifact link field, Title semantic field, Description semantic field,
 * Status semantic field, Timeframe semantic fields
 * @psalm-immutable
 */
final class SynchronizedFieldReferences
{
    private function __construct(
        public TitleFieldReference $title,
        public DescriptionFieldReference $description,
        public StatusFieldReference $status,
        public StartDateFieldReference $start_date
    ) {
    }

    public static function fromProgramIncrementTracker(
        GatherSynchronizedFields $gatherer,
        ProgramIncrementTrackerIdentifier $program_increment
    ): self {
        $title       = $gatherer->getTitleField($program_increment);
        $description = $gatherer->getDescriptionField($program_increment);
        $status      = $gatherer->getStatusField($program_increment);
        $start_date  = $gatherer->getStartDateField($program_increment);
        return new self($title, $description, $status, $start_date);
    }
}
