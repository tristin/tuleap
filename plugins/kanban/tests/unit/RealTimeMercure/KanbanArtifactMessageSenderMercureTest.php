<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
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
namespace Tuleap\Kanban\RealTimeMercure;

use PHPUnit\Framework\MockObject\MockObject;
use Tracker_Semantic_Status;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\RealtimeMercure\RealTimeMercureArtifactMessageSender;
use Tuleap\Tracker\Test\Builders\ArtifactTestBuilder;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class KanbanArtifactMessageSenderMercureTest extends TestCase
{
    private RealTimeMercureArtifactMessageSender&MockObject $artifact_message_sender;
    private KanbanArtifactMessageBuilderMercure&MockObject $kanban_artifact_message_builder;
    private int $kanban_id = 1;
    private Tracker_Semantic_Status&MockObject $tracker_semantic;
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->kanban_artifact_message_builder = $this->createMock(KanbanArtifactMessageBuilderMercure::class);
        $this->artifact_message_sender         = $this->createMock(RealTimeMercureArtifactMessageSender::class);
        $this->tracker_semantic                = $this->createMock(Tracker_Semantic_Status::class);
    }

    public function testSendMessageArtifactCreatedNoError(): void
    {
        $artifact = ArtifactTestBuilder::anArtifact(1)->build();
        $this->kanban_artifact_message_builder->method('buildArtifactUpdated')->willReturn(new KanbanArtifactUpdatedMessageRepresentationMercure(1));
        $this->artifact_message_sender->expects($this->once())->method('sendMessage');
        $sender =  new KanbanArtifactMessageSenderMercure($this->artifact_message_sender, $this->kanban_artifact_message_builder);
        $sender->sendMessageArtifactCreated($artifact, $this->kanban_id);
    }

    public function testSendMessageArtifactUpdatedNoError(): void
    {
        $artifact = ArtifactTestBuilder::anArtifact(1)->build();
        $this->kanban_artifact_message_builder->method('buildArtifactUpdated')->willReturn(new KanbanArtifactUpdatedMessageRepresentationMercure(1));
        $this->artifact_message_sender->expects($this->once())->method('sendMessage');
        $sender =  new KanbanArtifactMessageSenderMercure($this->artifact_message_sender, $this->kanban_artifact_message_builder);
        $sender->sendMessageArtifactUpdated($artifact, $this->kanban_id);
    }

    public function testSendMessageArtifactMovedNoError(): void
    {
        $tracker  = TrackerTestBuilder::aTracker()->withId(1)->build();
        $artifact = ArtifactTestBuilder::anArtifact(1)->inTracker($tracker)->build();
        $this->kanban_artifact_message_builder->method('buildArtifactUpdated')->willReturn(new KanbanArtifactUpdatedMessageRepresentationMercure(1));
        $this->artifact_message_sender->expects($this->once())->method('sendMessage');
        $this->kanban_artifact_message_builder->method('buildArtifactMoved')->willReturn(new KanbanArtifactMovedMessageRepresentationMercure(
            [1, 2],
            2,
            101,
            102
        ));
        $sender =  new KanbanArtifactMessageSenderMercure($this->artifact_message_sender, $this->kanban_artifact_message_builder);
        $sender->sendMessageArtifactMoved($artifact, $this->kanban_id, $this->tracker_semantic);
    }

    public function testSendMessageArtifactMovedNoData(): void
    {
        $tracker  = TrackerTestBuilder::aTracker()->withId(1)->build();
        $artifact = ArtifactTestBuilder::anArtifact(1)->inTracker($tracker)->build();
        $this->kanban_artifact_message_builder->method('buildArtifactUpdated')->willReturn(new KanbanArtifactUpdatedMessageRepresentationMercure(1));
        $this->artifact_message_sender->expects($this->never())->method('sendMessage');
        $this->kanban_artifact_message_builder->method('buildArtifactMoved')->willReturn(null);
        $sender =  new KanbanArtifactMessageSenderMercure($this->artifact_message_sender, $this->kanban_artifact_message_builder);
        $sender->sendMessageArtifactMoved($artifact, $this->kanban_id, $this->tracker_semantic);
    }

    public function testSendMessageArtifactReorderedNoError(): void
    {
        $tracker  = TrackerTestBuilder::aTracker()->withId(1)->build();
        $artifact = ArtifactTestBuilder::anArtifact(1)->inTracker($tracker)->build();
        $this->kanban_artifact_message_builder->method('buildArtifactUpdated')->willReturn(new KanbanArtifactUpdatedMessageRepresentationMercure(1));
        $this->artifact_message_sender->expects($this->once())->method('sendMessage');
        $this->kanban_artifact_message_builder->method('buildArtifactMoved')->willReturn(new KanbanArtifactMovedMessageRepresentationMercure(
            [1, 2],
            2,
            101,
            102
        ));
        $sender =  new KanbanArtifactMessageSenderMercure($this->artifact_message_sender, $this->kanban_artifact_message_builder);
        $sender->sendMessageArtifactMoved($artifact, $this->kanban_id, $this->tracker_semantic);
    }

    public function testSendMessageArtifactReorAderedNoData(): void
    {
        $tracker  = TrackerTestBuilder::aTracker()->withId(1)->build();
        $artifact = ArtifactTestBuilder::anArtifact(1)->inTracker($tracker)->build();
        $this->kanban_artifact_message_builder->method('buildArtifactUpdated')->willReturn(new KanbanArtifactUpdatedMessageRepresentationMercure(1));
        $this->artifact_message_sender->expects($this->never())->method('sendMessage');
        $this->kanban_artifact_message_builder->method('buildArtifactMoved')->willReturn(null);
        $sender =  new KanbanArtifactMessageSenderMercure($this->artifact_message_sender, $this->kanban_artifact_message_builder);
        $sender->sendMessageArtifactMoved($artifact, $this->kanban_id, $this->tracker_semantic);
    }
}
