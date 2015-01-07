<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
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
require_once TRACKER_BASE_DIR . '/../tests/bootstrap.php';

class Tracker_XMLExporter_ChangesetValue_ChangesetValuePermissionsOnArtifactXMLExporterTest extends TuleapTestCase {

    /** @var Tracker_XMLExporter_ChangesetValue_ChangesetValuePermissionsOnArtifactXMLExporter */
    private $exporter;

    /** @var SimpleXMLElement */
    private $changeset_xml;

    /** @var SimpleXMLElement */
    private $artifact_xml;

    /** @var Tracker_Artifact_ChangesetValue_PermissionsOnArtifact */
    private $changeset_value;

    /** @var Tracker_FormElement_Field */
    private $field;

    public function setUp() {
        parent::setUp();
        $this->field         = aFileField()->withName('story_points')->build();
        $this->exporter      = new Tracker_XMLExporter_ChangesetValue_ChangesetValuePermissionsOnArtifactXMLExporter();
        $this->artifact_xml  = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><artifact />');
        $this->changeset_xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><changeset />');

        $this->changeset_value = mock('Tracker_Artifact_ChangesetValue_PermissionsOnArtifact');
        stub($this->changeset_value)->getPerms()->returns(array(1001, 1002));
        stub($this->changeset_value)->getUsed()->returns(true);
        stub($this->changeset_value)->getField()->returns($this->field);
    }

    public function itCreatesFieldChangeNodeInChangesetNode() {
        $this->exporter->export(
            $this->artifact_xml,
            $this->changeset_xml,
            mock('Tracker_Artifact'),
            $this->changeset_value
        );

        $field_change = $this->changeset_xml->field_change;
        $this->assertEqual((string)$field_change['type'], 'permissions_on_artifact');
        $this->assertEqual((string)$field_change['field_name'], $this->field->getName());
        $this->assertEqual((string)$field_change['use_perm'], '1');
        $this->assertEqual((string)$field_change->ugroup[0]['ugroup_id'], 1001);
        $this->assertEqual((string)$field_change->ugroup[1]['ugroup_id'], 1002);
    }
}