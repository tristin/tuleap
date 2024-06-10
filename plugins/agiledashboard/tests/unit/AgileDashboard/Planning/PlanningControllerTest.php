<?php
/**
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

namespace Tuleap\AgileDashboard\Planning;

use AgileDashboard_XMLFullStructureExporter;
use Codendi_Request;
use EventManager;
use Exception;
use ForgeConfig;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Planning_Controller;
use Planning_RequestValidator;
use PlanningFactory;
use PlanningParameters;
use PlanningPermissionsManager;
use Project;
use ProjectManager;
use Tracker_FormElementFactory;
use Tuleap\AgileDashboard\BreadCrumbDropdown\AdministrationCrumbBuilder;
use Tuleap\AgileDashboard\BreadCrumbDropdown\AgileDashboardCrumbBuilder;
use Tuleap\AgileDashboard\ExplicitBacklog\ArtifactsInExplicitBacklogDao;
use Tuleap\AgileDashboard\Planning\Admin\PlanningEditionPresenterBuilder;
use Tuleap\AgileDashboard\Planning\Admin\UpdateRequestValidator;
use Tuleap\AgileDashboard\Planning\RootPlanning\UpdateIsAllowedChecker;
use Tuleap\AgileDashboard\Test\Builders\PlanningBuilder;
use Tuleap\ForgeConfigSandbox;
use Tuleap\GlobalLanguageMock;
use Tuleap\GlobalResponseMock;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\DB\DBTransactionExecutorPassthrough;
use Tuleap\Test\PHPUnit\TestCase;

final class PlanningControllerTest extends TestCase
{
    use ForgeConfigSandbox;
    use GlobalLanguageMock;
    use GlobalResponseMock;

    public PlanningFactory&MockObject $planning_factory;
    public ArtifactsInExplicitBacklogDao&MockObject $explicit_backlog_dao;
    private PlanningUpdater&MockObject $planning_updater;
    private Planning_RequestValidator&MockObject $planning_request_validator;
    private EventManager&MockObject $event_manager;
    private Codendi_Request&MockObject $request;
    private Planning_Controller $planning_controller;
    private UpdateIsAllowedChecker&MockObject $root_planning_update_checker;
    private UpdateRequestValidator&MockObject $update_request_validator;
    private BacklogTrackersUpdateChecker&MockObject $backlog_trackers_update_checker;
    private Project $project;

    protected function setUp(): void
    {
        ForgeConfig::set('codendi_dir', AGILEDASHBOARD_BASE_DIR . '/../../..');

        $this->request = $this->createMock(Codendi_Request::class);
        $this->project = ProjectTestBuilder::aProject()->withId(101)->build();
        $this->request->method('getProject')->willReturn($this->project);

        $this->planning_factory     = $this->createMock(PlanningFactory::class);
        $this->explicit_backlog_dao = $this->createMock(ArtifactsInExplicitBacklogDao::class);

        $this->event_manager                   = $this->createMock(EventManager::class);
        $this->planning_request_validator      = $this->createMock(Planning_RequestValidator::class);
        $this->planning_updater                = $this->createMock(PlanningUpdater::class);
        $this->root_planning_update_checker    = $this->createMock(UpdateIsAllowedChecker::class);
        $this->update_request_validator        = $this->createMock(UpdateRequestValidator::class);
        $this->backlog_trackers_update_checker = $this->createMock(BacklogTrackersUpdateChecker::class);

        $this->planning_controller = new Planning_Controller(
            $this->request,
            $this->planning_factory,
            $this->createMock(ProjectManager::class),
            $this->createMock(AgileDashboard_XMLFullStructureExporter::class),
            $this->createMock(PlanningPermissionsManager::class),
            $this->createMock(ScrumPlanningFilter::class),
            $this->createMock(Tracker_FormElementFactory::class),
            $this->createMock(AgileDashboardCrumbBuilder::class),
            $this->createMock(AdministrationCrumbBuilder::class),
            new DBTransactionExecutorPassthrough(),
            $this->explicit_backlog_dao,
            $this->planning_updater,
            $this->event_manager,
            $this->planning_request_validator,
            $this->root_planning_update_checker,
            $this->createMock(PlanningEditionPresenterBuilder::class),
            $this->update_request_validator,
            $this->backlog_trackers_update_checker,
        );
    }

    public function testItDeletesThePlanningAndRedirectsToTheIndex(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();
        $this->request->expects(self::exactly(2))->method('getCurrentUser')->willReturn($user);
        $this->request->expects(self::once())->method('get')->with('planning_id')->willReturn(42);

        $root_planning = PlanningBuilder::aPlanning(101)->withId(109)->build();
        $this->planning_factory->method('getRootPlanning')->willReturn($root_planning);
        $this->planning_factory->expects(self::once())->method('deletePlanning')->with(42);
        $this->explicit_backlog_dao->expects(self::never())->method('removeExplicitBacklogOfPlanning');

        $this->event_manager->expects(self::once())->method('dispatch');

        $GLOBALS['Response']->expects(self::once())->method('redirect')->with('/plugins/agiledashboard/?group_id=101&action=admin');

        $this->planning_controller->delete();
    }

    public function testItDeletesExplicitBacklogPlanning(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();
        $this->request->expects(self::exactly(2))->method('getCurrentUser')->willReturn($user);
        $this->request->expects(self::once())->method('get')->with('planning_id')->willReturn(42);

        $root_planning = PlanningBuilder::aPlanning(101)->withId(42)->build();
        $this->planning_factory->method('getRootPlanning')->willReturn($root_planning);
        $this->planning_factory->expects(self::once())->method('deletePlanning')->with(42);
        $this->explicit_backlog_dao->expects(self::once())->method('removeExplicitBacklogOfPlanning')->with(42);

        $this->event_manager->expects(self::once())->method('dispatch');

        $GLOBALS['Response']->expects(self::once())->method('redirect')->with('/plugins/agiledashboard/?group_id=101&action=admin');

        $this->planning_controller->delete();
    }

    public function testItDoesntDeleteAnythingIfTheUserIsNotAdmin(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withoutMemberOfProjects()
            ->withoutSiteAdministrator()
            ->build();
        $this->request->expects(self::once())->method('getCurrentUser')->willReturn($user);
        $this->request->expects(self::never())->method('get')->with('planning_id');

        // redirect() is a never return method, but phpunit mock system cannot handle it, so replace the exit() call by an exception
        $GLOBALS['Response']->expects(self::once())->method('redirect')->willThrowException(new Exception());

        self::expectException(Exception::class);
        $this->planning_controller->delete();
    }

    public function testItOnlyUpdateCardWallConfigWhenRequestIsInvalid(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();

        $this->request->expects(self::once())->method('getCurrentUser')->willReturn($user);
        $this->request->method('get')->with('planning_id')->willReturn(1);

        $GLOBALS['Response']->expects(self::once())->method('addFeedback');

        $this->event_manager->expects(self::once())->method('processEvent');
        $this->event_manager->expects(self::once())->method('dispatch');

        $planning = PlanningBuilder::aPlanning(101)->build();
        $this->planning_factory->expects(self::exactly(2))->method('getPlanning')->willReturn($planning);
        $this->planning_factory->expects(self::once())->method('getPlanningTrackerIdsByGroupId')->willReturn([]);
        $this->update_request_validator->expects(self::once())->method('getValidatedPlanning')->willReturn(null);

        $GLOBALS['Response']->expects(self::once())->method('redirect');

        $this->planning_updater->expects(self::never())->method('update');

        $this->planning_controller->update();
    }

    public function testItOnlyUpdateCardWallConfigWhenRootPlanningCannotBeUpdated(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();

        $this->request->expects(self::exactly(2))->method('getCurrentUser')->willReturn($user);
        $planning_parameters = [];
        $this->request->method('get')
            ->will(self::returnCallback(static fn(string $arg) => match ($arg) {
                'planning_id' => 1,
                'planning'    => $planning_parameters,
                default       => throw new LogicException("Should not be called with '$arg'"),
            }));

        $GLOBALS['Response']->expects(self::once())->method('addFeedback');

        $this->event_manager->expects(self::once())->method('processEvent');
        $this->event_manager->expects(self::once())->method('dispatch');

        $planning = PlanningBuilder::aPlanning(101)->build();
        $this->planning_factory->expects(self::exactly(2))->method('getPlanning')->willReturn($planning);
        $this->planning_factory->expects(self::once())->method('getPlanningTrackerIdsByGroupId')->willReturn([]);

        $this->update_request_validator->method('getValidatedPlanning')->willReturn(PlanningParameters::fromArray([]));
        $this->root_planning_update_checker->expects(self::once())->method('checkUpdateIsAllowed')
            ->willThrowException(new TrackerHaveAtLeastOneAddToTopBacklogPostActionException([]));

        $this->planning_updater->expects(self::never())->method('update');
        $this->backlog_trackers_update_checker->method('checkProvidedBacklogTrackersAreValid');

        $GLOBALS['Response']->expects(self::once())->method('redirect');

        $this->planning_controller->update();
    }

    public function testItOnlyUpdateCardWallConfigWhenPlanningCannotBeUpdated(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();

        $this->request->expects(self::exactly(2))->method('getCurrentUser')->willReturn($user);
        $planning_parameters = [];
        $this->request->method('get')
            ->will(self::returnCallback(static fn(string $arg) => match ($arg) {
                'planning_id' => 1,
                'planning'    => $planning_parameters,
                default       => throw new LogicException("Should not be called with '$arg'"),
            }));

        $GLOBALS['Response']->expects(self::once())->method('addFeedback');

        $this->event_manager->expects(self::once())->method('processEvent');
        $this->event_manager->expects(self::once())->method('dispatch');

        $planning = PlanningBuilder::aPlanning(101)->build();
        $this->planning_factory->expects(self::exactly(2))->method('getPlanning')->willReturn($planning);
        $this->planning_factory->expects(self::once())->method('getPlanningTrackerIdsByGroupId')->willReturn([]);

        $this->update_request_validator->method('getValidatedPlanning')->willReturn(PlanningParameters::fromArray([]));
        $this->backlog_trackers_update_checker->method('checkProvidedBacklogTrackersAreValid')->willThrowException(
            new TrackersHaveAtLeastOneHierarchicalLinkException('tracker01', 'tracker02')
        );
        $this->root_planning_update_checker->expects(self::never())->method('checkUpdateIsAllowed');

        $this->planning_updater->expects(self::never())->method('update');

        $GLOBALS['Response']->expects(self::once())->method('redirect');

        $this->planning_controller->update();
    }

    public function testItUpdatesThePlanning(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();

        $this->request->expects(self::exactly(2))->method('getCurrentUser')->willReturn($user);
        $planning_parameters = [];
        $this->request->method('get')
            ->will(self::returnCallback(static fn(string $arg) => match ($arg) {
                'planning_id' => 1,
                'planning'    => $planning_parameters,
                default       => throw new LogicException("Should not be called with '$arg'"),
            }));

        $GLOBALS['Response']->expects(self::once())->method('addFeedback');

        $this->event_manager->expects(self::once())->method('processEvent');
        $this->event_manager->expects(self::once())->method('dispatch');

        $planning = PlanningBuilder::aPlanning(101)->build();
        $this->planning_factory->expects(self::exactly(2))->method('getPlanning')->willReturn($planning);
        $this->planning_factory->expects(self::once())->method('getPlanningTrackerIdsByGroupId')->willReturn([]);

        $this->update_request_validator->method('getValidatedPlanning')->willReturn(PlanningParameters::fromArray([]));
        $this->root_planning_update_checker->expects(self::once())->method('checkUpdateIsAllowed');
        $this->backlog_trackers_update_checker->method('checkProvidedBacklogTrackersAreValid');

        $this->planning_updater->expects(self::once())->method('update');

        $GLOBALS['Response']->expects(self::once())->method('redirect');

        $this->planning_controller->update();
    }

    public function testItShowsAnErrorMessageAndRedirectsBackToTheCreationForm(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();

        $this->request->method('getCurrentUser')->willReturn($user);
        $this->request->method('getProject')->willReturn($this->project);

        $this->planning_request_validator->method('isValid')->willReturn(false);

        $this->planning_factory->expects(self::never())->method('createPlanning');

        $GLOBALS['Response']->expects(self::once())->method('addFeedback');
        $GLOBALS['Response']->expects(self::once())->method('redirect')->with('/plugins/agiledashboard/?group_id=101&action=new');

        $this->planning_controller->create();
    }

    public function testItCreatesThePlanningAndRedirectsToTheIndex(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withAdministratorOf($this->project)
            ->build();

        $planning_parameters = [
            PlanningParameters::NAME                         => 'Release Planning',
            PlanningParameters::PLANNING_TRACKER_ID          => '3',
            PlanningParameters::BACKLOG_TITLE                => 'Release Backlog',
            PlanningParameters::PLANNING_TITLE               => 'Sprint Plan',
            PlanningParameters::BACKLOG_TRACKER_IDS          => ['2'],
            PlanningPermissionsManager::PERM_PRIORITY_CHANGE => ['2', '3'],
        ];

        $this->request->method('getCurrentUser')->willReturn($user);
        $this->request->method('get')->with('planning')->willReturn($planning_parameters);
        $this->request->method('getProject')->willReturn($this->project);

        $this->planning_request_validator->method('isValid')->willReturn(true);

        $this->planning_factory->expects(self::once())->method('createPlanning');

        $GLOBALS['Response']->expects(self::once())->method('addFeedback');
        $GLOBALS['Response']->expects(self::once())->method('redirect')->with('/plugins/agiledashboard/?group_id=101&action=admin');

        $this->planning_controller->create();
    }

    public function testItDoesntCreateAnythingIfTheUserIsNotAdmin(): void
    {
        $user = UserTestBuilder::anActiveUser()
            ->withoutMemberOfProjects()
            ->withoutSiteAdministrator()
            ->build();

        $this->request->method('getProject')->willReturn($this->project);
        $this->request->method('getCurrentUser')->willReturn($user);

        $this->planning_factory->expects(self::never())->method('createPlanning');

        $GLOBALS['Response']->expects(self::once())->method('addFeedback');
        // redirect() is a never return method, but phpunit mock system cannot handle it, so replace the exit() call by an exception
        $GLOBALS['Response']->expects(self::once())->method('redirect')->with('/plugins/agiledashboard/?group_id=101')->willThrowException(new Exception());

        self::expectException(Exception::class);

        $this->planning_controller->create();
    }
}
