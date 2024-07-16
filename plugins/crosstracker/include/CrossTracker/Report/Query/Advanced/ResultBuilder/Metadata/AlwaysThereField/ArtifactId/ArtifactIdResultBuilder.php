<?php
/**
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

declare(strict_types=1);

namespace Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilder\Metadata\AlwaysThereField\ArtifactId;

use LogicException;
use Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilder\Representations\NumericResultRepresentation;
use Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilder\SelectedValue;
use Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilder\SelectedValuesCollection;
use Tuleap\CrossTracker\REST\v1\Representation\CrossTrackerSelectedRepresentation;
use Tuleap\CrossTracker\REST\v1\Representation\CrossTrackerSelectedType;

final class ArtifactIdResultBuilder
{
    public function getResult(array $select_results): SelectedValuesCollection
    {
        $values = [];
        $alias  = '@id';

        foreach ($select_results as $result) {
            $id = $result['id'];
            if (isset($values[$id])) {
                continue;
            }

            $value = $result[$alias];
            if (! is_int($value) || $value !== $id) {
                throw new LogicException('DB query should have same artifact id for columns id and @id');
            }
            $values[$id] = new SelectedValue('@id', new NumericResultRepresentation($value));
        }

        return new SelectedValuesCollection(
            new CrossTrackerSelectedRepresentation('@id', CrossTrackerSelectedType::TYPE_NUMERIC),
            $values,
        );
    }
}
