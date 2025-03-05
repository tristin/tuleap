<?php
/**
 * Copyright (c) Enalean 2021 -  Present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Adapter\Workspace\Tracker;

use Tuleap\ProgramManagement\Domain\TrackerNotFoundException;
use Tuleap\ProgramManagement\Tests\Builder\ProgramForAdministrationIdentifierBuilder;
use Tuleap\ProgramManagement\Tests\Stub\TrackerIdentifierStub;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class TrackerFactoryAdapterTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const TRACKER_ID = 85;
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\TrackerFactory
     */
    private $tracker_factory;

    protected function setUp(): void
    {
        $this->tracker_factory = $this->createStub(\TrackerFactory::class);
    }

    private function getAdapter(): TrackerFactoryAdapter
    {
        return new TrackerFactoryAdapter($this->tracker_factory);
    }

    public function testReturnArrayOfTrackerReference(): void
    {
        $project = ProjectTestBuilder::aProject()->build();
        $this->tracker_factory->method('getTrackersByGroupId')->willReturn(
            [
                TrackerTestBuilder::aTracker()->withId(20)->withName('Sprint')->withProject($project)->build(),
                TrackerTestBuilder::aTracker()->withId(30)->withName('Feature')->withProject($project)->build(),
            ]
        );

        $trackers_references = $this->getAdapter()->searchAllTrackersOfProgram(
            ProgramForAdministrationIdentifierBuilder::build()
        );

        self::assertCount(2, $trackers_references);
        [$first_tracker, $second_tracker] = $trackers_references;
        self::assertSame(20, $first_tracker->getId());
        self::assertSame('Sprint', $first_tracker->getLabel());
        self::assertSame(30, $second_tracker->getId());
        self::assertSame('Feature', $second_tracker->getLabel());
    }

    public function testReturnNullWhenNoTracker(): void
    {
        $this->tracker_factory->method('getTrackerById')->willReturn(null);

        self::assertNull($this->getAdapter()->getTrackerFromId(404));
    }

    public function testItReturnsTracker(): void
    {
        $project = ProjectTestBuilder::aProject()->build();
        $tracker = TrackerTestBuilder::aTracker()->withId(self::TRACKER_ID)->withProject($project)->build();
        $this->tracker_factory->method('getTrackerById')->willReturn($tracker);

        $result = $this->getAdapter()->getTrackerFromId(self::TRACKER_ID);

        self::assertSame($tracker, $result);
    }

    public function testItReturnsTrackerFromIdentifier(): void
    {
        $tracker = TrackerTestBuilder::aTracker()->withId(self::TRACKER_ID)->build();
        $this->tracker_factory->method('getTrackerById')->willReturn($tracker);

        $result = $this->getAdapter()->getNonNullTracker(TrackerIdentifierStub::withId(self::TRACKER_ID));

        self::assertSame($tracker, $result);
    }

    public function testItThrowsWhenIdentifierDoesNotMatchAnyTracker(): void
    {
        $this->tracker_factory->method('getTrackerById')->willReturn(null);

        $this->expectException(TrackerNotFoundException::class);
        $this->getAdapter()->getNonNullTracker(TrackerIdentifierStub::withId(404));
    }
}
