/*
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

import { shallowMount } from "@vue/test-utils";
import WidgetModalContent from "./WidgetModalContent.vue";
import localVue from "../../helpers/local-vue.js";
import { createTestingPinia } from "@pinia/testing";
import { defineStore } from "pinia";

describe("Given a personal timetracking widget modal", () => {
    let rest_feedback;
    let is_add_mode;
    let current_artifact;
    let setAddMode = jest.fn();

    function getWidgetModalContentInstance() {
        const useStore = defineStore("root", {
            state: () => ({
                rest_feedback: rest_feedback,
                is_add_mode: is_add_mode,
            }),
            getters: {
                current_artifact: () => current_artifact,
            },
            actions: {
                setAddMode: setAddMode,
            },
        });
        const pinia = createTestingPinia({ stubActions: false });
        useStore(pinia);

        const component_options = {
            localVue,
            pinia,
        };
        return shallowMount(WidgetModalContent, component_options);
    }

    beforeEach(() => {
        rest_feedback = "";
        is_add_mode = false;
        current_artifact = { artifact: "artifact" };
    });

    it("When there is no REST feedback, then feedback message should not be displayed", () => {
        const wrapper = getWidgetModalContentInstance();
        expect(wrapper.find("[data-test=feedback]").exists()).toBeFalsy();
    });

    it("When there is REST feedback, then feedback message should be displayed", () => {
        rest_feedback = { type: "success" };
        const wrapper = getWidgetModalContentInstance();
        expect(wrapper.find("[data-test=feedback]").exists()).toBeTruthy();
    });

    it("When add mode button is triggered, then setAddMode should be called", () => {
        const wrapper = getWidgetModalContentInstance();
        wrapper.get("[data-test=button-set-add-mode]").trigger("click");
        expect(setAddMode).toHaveBeenCalledWith(true);
    });
});
