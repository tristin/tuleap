<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

namespace Tuleap\Artidoc\REST\v1;

use Tuleap\Artidoc\Domain\Document\Section\Freetext\RawSectionContentFreetext;
use Tuleap\Artidoc\Domain\Document\Section\Identifier\SectionIdentifier;

/**
 * @psalm-immutable
 */
final readonly class FreetextSectionRepresentation implements SectionRepresentation
{
    private function __construct(
        public string $id,
        public string $title,
        public string $description,
    ) {
    }

    public static function fromRawSectionContentFreetext(
        SectionIdentifier $section_identifier,
        RawSectionContentFreetext $freetext,
    ): self {
        return new self(
            $section_identifier->toString(),
            $freetext->content->title,
            $freetext->content->description,
        );
    }
}
