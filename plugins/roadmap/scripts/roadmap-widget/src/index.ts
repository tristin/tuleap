/**
 * Copyright (c) Enalean, 2021 - present. All Rights Reserved.
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

import Vue from "vue";
import Vuex from "vuex";
import App from "./components/App.vue";
import {
    getPOFileFromLocale,
    initVueGettext,
} from "../../../../../src/scripts/tuleap/gettext/vue-gettext-init";
import { parseNatureLabels } from "./helpers/nature-labels-from-mountpoint";
import { createStore } from "./store";
import type { RootState } from "./store/type";
import { toBCP47 } from "./helpers/locale-for-intl";

document.addEventListener("DOMContentLoaded", async () => {
    const all_vue_mount_points = document.querySelectorAll(".roadmap");
    if (all_vue_mount_points.length === 0) {
        return;
    }

    await initVueGettext(
        Vue,
        (locale: string) =>
            import(
                /* webpackChunkName: "roadmap-widget-po-" */ "../po/" + getPOFileFromLocale(locale)
            )
    );
    Vue.use(Vuex);

    const AppComponent = Vue.extend(App);

    for (const vue_mount_point of all_vue_mount_points) {
        if (!(vue_mount_point instanceof HTMLElement)) {
            continue;
        }

        const roadmap_id = vue_mount_point.dataset.roadmapId;
        if (!roadmap_id) {
            continue;
        }

        const visible_natures = await parseNatureLabels(vue_mount_point, {
            $gettext: Vue.prototype.$gettext,
            $gettextInterpolate: Vue.prototype.$gettextInterpolate,
        });

        // eslint-disable-next-line @typescript-eslint/consistent-type-assertions
        const initial_root_state: RootState = {
            locale_bcp47: toBCP47(document.body.dataset.userLocale || "en_US"),
            should_load_lvl1_iterations: Boolean(vue_mount_point.dataset.shouldLoadLvl1Iterations),
            should_load_lvl2_iterations: Boolean(vue_mount_point.dataset.shouldLoadLvl2Iterations),
        } as RootState;

        new AppComponent({
            store: createStore(initial_root_state),
            propsData: {
                roadmap_id,
                visible_natures,
            },
        }).$mount(vue_mount_point);
    }
});
