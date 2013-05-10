<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * SearchThisCourse block main code.
 *
 * SearchThisCourse searches through all of a course's resources for
 * specific keywords.
 *
 * @package    block
 * @subpackage searchthiscourse
 * @copyright  2012 Paul Vaughan, paulvaughan@southdevon.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_searchthiscourse extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_searchthiscourse');
    }

    public function get_content() {

        global $CFG, $OUTPUT;

        include_once('lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content->text   = '';
            return $this->content;
        }

        $this->content->text  = '<p>'.get_string('enterkeyword', 'block_searchthiscourse').'</p>';
        $this->content->text .= '<div class="searchform">';
        $this->content->text .= '  <form action="'.$CFG->wwwroot.'/blocks/searchthiscourse/search.php" style="display:inline">';
        $this->content->text .= '    <fieldset class="invisiblefieldset">';
        $this->content->text .= '      <input name="id" type="hidden" value="'.$this->page->course->id.'" />';
        $this->content->text .= '      <label class="accesshide" for="searchform_search">'.get_string('search').'</label>';
        $this->content->text .= '      <input id="searchform_search" name="search" type="text" size="16" />';
        $this->content->text .= '      <p>'.get_string('min3chars', 'block_searchthiscourse').'</p>';
        $this->content->text .= '      <button id="searchform_button" type="submit" title="'.get_string('search').'">'.
            get_string('go').'</button><br />';
        $this->content->text .= '    </fieldset>';
        $this->content->text .= '  </form>';
        $this->content->text .= '</div>';

        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'site' => true,
            'course' => true,
        );
    }
}
