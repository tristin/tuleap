<?php
/**
 * Copyright (c) Enalean, 2023 - Present. All Rights Reserved.
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

namespace Tuleap\Kanban;

final class CheckSplitKanbanConfiguration implements SplitKanbanConfigurationChecker
{
    public function isProjectAllowedToUseSplitKanban(\Project $project): bool
    {
        $list_of_project_ids_with_split_kanban = \ForgeConfig::getFeatureFlagArrayOfInt(SplitKanbanConfiguration::FEATURE_FLAG);

        if (! $list_of_project_ids_with_split_kanban) {
            return false;
        }

        if ($list_of_project_ids_with_split_kanban === [1]) {
            return true;
        }

        $is_activated = in_array((int) $project->getID(), $list_of_project_ids_with_split_kanban, true);

        return $is_activated;
    }
}
