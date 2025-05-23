/*
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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
import { okAsync, errAsync } from "neverthrow";
import type { Fault } from "@tuleap/fault";
import type { LinkedArtifact } from "../../../src/domain/links/LinkedArtifact";
import type { RetrieveLinkedArtifactsByType } from "../../../src/domain/links/RetrieveLinkedArtifactsByType";

export const RetrieveLinkedArtifactsByTypeStub = {
    withSuccessiveLinkedArtifacts: (
        first_batch: readonly LinkedArtifact[],
        ...other_batches: readonly LinkedArtifact[][]
    ): RetrieveLinkedArtifactsByType => {
        const all_batches = [first_batch, ...other_batches];
        return {
            getLinkedArtifactsByLinkType: (): ResultAsync<ReadonlyArray<LinkedArtifact>, Fault> => {
                const batch = all_batches.shift();
                if (batch !== undefined) {
                    return okAsync(batch);
                }
                throw new Error("No linked artifacts configured");
            },
        };
    },

    withFault: (fault: Fault): RetrieveLinkedArtifactsByType => ({
        getLinkedArtifactsByLinkType: () => errAsync(fault),
    }),
};
