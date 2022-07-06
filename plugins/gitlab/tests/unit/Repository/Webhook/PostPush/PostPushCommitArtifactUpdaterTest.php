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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Repository\Webhook\PostPush;

use Psr\Log\NullLogger;
use Tracker_FormElement_Field_List_Bind_StaticValue;
use Tracker_Workflow_WorkflowUser;
use Tuleap\Gitlab\Test\Stub\ArtifactClosingCommentInCommonMarkFormatStub;
use Tuleap\NeverThrow\Err;
use Tuleap\NeverThrow\Fault;
use Tuleap\NeverThrow\Ok;
use Tuleap\NeverThrow\Result;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Semantic\Status\Done\DoneValueRetriever;
use Tuleap\Tracker\Semantic\Status\Done\SemanticDoneValueNotFoundException;
use Tuleap\Tracker\Semantic\Status\SemanticStatusClosedValueNotFoundException;
use Tuleap\Tracker\Semantic\Status\StatusValueRetriever;
use Tuleap\Tracker\Test\Builders\ChangesetTestBuilder;
use Tuleap\Tracker\Test\Builders\NewCommentTestBuilder;
use Tuleap\Tracker\Test\Stub\CreateCommentOnlyChangesetStub;
use Tuleap\Tracker\Test\Stub\CreateNewChangesetStub;
use Tuleap\Tracker\Workflow\NoPossibleValueException;

final class PostPushCommitArtifactUpdaterTest extends TestCase
{
    private const COMMIT_SHA1          = '99aa042c9c';
    private const COMMITTER_USERNAME   = 'asticotc';
    private const STATUS_FIELD_ID      = 18;
    private const DONE_BIND_VALUE_ID   = 1234;
    private const CLOSED_BIND_VALUE_ID = 3174;
    private const DONE_LABEL           = 'Done';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&StatusValueRetriever
     */
    private $status_value_retriever;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&DoneValueRetriever
     */
    private $done_value_retriever;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&Artifact
     */
    private $artifact;
    private Tracker_Workflow_WorkflowUser $workflow_user;
    private string $success_message;
    private string $no_semantic_defined_message;
    private CreateCommentOnlyChangesetStub $comment_creator;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\Tracker_Semantic_Status
     */
    private $status_semantic;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\Tracker_FormElement_Field_List
     */
    private $status_field;
    private CreateNewChangesetStub $changeset_creator;

    protected function setUp(): void
    {
        $this->status_value_retriever = $this->createMock(StatusValueRetriever::class);
        $this->done_value_retriever   = $this->createMock(DoneValueRetriever::class);
        $this->comment_creator        = CreateCommentOnlyChangesetStub::withChangeset(
            ChangesetTestBuilder::aChangeset('5438')->build()
        );
        $this->changeset_creator      = CreateNewChangesetStub::withReturnChangeset(
            ChangesetTestBuilder::aChangeset('2452')->build()
        );

        $this->workflow_user = new Tracker_Workflow_WorkflowUser(
            [
                'user_id'     => Tracker_Workflow_WorkflowUser::ID,
                'language_id' => 'en',
            ]
        );

        $this->artifact = $this->createStub(Artifact::class);
        $this->artifact->method('getId')->willReturn(25);
        $this->status_semantic = $this->createStub(\Tracker_Semantic_Status::class);
        $this->status_field    = $this->createStub(\Tracker_FormElement_Field_List::class);
        $this->status_field->method('getId')->willReturn(self::STATUS_FIELD_ID);

        $this->success_message             = sprintf(
            'solved by @%s with gitlab_commit #MyRepo/%s',
            self::COMMITTER_USERNAME,
            self::COMMIT_SHA1
        );
        $this->no_semantic_defined_message = sprintf(
            '@%s attempts to close this artifact from GitLab but neither done nor status semantic defined.',
            self::COMMITTER_USERNAME
        );
    }

    /**
     * @return Ok<null> | Err<Fault>
     */
    private function closeTuleapArtifact(): Ok|Err
    {
        $no_semantic_comment = NewCommentTestBuilder::aNewComment($this->no_semantic_defined_message)->withSubmitter(
            $this->workflow_user
        )->build();

        $updater = new PostPushCommitArtifactUpdater(
            $this->status_value_retriever,
            $this->done_value_retriever,
            new NullLogger(),
            $this->comment_creator,
            $this->changeset_creator,
        );
        return $updater->closeTuleapArtifact(
            $this->artifact,
            $this->workflow_user,
            $this->status_semantic,
            ArtifactClosingCommentInCommonMarkFormatStub::fromString($this->success_message),
            $no_semantic_comment,
        );
    }

    public function testItClosesArtifactWithDoneValue(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsFound();
        $this->mockDoneValueIsFound();

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isOk($result));
        $new_changeset = $this->changeset_creator->getNewChangeset();
        if (! $new_changeset) {
            throw new \Exception('Expected to receive a new changeset');
        }
        self::assertSame($this->artifact, $new_changeset->getArtifact());
        self::assertSame($this->workflow_user, $new_changeset->getSubmitter());
        self::assertSame($this->success_message, $new_changeset->getComment()->getBody());
        self::assertEqualsCanonicalizing(
            [self::STATUS_FIELD_ID => self::DONE_BIND_VALUE_ID],
            $new_changeset->getFieldsData()
        );
    }

    public function testItClosesArtifactWithFirstClosedStatusValue(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsFound();
        $this->mockNoDoneValue();
        $this->mockClosedValueIsFound();

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isOk($result));
        $new_changeset = $this->changeset_creator->getNewChangeset();
        if (! $new_changeset) {
            throw new \Exception('Expected to receive a new changeset');
        }
        self::assertSame($this->artifact, $new_changeset->getArtifact());
        self::assertSame($this->workflow_user, $new_changeset->getSubmitter());
        self::assertSame($this->success_message, $new_changeset->getComment()->getBody());
        self::assertEqualsCanonicalizing(
            [self::STATUS_FIELD_ID => self::CLOSED_BIND_VALUE_ID],
            $new_changeset->getFieldsData()
        );
    }

    public function testItReturnsErrIfArtifactIsAlreadyClosed(): void
    {
        $this->artifact->method('isOpen')->willReturn(false);

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isErr($result));
        self::assertInstanceOf(ArtifactIsAlreadyClosedFault::class, $result->error);
        self::assertNull($this->changeset_creator->getNewChangeset());
    }

    public function testItReturnsErrIfNoPossibleValueAreFound(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsFound();

        $this->done_value_retriever->method('getFirstDoneValueUserCanRead')
            ->with($this->artifact, $this->workflow_user)
            ->willThrowException(new NoPossibleValueException());

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isErr($result));
        self::assertNull($this->changeset_creator->getNewChangeset());
    }

    public function testItReturnsErrIfChangesetIsNotCreated(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsFound();
        $this->mockDoneValueIsFound();
        $this->changeset_creator = CreateNewChangesetStub::withNullReturnChangeset();

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isErr($result));
        self::assertNotNull($this->changeset_creator->getNewChangeset());
    }

    public function testItReturnsErrIfAnErrorOccursDuringTheChangesetCreation(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsFound();
        $this->mockDoneValueIsFound();
        $this->changeset_creator = CreateNewChangesetStub::withException(new \Tracker_NoChangeException(1, 'xref'));

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isErr($result));
        self::assertNotNull($this->changeset_creator->getNewChangeset());
    }

    public function testItAddsOnlyACommentIfStatusSemanticIsNotDefined(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsNotDefined();

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isOk($result));
        $new_comment = $this->comment_creator->getNewComment();
        if (! $new_comment) {
            throw new \Exception('Expected to receive a new comment');
        }
        self::assertSame($this->no_semantic_defined_message, $new_comment->getBody());
        self::assertSame($this->workflow_user, $new_comment->getSubmitter());
        self::assertSame($this->artifact, $this->comment_creator->getArtifact());
    }

    public function testItAddsOnlyACommentIfClosedValueNotFound(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsFound();
        $this->mockNoDoneValue();
        $this->status_value_retriever->expects(self::once())
            ->method("getFirstClosedValueUserCanRead")
            ->with($this->workflow_user, $this->artifact)
            ->willThrowException(new SemanticStatusClosedValueNotFoundException());

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isOk($result));
        $new_comment = $this->comment_creator->getNewComment();
        if (! $new_comment) {
            throw new \Exception('Expected to receive a new comment');
        }
        self::assertSame($this->no_semantic_defined_message, $new_comment->getBody());
        self::assertSame($this->workflow_user, $new_comment->getSubmitter());
        self::assertSame($this->artifact, $this->comment_creator->getArtifact());
    }

    public function testItReturnsErrIfAnErrorOccursDuringTheCommentCreation(): void
    {
        $this->mockArtifactIsOpen();
        $this->mockStatusFieldIsNotDefined();
        $this->comment_creator = CreateCommentOnlyChangesetStub::withFault(
            Fault::fromMessage('Error during comment creation')
        );

        $result = $this->closeTuleapArtifact();

        self::assertTrue(Result::isErr($result));
        self::assertNotNull($this->comment_creator->getNewComment());
    }

    private function mockArtifactIsOpen(): void
    {
        $this->artifact->method('isOpen')->willReturn(true);
    }

    private function mockStatusFieldIsFound(): void
    {
        $this->status_semantic->method('getField')->willReturn($this->status_field);
    }

    private function mockStatusFieldIsNotDefined(): void
    {
        $this->status_semantic->method('getField')->willReturn(null);
    }

    private function mockDoneValueIsFound(): void
    {
        $this->status_field->method('getFieldData')->willReturn(self::DONE_BIND_VALUE_ID);

        $this->done_value_retriever->expects(self::once())
            ->method("getFirstDoneValueUserCanRead")
            ->with($this->artifact, $this->workflow_user)
            ->willReturn($this->getDoneValue());
    }

    private function mockNoDoneValue(): void
    {
        $this->done_value_retriever->expects(self::once())
            ->method("getFirstDoneValueUserCanRead")
            ->with($this->artifact, $this->workflow_user)
            ->willThrowException(new SemanticDoneValueNotFoundException());
    }

    private function mockClosedValueIsFound(): void
    {
        $this->status_field->method('getFieldData')->willReturn(self::CLOSED_BIND_VALUE_ID);

        $this->status_value_retriever->expects(self::once())
            ->method("getFirstClosedValueUserCanRead")
            ->with($this->workflow_user, $this->artifact)
            ->willReturn($this->getDoneValue());
    }

    private function getDoneValue(): Tracker_FormElement_Field_List_Bind_StaticValue
    {
        return new Tracker_FormElement_Field_List_Bind_StaticValue(14, self::DONE_LABEL, '', 1, false);
    }
}
