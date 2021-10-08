<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement;

use PHPUnit\Framework\MockObject\Stub;
use Tuleap\ProgramManagement\Adapter\Program\Backlog\ProgramIncrement\Source\SourceArtifactNatureAnalyzer;
use Tuleap\ProgramManagement\Adapter\Workspace\ProjectProxy;
use Tuleap\ProgramManagement\Domain\Team\MirroredTimebox\TimeboxOfMirroredTimeboxNotFoundException;
use Tuleap\ProgramManagement\Domain\Workspace\ArtifactIdentifier;
use Tuleap\ProgramManagement\Domain\Workspace\UserIdentifier;
use Tuleap\ProgramManagement\Tests\Stub\ArtifactIdentifierStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveTimeboxFromMirroredTimeboxStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveUserStub;
use Tuleap\ProgramManagement\Tests\Stub\UserIdentifierStub;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class SourceArtifactNatureAnalyzerTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const TIMEBOX_ID = 247;
    private RetrieveTimeboxFromMirroredTimeboxStub $timebox_retriever;
    /**
     * @var Stub&\Tracker_ArtifactFactory
     */
    private $artifact_factory;
    private UserIdentifier $user_identifier;
    private \Project $project;

    protected function setUp(): void
    {
        $this->user_identifier = UserIdentifierStub::buildGenericUser();
        $this->project         = new \Project(['group_id' => 101, 'group_name' => 'A project', 'unix_group_name' => 'a_project']);

        $this->timebox_retriever = RetrieveTimeboxFromMirroredTimeboxStub::withTimebox(self::TIMEBOX_ID);
        $this->artifact_factory  = $this->createStub(\Tracker_ArtifactFactory::class);
    }

    private function getAnalyzer(): SourceArtifactNatureAnalyzer
    {
        return new SourceArtifactNatureAnalyzer(
            $this->timebox_retriever,
            $this->artifact_factory,
            RetrieveUserStub::withGenericUser()
        );
    }

    public function testItThrowsExceptionWhenProgramIncrementIdIsNotFound(): void
    {
        $this->timebox_retriever = RetrieveTimeboxFromMirroredTimeboxStub::withNoTimebox();

        $this->expectException(TimeboxOfMirroredTimeboxNotFoundException::class);
        $this->getAnalyzer()->retrieveProjectOfMirroredArtifact($this->getMirroredTimebox(), $this->user_identifier);
    }

    public function testItThrowsExceptionWhenProgramIncrementArtifactIsNotFound(): void
    {
        $this->artifact_factory->method('getArtifactById')->willReturn(null);

        $this->expectException(TimeboxOfMirroredTimeboxNotFoundException::class);
        $this->getAnalyzer()->retrieveProjectOfMirroredArtifact($this->getMirroredTimebox(), $this->user_identifier);
    }

    public function testReturnsProjectWhenArtifactHaveMirroredMilestoneLink(): void
    {
        $timebox = $this->getTimebox(true);
        $this->artifact_factory->method('getArtifactById')->willReturn($timebox);

        $result = $this->getAnalyzer()->retrieveProjectOfMirroredArtifact($this->getMirroredTimebox(), $this->user_identifier);
        self::assertEquals(ProjectProxy::buildFromProject($this->project), $result);
    }

    public function testItThrowsExceptionWhenUserCanNotSeeArtifact(): void
    {
        $timebox = $this->getTimebox(false);
        $this->artifact_factory->method('getArtifactById')->willReturn($timebox);

        $this->expectException(TimeboxOfMirroredTimeboxNotFoundException::class);
        $this->getAnalyzer()->retrieveProjectOfMirroredArtifact($this->getMirroredTimebox(), $this->user_identifier);
    }

    private function getMirroredTimebox(): ArtifactIdentifier
    {
        return ArtifactIdentifierStub::withId(1);
    }

    private function getTimebox(bool $user_can_view): Stub|Artifact
    {
        $timebox = $this->createStub(Artifact::class);
        $timebox->method('userCanView')->willReturn($user_can_view);
        $timebox->method('getTracker')->willReturn(TrackerTestBuilder::aTracker()->withProject($this->project)->build());

        return $timebox;
    }
}
