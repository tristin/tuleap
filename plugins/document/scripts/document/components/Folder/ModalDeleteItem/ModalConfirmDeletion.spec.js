/*
 * Copyright (c) Enalean, 2019. All Rights Reserved.
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
 *
 */

import { shallowMount } from "@vue/test-utils";
import localVue from "../../../helpers/local-vue.js";
import { USER_CANNOT_PROPAGATE_DELETION_TO_WIKI_SERVICE } from "../../../constants.js";
import ConfirmationModal from "./ModalConfirmDeletion.vue";
import { tlp } from "tlp-mocks";
import { createStoreMock } from "@tuleap-vue-components/store-wrapper.js";

describe("ModalConfirmDeletion", () => {
    let state, store_options, store;

    function getDeletionModal(item = {}) {
        return shallowMount(ConfirmationModal, {
            localVue,
            mocks: { $store: store },
            propsData: {
                item: { ...item }
            },
            stubs: {
                "delete-associated-wiki-page-checkbox": `<div data-test="checkbox"></div>`
            }
        });
    }

    beforeEach(() => {
        state = {
            error: {
                has_modal_error: false
            }
        };
        store_options = {
            state
        };
        store = createStoreMock(store_options);

        tlp.modal.and.returnValue({
            addEventListener: () => {},
            show: jasmine.createSpy("show")
        });

        store.getters.is_item_a_wiki = () => false;
        store.getters.is_item_a_folder = () => false;
    });

    describe("When the item is a wiki", () => {
        let item;

        beforeEach(() => {
            item = {
                id: 42,
                title: "my wiki",
                wiki_properties: {
                    page_name: "my wiki",
                    page_id: 123
                },
                type: "wiki"
            };

            store.getters.is_item_a_wiki = () => true;
        });

        it(`When some docman wikis reference the same wiki page, then it should add a checkbox`, async () => {
            store.dispatch.withArgs("getWikisReferencingSameWikiPage", item).and.returnValue([
                {
                    id: 43,
                    title: "my other wiki",
                    wiki_properties: {
                        page_name: "my wiki",
                        page_id: 123
                    },
                    type: "wiki"
                }
            ]);

            const deletion_modal = await getDeletionModal(item);

            expect(store.dispatch).toHaveBeenCalledWith("getWikisReferencingSameWikiPage", item);
            expect(deletion_modal.contains("[data-test=checkbox]")).toBeTruthy();
        });

        it(`When there is a problem retrieving the wiki page referencers (either not found or either unreadable), then it should not add a checkbox`, async () => {
            store.dispatch
                .withArgs("getWikisReferencingSameWikiPage", item)
                .and.returnValue(USER_CANNOT_PROPAGATE_DELETION_TO_WIKI_SERVICE);

            const deletion_modal = await getDeletionModal(item);

            expect(store.dispatch).toHaveBeenCalledWith("getWikisReferencingSameWikiPage", item);
            expect(deletion_modal.contains("[data-test=checkbox]")).toBeFalsy();
        });

        it(`when it does not reference an existing wiki page, then it should not add a checkbox`, () => {
            item.wiki_properties.page_id = null;

            const deletion_modal = getDeletionModal(item);

            expect(store.dispatch).not.toHaveBeenCalled();
            expect(deletion_modal.contains("[data-test=checkbox]")).toBeFalsy();
        });
    });

    it("When the item is a folder, then it should display a special warning and the checkbox should not be shown", () => {
        const item = {
            id: 42,
            title: "my folder",
            type: "folder"
        };

        store.getters.is_item_a_folder = () => true;

        const deletion_modal = getDeletionModal(item);

        expect(store.dispatch).not.toHaveBeenCalled();
        expect(deletion_modal.contains("[data-test=delete-folder-warning]")).toBeTruthy();
        expect(deletion_modal.contains("[data-test=checkbox]")).toBeFalsy();
    });
});
