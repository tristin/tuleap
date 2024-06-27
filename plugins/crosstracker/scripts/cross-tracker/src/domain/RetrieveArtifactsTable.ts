/*
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

import type { ResultAsync } from "neverthrow";
import type { Fault } from "@tuleap/fault";
import type { ArtifactsTable } from "./ArtifactsTable";

export type ArtifactsTableWithTotal = {
    readonly table: ArtifactsTable;
    readonly total: number;
};

export type RetrieveArtifactsTable = {
    getSelectableQueryResult(
        tracker_ids: ReadonlyArray<number>,
        expert_query: string,
        limit: number,
        offset: number,
    ): ResultAsync<ArtifactsTableWithTotal, Fault>;
};
