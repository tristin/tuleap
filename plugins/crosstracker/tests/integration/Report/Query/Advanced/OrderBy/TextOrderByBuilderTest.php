<?php
/**
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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

namespace Tuleap\CrossTracker\Report\Query\Advanced\OrderBy;

use PFUser;
use ProjectUGroup;
use Tracker;
use Tuleap\CrossTracker\CrossTrackerQuery;
use Tuleap\CrossTracker\Report\Query\Advanced\CrossTrackerFieldTestCase;
use Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilder\Representations\NumericResultRepresentation;
use Tuleap\DB\DBFactory;
use Tuleap\DB\UUID;
use Tuleap\Test\Builders\CoreDatabaseBuilder;
use Tuleap\Tracker\Test\Builders\TrackerDatabaseBuilder;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class TextOrderByBuilderTest extends CrossTrackerFieldTestCase
{
    private UUID $uuid;
    private PFUser $user;
    /** @var list<int> */
    private array $result_descending;
    /** @var list<int> */
    private array $result_ascending;

    protected function setUp(): void
    {
        $db              = DBFactory::getMainTuleapDBConnection()->getDB();
        $tracker_builder = new TrackerDatabaseBuilder($db);
        $core_builder    = new CoreDatabaseBuilder($db);

        $project    = $core_builder->buildProject('project_name');
        $project_id = (int) $project->getID();
        $this->user = $core_builder->buildUser('project_member', 'Project Member', 'project_member@example.com');
        $core_builder->addUserToProjectMembers((int) $this->user->getId(), $project_id);
        $this->uuid = $this->addReportToProject(1, $project_id);

        $release_tracker = $tracker_builder->buildTracker($project_id, 'Release');
        $sprint_tracker  = $tracker_builder->buildTracker($project_id, 'Sprint');
        $tracker_builder->setViewPermissionOnTracker($release_tracker->getId(), Tracker::PERMISSION_FULL, ProjectUGroup::PROJECT_MEMBERS);
        $tracker_builder->setViewPermissionOnTracker($sprint_tracker->getId(), Tracker::PERMISSION_FULL, ProjectUGroup::PROJECT_MEMBERS);

        $release_text_field_id = $tracker_builder->buildTextField($release_tracker->getId(), 'text_field');
        $sprint_text_field_id  = $tracker_builder->buildTextField($sprint_tracker->getId(), 'text_field');

        $tracker_builder->grantReadPermissionOnField($release_text_field_id, ProjectUGroup::PROJECT_MEMBERS);
        $tracker_builder->grantReadPermissionOnField($sprint_text_field_id, ProjectUGroup::PROJECT_MEMBERS);

        $release_artifact_1_id = $tracker_builder->buildArtifact($release_tracker->getId());
        $release_artifact_2_id = $tracker_builder->buildArtifact($release_tracker->getId());
        $sprint_artifact_3_id  = $tracker_builder->buildArtifact($sprint_tracker->getId());
        $sprint_artifact_4_id  = $tracker_builder->buildArtifact($sprint_tracker->getId());

        $release_artifact_1_changeset = $tracker_builder->buildLastChangeset($release_artifact_1_id);
        $release_artifact_2_changeset = $tracker_builder->buildLastChangeset($release_artifact_2_id);
        $sprint_artifact_3_changeset  = $tracker_builder->buildLastChangeset($sprint_artifact_3_id);
        $sprint_artifact_4_changeset  = $tracker_builder->buildLastChangeset($sprint_artifact_4_id);

        $tracker_builder->buildTextValue(
            $release_artifact_1_changeset,
            $release_text_field_id,
            '1 **Hello World**',
            'commonmark'
        );
        $tracker_builder->buildTextValue(
            $release_artifact_2_changeset,
            $release_text_field_id,
            '10 <h1>Hello World</h1>',
            'text'
        );
        $tracker_builder->buildTextValue(
            $sprint_artifact_3_changeset,
            $sprint_text_field_id,
            '2 Test',
            'commonmark'
        );

        $tracker_builder->buildTextValue(
            $sprint_artifact_4_changeset,
            $sprint_text_field_id,
            'Gigouli',
            'commonmark'
        );
        $this->result_descending = [$release_artifact_2_id, $sprint_artifact_3_id, $release_artifact_1_id, $sprint_artifact_4_id];
        $this->result_ascending  = [$sprint_artifact_4_id, $release_artifact_1_id, $sprint_artifact_3_id, $release_artifact_2_id];
    }

    public function testLastUpdateDateDescending(): void
    {
        $result = $this->getQueryResults(
            new CrossTrackerQuery($this->uuid, 'SELECT @id FROM @project = "self" WHERE @id >= 1 ORDER BY text_field DESC', '', '', 1),
            $this->user,
        );

        $values = [];
        foreach ($result->artifacts as $artifact) {
            self::assertCount(2, $artifact);
            self::assertArrayHasKey('@id', $artifact);
            $value = $artifact['@id'];
            self::assertInstanceOf(NumericResultRepresentation::class, $value);
            $values[] = $value->value;
        }
        self::assertSame($this->result_descending, $values);
    }

    public function testLastUpdateDateAscending(): void
    {
        $result = $this->getQueryResults(
            new CrossTrackerQuery($this->uuid, 'SELECT @id FROM @project = "self" WHERE @id >= 1 ORDER BY text_field ASC', '', '', 1),
            $this->user,
        );

        $values = [];
        foreach ($result->artifacts as $artifact) {
            self::assertCount(2, $artifact);
            self::assertArrayHasKey('@id', $artifact);
            $value = $artifact['@id'];
            self::assertInstanceOf(NumericResultRepresentation::class, $value);
            $values[] = $value->value;
        }
        self::assertSame($this->result_ascending, $values);
    }
}
