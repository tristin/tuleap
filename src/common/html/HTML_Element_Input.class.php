<?php
/*
 * Copyright (c) Xerox, 2008. All Rights Reserved.
 *
 * Originally written by Nicolas Terray, 2008. Xerox Codex Team.
 *
 * This file is a part of CodeX.
 *
 * CodeX is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * CodeX is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CodeX; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('HTML_Element.class.php');

/**
 * Define a generic html input field
 */
abstract class HTML_Element_Input extends HTML_Element {
    protected function renderValue() {
        $hp = Codendi_HTMLPurifier::instance();
        $html = '<input type="'. $this->getInputType() .'" 
                         id="'. $this->id .'" 
                         name="'.  $hp->purify($this->name, CODENDI_PURIFIER_CONVERT_HTML) .'" 
                         value="'.  parent::renderValue() .'" ';
        foreach($this->params as $key => $value) {
            $html .= $key .'="'. $value .'" ';
        }
        $html .= ' />';
        return $html;
    }
    
    abstract protected function getInputType();
}

?>
