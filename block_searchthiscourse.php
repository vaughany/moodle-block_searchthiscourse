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

        function init() {
        $this->title = get_string('pluginname', 'block_searchthiscourse');
    }

    function get_content() {

        global $CFG, $OUTPUT;

        include_once('lib.php');

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content->text   = '';
            return $this->content;
        }

        $advancedsearch = get_string('advancedsearch', 'block_searchthiscourse');

        $strsearch  = get_string('search');
        $strgo      = get_string('go');

        $this->content->text  = 'Enter keyword/s:';
        $this->content->text .= '<div class="searchform">';
        $this->content->text .= '<form action="'.$CFG->wwwroot.'/blocks/searchthiscourse/search.php" style="display:inline"><fieldset class="invisiblefieldset">';
        $this->content->text .= '<input name="id" type="hidden" value="'.$this->page->course->id.'" />';  // course
        $this->content->text .= '<label class="accesshide" for="searchform_search">'.$strsearch.'</label>'.
                                '<input id="searchform_search" name="search" type="text" size="16" />';
        $this->content->text .= '<button id="searchform_button" type="submit" title="'.$strsearch.'">'.$strgo.'</button><br />';
        //$this->content->text .= '<a href="'.$CFG->wwwroot.'/blocks/searchthiscourse/search.php?id='.$this->page->course->id.'">'.$advancedsearch.'</a>';
        //$this->content->text .= $OUTPUT->help_icon('search');
        $this->content->text .= '</fieldset></form></div>';

/*
        $mform = new block_searchthiscourse_form(new moodle_url('/blocks/searchthiscourse/'));
        //$mform->set_data((object) array('path' => $path));
        if ($data = $mform->get_data()) {
            //redirect(new moodle_url('/blocks/searchthiscourse/', array('path' => $data->path)));
            redirect(new moodle_url('/blocks/searchthiscourse/'));
        }
        //$this->content->text .= $mform;
        $this->content->text .= $mform->display();
*/

        return $this->content;
    }

    function applicable_formats() {
        return array(
            'site' => true,
            'course' => true,
        );
    }
}


