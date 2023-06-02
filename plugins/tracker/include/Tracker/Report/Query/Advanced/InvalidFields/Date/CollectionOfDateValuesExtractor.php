<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

namespace Tuleap\Tracker\Report\Query\Advanced\InvalidFields\Date;

use Tracker_FormElement_Field;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\BetweenValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\CurrentDateTimeValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\CurrentUserValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\FieldValueWrapperParameters;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\InValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\SimpleValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\StatusOpenValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\ValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\ValueWrapperVisitor;
use Tuleap\Tracker\Report\Query\Advanced\InvalidFields\InIsNotSupportedException;
use Tuleap\Tracker\Report\Query\Advanced\InvalidFields\MySelfIsNotSupportedException;
use Tuleap\Tracker\Report\Query\Advanced\InvalidFields\StatusOpenIsNotSupportedException;

/**
 * @template-implements ValueWrapperVisitor<FieldValueWrapperParameters, string | int | float | array<string | int | float>>
 */
class CollectionOfDateValuesExtractor implements ValueWrapperVisitor
{
    public function __construct(private readonly string $date_format)
    {
    }

    /** @return array<string | int | float> */
    public function extractCollectionOfValues(ValueWrapper $value_wrapper, Tracker_FormElement_Field $field): array
    {
        try {
            return (array) $value_wrapper->accept($this, new FieldValueWrapperParameters($field));
        } catch (MySelfIsNotSupportedException $exception) {
            throw new DateToMySelfComparisonException($field);
        } catch (StatusOpenIsNotSupportedException $exception) {
            throw new DateToStatusOpenComparisonException($field);
        }
    }

    public function visitCurrentDateTimeValueWrapper(CurrentDateTimeValueWrapper $value_wrapper, $parameters)
    {
        $current_date_time = $value_wrapper->getValue();

        return $current_date_time->format($this->date_format);
    }

    public function visitSimpleValueWrapper(SimpleValueWrapper $value_wrapper, $parameters)
    {
        return $value_wrapper->getValue();
    }

    public function visitBetweenValueWrapper(BetweenValueWrapper $value_wrapper, $parameters)
    {
        $values = [];

        $min = $value_wrapper->getMinValue()->accept($this, $parameters);
        if (is_array($min)) {
            throw new \Exception("Unsupported between value");
        }
        $values[] = $min;

        $max = $value_wrapper->getMaxValue()->accept($this, $parameters);
        if (is_array($max)) {
            throw new \Exception("Unsupported between value");
        }
        $values[] = $max;

        return $values;
    }

    public function visitInValueWrapper(InValueWrapper $collection_of_value_wrappers, $parameters)
    {
        throw new InIsNotSupportedException();
    }

    public function visitCurrentUserValueWrapper(CurrentUserValueWrapper $value_wrapper, $parameters)
    {
        throw new MySelfIsNotSupportedException();
    }

    public function visitStatusOpenValueWrapper(StatusOpenValueWrapper $value_wrapper, $parameters)
    {
        throw new StatusOpenIsNotSupportedException();
    }
}
