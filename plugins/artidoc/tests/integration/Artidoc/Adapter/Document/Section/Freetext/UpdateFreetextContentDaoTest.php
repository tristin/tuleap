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

namespace Tuleap\Artidoc\Adapter\Document\Section\Freetext;

use Tuleap\Artidoc\Adapter\Document\ArtidocDocument;
use Tuleap\Artidoc\Adapter\Document\Section\Freetext\Identifier\UUIDFreetextIdentifierFactory;
use Tuleap\Artidoc\Adapter\Document\Section\Identifier\UUIDSectionIdentifierFactory;
use Tuleap\Artidoc\Adapter\Document\Section\RetrieveArtidocSectionDao;
use Tuleap\Artidoc\Adapter\Document\Section\SaveSectionDao;
use Tuleap\Artidoc\Adapter\Document\Section\SectionsAsserter;
use Tuleap\Artidoc\Domain\Document\ArtidocWithContext;
use Tuleap\Artidoc\Domain\Document\Section\ContentToInsert;
use Tuleap\Artidoc\Domain\Document\Section\Freetext\FreetextContent;
use Tuleap\Artidoc\Domain\Document\Section\Freetext\Identifier\FreetextIdentifierFactory;
use Tuleap\Artidoc\Domain\Document\Section\Freetext\RawSectionContentFreetext;
use Tuleap\Artidoc\Domain\Document\Section\Identifier\SectionIdentifierFactory;
use Tuleap\DB\DBFactory;
use Tuleap\NeverThrow\Fault;
use Tuleap\NeverThrow\Result;
use Tuleap\Test\PHPUnit\TestIntegrationTestCase;

final class UpdateFreetextContentDaoTest extends TestIntegrationTestCase
{
    public function testUpdateFreetextContent(): void
    {
        $artidoc = new ArtidocWithContext(new ArtidocDocument(['item_id' => 101]));
        $this->createArtidocSections($artidoc, [
            ContentToInsert::fromFreetext(new FreetextContent('Intro', '')),
            ContentToInsert::fromFreetext(new FreetextContent('Requirements', '')),
            ContentToInsert::fromArtifactId(1001),
            ContentToInsert::fromArtifactId(1002),
        ]);
        SectionsAsserter::assertSectionsForDocument($artidoc, ['Intro', 'Requirements', 1001, 1002]);

        $search = new RetrieveArtidocSectionDao($this->getSectionIdentifierFactory(), $this->getFreetextIdentifierFactory());

        $paginated_raw_sections = $search->searchPaginatedRawSections($artidoc, 1, 0);
        self::assertCount(1, $paginated_raw_sections->rows);
        self::assertTrue(Result::isOk($paginated_raw_sections->rows[0]->content->apply(
            static fn () => Result::err(Fault::fromMessage('Should get freetext, not an artifact section')),
            static function (RawSectionContentFreetext $freetext) use ($artidoc) {
                $dao = new UpdateFreetextContentDao();

                $dao->updateFreetextContent($freetext->id, new FreetextContent('Introduction', ''));

                SectionsAsserter::assertSectionsForDocument($artidoc, ['Introduction', 'Requirements', 1001, 1002]);

                return Result::ok(null);
            }
        )));
    }

    private function createArtidocSections(ArtidocWithContext $artidoc, array $content): void
    {
        $dao = new SaveSectionDao($this->getSectionIdentifierFactory(), $this->getFreetextIdentifierFactory());

        $db = DBFactory::getMainTuleapDBConnection()->getDB();
        $db->run('DELETE FROM plugin_artidoc_document WHERE item_id = ?', $artidoc->document->getId());

        foreach ($content as $content_to_insert) {
            $dao->saveSectionAtTheEnd($artidoc, $content_to_insert);
        }
    }

    /**
     * @return UUIDSectionIdentifierFactory
     */
    private function getSectionIdentifierFactory(): SectionIdentifierFactory
    {
        return new UUIDSectionIdentifierFactory(new \Tuleap\DB\DatabaseUUIDV7Factory());
    }

    /**
     * @return UUIDFreetextIdentifierFactory
     */
    private function getFreetextIdentifierFactory(): FreetextIdentifierFactory
    {
        return new UUIDFreetextIdentifierFactory(new \Tuleap\DB\DatabaseUUIDV7Factory());
    }
}
