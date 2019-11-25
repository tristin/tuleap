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

import { shallowMount, Slots, Wrapper } from "@vue/test-utils";
import BaseCard from "./BaseCard.vue";
import { createStoreMock } from "../../../../../../../../../../src/www/scripts/vue-components/store-wrapper-jest";
import { Card, Event, User } from "../../../../../type";
import EventBus from "../../../../../helpers/event-bus";

jest.useFakeTimers();

function getWrapper(
    card: Card,
    slots: Slots = {},
    user_has_accessibility_mode = false
): Wrapper<BaseCard> {
    return shallowMount(BaseCard, {
        mocks: {
            $store: createStoreMock({
                state: {
                    user: { user_has_accessibility_mode },
                    swimlane: {}
                }
            })
        },
        propsData: { card },
        slots
    });
}

function getCard(
    definition: Card = {
        background_color: "",
        has_been_dropped: false,
        is_in_edit_mode: false
    } as Card
): Card {
    return {
        id: 43,
        color: "lake-placid-blue",
        assignees: [] as User[],
        ...definition
    } as Card;
}

describe("BaseCard", () => {
    it("doesn't add a dummy taskboard-card-background- class if the card has no background color", () => {
        const wrapper = getWrapper(getCard());

        expect(wrapper.classes()).not.toContain("taskboard-card-background-");
    });

    it("adds accessibility class if user needs it and card has a background color", () => {
        const wrapper = getWrapper(getCard({ background_color: "fiesta-red" } as Card), {}, true);

        expect(wrapper.contains(".taskboard-card-accessibility")).toBe(true);
        expect(wrapper.classes()).toContain("taskboard-card-with-accessibility");
    });

    it("does not add accessibility class if user needs it but card has no background color", () => {
        const wrapper = getWrapper(getCard(), {}, true);

        expect(wrapper.contains(".taskboard-card-accessibility")).toBe(false);
        expect(wrapper.classes()).not.toContain("taskboard-card-with-accessibility");
    });

    it("adds the show classes", () => {
        const wrapper = getWrapper(getCard());

        expect(wrapper.classes()).toContain("taskboard-card-show");
    });

    it("does not add the show classes if card has been dropped", () => {
        const wrapper = getWrapper(getCard({ has_been_dropped: true } as Card));

        expect(wrapper.classes()).not.toContain("taskboard-card-show");
    });

    it("removes the show classes after 500ms", () => {
        const wrapper = getWrapper(getCard());

        jest.runAllTimers();
        expect(setTimeout).toHaveBeenCalledWith(expect.any(Function), 500);
        expect(wrapper.classes()).not.toContain("taskboard-card-show");
    });

    it("includes the remaining effort slot", () => {
        const wrapper = getWrapper(getCard(), {
            remaining_effort: '<div class="my-remaining-effort"></div>'
        });

        expect(wrapper.contains(".taskboard-card > .my-remaining-effort")).toBe(true);
    });

    it("includes the initial effort slot", () => {
        const wrapper = getWrapper(getCard(), {
            initial_effort: '<div class="my-initial-effort"></div>'
        });

        expect(
            wrapper.contains(".taskboard-card-content > .taskboard-card-info > .my-initial-effort")
        ).toBe(true);
    });

    describe("edit mode", () => {
        beforeEach(() => {
            jest.clearAllMocks();
        });

        it("Given the card is in read mode, then it doesn't add additional class", () => {
            const wrapper = getWrapper(getCard({ is_in_edit_mode: false } as Card));

            expect(wrapper.classes("taskboard-card-edit-mode")).toBe(false);
        });

        it("Given the card is in edit mode, then it adds necessary class", () => {
            const wrapper = getWrapper(getCard({ is_in_edit_mode: true } as Card));

            expect(wrapper.classes("taskboard-card-edit-mode")).toBe(true);
        });

        it("Given the card is in read mode, when user clicks on it, then it toggles its edit mode", () => {
            const card = getCard({ is_in_edit_mode: false } as Card);
            const wrapper = getWrapper(card);

            wrapper.trigger("click");
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(
                "swimlane/addCardToEditMode",
                card
            );
        });

        it("Given the card is in edit mode, when user clicks on it, then it does nothing", () => {
            const wrapper = getWrapper(getCard({ is_in_edit_mode: true } as Card));

            wrapper.trigger("click");
            expect(wrapper.vm.$store.commit).not.toHaveBeenCalled();
        });

        it(`Cancels the edition of the card if user clicks on cancel button (that is outside of this component)`, () => {
            const card = getCard({ is_in_edit_mode: true } as Card);
            const wrapper = getWrapper(card);

            EventBus.$emit(Event.CANCEL_CARD_EDITION, card);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(
                "swimlane/removeCardFromEditMode",
                card
            );
        });

        it(`Cancels also the edition of the card if user clicks on save button (that is outside of this component),
            because edition of title is not implemented yet`, () => {
            const card = getCard({ is_in_edit_mode: true } as Card);
            const wrapper = getWrapper(card);

            EventBus.$emit(Event.SAVE_CARD_EDITION, card);
            expect(wrapper.vm.$store.commit).toHaveBeenCalledWith(
                "swimlane/removeCardFromEditMode",
                card
            );
        });
    });
});
