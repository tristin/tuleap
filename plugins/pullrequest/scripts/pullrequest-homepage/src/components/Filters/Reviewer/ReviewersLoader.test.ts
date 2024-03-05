/*
 * Copyright (c) Enalean, 2024 - present. All Rights Reserved.
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

import { describe, it, expect, vi } from "vitest";
import { okAsync, errAsync } from "neverthrow";
import { Fault } from "@tuleap/fault";
import type { User } from "@tuleap/plugin-pullrequest-rest-api-types";
import { UserStub } from "../../../../tests/stubs/UserStub";
import * as rest_querier from "../../../api/tuleap-rest-querier";
import { ReviewersLoader } from "./ReviewersLoader";

const reviewers: User[] = [
    UserStub.withIdAndName(101, "Joe l'asticot (jolasti)"),
    UserStub.withIdAndName(102, "John Doe (jdoe)"),
];

const repository_id = 2;
const noop = (): void => {
    // Do nothing
};

describe("ReviewersLoader", () => {
    it("should load the reviewers and return a collection of LazyboxItem", async () => {
        vi.spyOn(rest_querier, "fetchRepositoryReviewers").mockReturnValue(okAsync(reviewers));

        const loadItems = ReviewersLoader(noop, repository_id);
        const items = await loadItems();

        expect(rest_querier.fetchRepositoryReviewers).toHaveBeenCalledWith(repository_id);
        expect(items).toHaveLength(reviewers.length);
        expect(items).toStrictEqual([
            { value: reviewers[0], is_disabled: false },
            { value: reviewers[1], is_disabled: false },
        ]);
    });

    it("When an error occurres, Then it should call the on_error_callback and return an empty array", async () => {
        const tuleap_api_error = Fault.fromMessage("Oops!");
        const on_error_callback = vi.fn();

        vi.spyOn(rest_querier, "fetchRepositoryReviewers").mockReturnValue(
            errAsync(tuleap_api_error),
        );

        const loadItems = ReviewersLoader(on_error_callback, repository_id);
        const items = await loadItems();

        expect(on_error_callback).toHaveBeenCalledWith(tuleap_api_error);
        expect(items).toHaveLength(0);
    });
});
