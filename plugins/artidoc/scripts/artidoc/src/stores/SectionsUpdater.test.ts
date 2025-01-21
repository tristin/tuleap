/*
 * Copyright (c) Enalean, 2025 - present. All Rights Reserved.
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

import { describe, it, expect } from "vitest";
import ArtifactSectionFactory from "@/helpers/artifact-section.factory";
import FreetextSectionFactory from "@/helpers/freetext-section.factory";
import type {
    ArtidocSection,
    FreetextSection,
    SectionBasedOnArtifact,
} from "@/helpers/artidoc-section.type";
import type { SectionsStore } from "@/stores/useSectionsStore";
import { buildSectionsStore } from "@/stores/useSectionsStore";
import { getSectionsUpdater } from "@/stores/SectionsUpdater";
import { CreateStoredSections } from "@/stores/CreateStoredSections";

describe("SectionsUpdater", () => {
    const getCollectionWithSections = (sections: ArtidocSection[]): SectionsStore => {
        const store = buildSectionsStore();
        store.replaceAll(CreateStoredSections.fromArtidocSectionsCollection(sections));
        return store;
    };

    it("should update the artifact section", () => {
        const section = ArtifactSectionFactory.create();
        const section_a = ArtifactSectionFactory.override({
            ...section,
            id: "section-a",
            title: {
                ...section.title,
                value: "Section A",
            },
        });
        const section_b = ArtifactSectionFactory.override({
            ...section,
            id: "section-b",
            title: {
                ...section.title,
                value: "Section B",
            },
        });

        const collection = getCollectionWithSections([section_a, section_b]);
        const updater = getSectionsUpdater(collection);

        updater.updateSection(
            ArtifactSectionFactory.override({
                ...section_b,
                title: {
                    ...section_b.title,
                    value: "Updated section B",
                },
            }),
        );

        const section_0: SectionBasedOnArtifact = collection.sections
            .value[0] as SectionBasedOnArtifact;
        const section_1: SectionBasedOnArtifact = collection.sections
            .value[1] as SectionBasedOnArtifact;

        expect(collection.sections.value).toHaveLength(2);
        expect(section_0.title.value).toBe("Section A");
        expect(section_1.title.value).toBe("Updated section B");
    });

    it("should update the freetext section", () => {
        const section = FreetextSectionFactory.create();
        const section_a = FreetextSectionFactory.override({
            ...section,
            id: "section-a",
            title: "Section A",
        });
        const section_b = FreetextSectionFactory.override({
            ...section,
            id: "section-b",
            title: "Section B",
        });

        const collection = getCollectionWithSections([section_a, section_b]);
        const updater = getSectionsUpdater(collection);

        updater.updateSection(
            FreetextSectionFactory.override({
                ...section_b,
                title: "Updated section B",
            }),
        );

        const section_0: FreetextSection = collection.sections.value[0] as FreetextSection;
        const section_1: FreetextSection = collection.sections.value[1] as FreetextSection;

        expect(collection.sections.value).toHaveLength(2);
        expect(section_0.title).toBe("Section A");
        expect(section_1.title).toBe("Updated section B");
    });
});
