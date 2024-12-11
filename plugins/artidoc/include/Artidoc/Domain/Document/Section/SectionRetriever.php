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

namespace Tuleap\Artidoc\Domain\Document\Section;

use Tuleap\Artidoc\Domain\Document\ArtidocWithContext;
use Tuleap\Artidoc\Domain\Document\RetrieveArtidocWithContext;
use Tuleap\Artidoc\Domain\Document\Section\Identifier\SectionIdentifier;
use Tuleap\NeverThrow\Err;
use Tuleap\NeverThrow\Fault;
use Tuleap\NeverThrow\Ok;
use Tuleap\NeverThrow\Result;

final class SectionRetriever
{
    public function __construct(
        private SearchOneSection $search_section,
        private RetrieveArtidocWithContext $retrieve_artidoc,
        private CollectRequiredSectionInformation $collect_required_section_information,
    ) {
    }

    /**
     * @return Ok<RawSection>|Err<Fault>
     */
    public function retrieveSection(SectionIdentifier $id): Ok|Err
    {
        return $this->search_section->searchSectionById($id)
            ->match(
                fn (RawSection $raw_section) => $this->retrieve_artidoc->retrieveArtidocUserCanRead($raw_section->item_id)
                    ->andThen(fn (ArtidocWithContext $artidoc) => $this->collectRequiredSectionInformation($artidoc, $raw_section))
                    ->map(static fn() => $raw_section),
                static fn (Fault $fault) => Result::err($fault),
            );
    }

    private function collectRequiredSectionInformation(ArtidocWithContext $artidoc, RawSection $raw_section): Ok|Err
    {
        return $raw_section->content->apply(
            fn (int $artifact_id) => $this->collect_required_section_information->collectRequiredSectionInformation($artidoc, $artifact_id),
            static fn () => Result::ok(true),
        );
    }
}
