<?php
/**
 * Copyright (c) Enalean 2022 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\REST\Artifact\ChangesetValue\ArtifactLink;

use Tuleap\Tracker\Artifact\ChangesetValue\ArtifactLink\CollectionOfReverseLinks;

final class AllLinkPayloadParser
{
    private const DIRECTION_KEY     = 'direction';
    private const REVERSE_DIRECTION = 'reverse';

    /**
     * @param list<array{direction: string}> $all_links
     * @throws \Tracker_FormElement_InvalidFieldValueException
     */
    public static function buildLinksToUpdate(array $all_links): CollectionOfReverseLinks
    {
        $reverse_links = [];
        foreach ($all_links as $link) {
            if (isset($link[self::DIRECTION_KEY]) && $link[self::DIRECTION_KEY] === self::REVERSE_DIRECTION) {
                $reverse_links[] = RESTReverseLinkProxy::fromPayload($link);
            }
        }

        return new CollectionOfReverseLinks($reverse_links);
    }
}
