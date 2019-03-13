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

const identity = i => i;

const factories = {
    milestone: {
        default: {
            id: identity,
            label: "Milestone label"
        }
    },
    baseline: {
        default: {
            id: identity,
            name: "Baseline label",
            snapshot_date: "10/02/2019",
            author_id: 3
        },
        presented: {
            author: association("user", { id: 3 })
        }
    },
    simplified_baseline: {
        default: {
            id: identity,
            name: "Simplified baseline label",
            milestone_id: 3,
            author_id: 2,
            creation_date: 12344567
        }
    },
    user: {
        default: {
            id: identity,
            username: "John Doe"
        }
    }
};

let instance_index = 1;

function association(factory_name, ...trait_or_attributes) {
    return () => create(factory_name, ...trait_or_attributes);
}

const evaluateAttributesAsFunction = instance =>
    Object.keys(instance).reduce((evaluatedInstance, key) => {
        const attribute_or_function = instance[key];
        if (attribute_or_function && typeof attribute_or_function === "function") {
            evaluatedInstance[key] = attribute_or_function(instance_index++);
        } else {
            evaluatedInstance[key] = attribute_or_function;
        }
        return evaluatedInstance;
    }, {});

function getDefaultAttributes(factory_name) {
    if (!factories.hasOwnProperty(factory_name)) {
        throw new Error(
            `No factory found with name [${factory_name}]. Did you register this new factory?`
        );
    }
    const factory = factories[factory_name];
    if (!factory.hasOwnProperty("default")) {
        throw new Error(`No default trait found for factory [${factory_name}]`);
    }
    return factories[factory_name].default;
}

function getTraitAttributes(factory_name, trait) {
    if (!factories[factory_name].hasOwnProperty(trait)) {
        throw new Error(`No trait [${trait}] found for factory [${factory_name}]`);
    }
    return factories[factory_name][trait];
}

export function create(factory_name, ...trait_or_attributes) {
    const attributes = [getDefaultAttributes(factory_name)];
    trait_or_attributes.forEach((trait_or_attribute, index) => {
        if (index < trait_or_attributes.length - 1 || typeof trait_or_attribute === "string") {
            attributes.push(getTraitAttributes(factory_name, trait_or_attribute));
        } else {
            attributes.push(trait_or_attribute);
        }
    });

    return evaluateAttributesAsFunction(Object.assign({}, ...attributes));
}

export function createList(factory, count, trait_or_attributes) {
    return Array.from(Array(count)).map(() => create(factory, trait_or_attributes));
}
