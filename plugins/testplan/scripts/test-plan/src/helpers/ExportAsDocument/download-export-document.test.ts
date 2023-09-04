/*
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

import { createVueGettextProviderPassthrough } from "../vue-gettext-provider-for-test";
import { downloadExportDocument } from "./download-export-document";
import * as report_creator from "./Reporter/report-creator";
import type {
    ArtifactFieldValueStepDefinitionEnhancedWithResults,
    ExportDocument,
} from "@tuleap/plugin-testmanagement/scripts/testmanagement/src/type";

describe("Start download of export document", () => {
    it("generates the report and start the download of the document", async () => {
        const gettext_provider = createVueGettextProviderPassthrough();

        const download_document = jest.fn();

        const create_export_report = jest
            .spyOn(report_creator, "createExportReport")
            .mockResolvedValue(
                {} as ExportDocument<ArtifactFieldValueStepDefinitionEnhancedWithResults>,
            );

        await downloadExportDocument(
            {
                platform_name: "My Tuleap Platform",
                platform_logo_url: "platform/logo/url",
                project_name: "ACME",
                user_display_name: "Jean Dupont",
                user_timezone: "UTC",
                user_locale: "en_US",
                title: "Tuleap 13.3",
                milestone_name: "Tuleap 13.3",
                parent_milestone_name: "",
                milestone_url: "/path/to/13.3",
                base_url: "https://example.com",
                artifact_links_types: [],
                testdefinition_tracker_id: 10,
            },
            gettext_provider,
            download_document,
            [],
            [],
        );

        expect(create_export_report).toHaveBeenCalledTimes(1);
        expect(download_document).toHaveBeenCalledTimes(1);
    });
});
