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
 * SearchThisCourse library code.
 *
 * Chunks of code used from local/codechecker and mod/forum.
 *
 * @package    block
 * @subpackage searchthiscourse
 * @copyright  2012 Paul Vaughan, paulvaughan@southdevon.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('HIDDEN', 'class="dimmed_text" ' );

/**
 * This function takes each word out of the search string, makes sure they are at least
 * two characters long and returns an array containing every good word.
 *
 * @param string $words     String containing space-separated strings to search for
 * @param int $len          Int defining minimum length of search word
 * @param string $prefix    String to prepend to the each token taken out of $words
 * @returns array
 */
//function clean_search_terms($words, $len=2, $prefix='') {
function clean_search_terms($words, $len=2) {
    $searchterms = explode(' ', $words);
    foreach ($searchterms as $key => $searchterm) {
        if (strlen($searchterm) < $len) {
            unset($searchterms[$key]);
//        } else if ($prefix) {
//            $searchterms[$key] = $prefix.$searchterm;
        }
    }
    return trim(implode(' ', $searchterms));
}


/*
 * Regular use function for displaying the results of searches in a nice way.
 * @param object $res       Database result object.
 * @param string $title     Text snippet of the searched area.
 */
function display_result_links($res, $title) {
    global $OUTPUT;

    $listtype = (count($res) > 1) ? 'ol' : 'ul';

    echo $OUTPUT->box_start('generalbox');
    echo "<p>Found the following $title:</p>\n<$listtype>\n";
    foreach ($res as $item) {
        echo "<li>$item</li>\n";
    }
    echo "</$listtype>\n".$OUTPUT->box_end();
}


/**
 * Check the plugin is visible.
 * @param string $name      Name of block or module (will take either).
 * @return true or false
 */
function check_plugin_visible($name) {
    global $DB;
    $module = $DB->get_record('modules', array('name' => $name), 'id, visible');
    if ($module) {
        return ($module->visible) ? true : false;
    } else {
        $block = $DB->get_record('block', array('name' => $name), 'id, visible');
        if ($block) {
            return ($block->visible) ? true : false;
        } else {
            return false;
        }
    }
}


/*
 * Regular use function for displaying the lack of search results.
 * @param string $title     Text snippet of the searched area.
 */
function display_no_result($title) {
    echo "<p>Did not find the search term in $title.</p>\n";
}


/*
 * Search forum titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_forum_titles($search, $cid) {
    global $CFG, $DB;

    // Forums cannot be hidden globally, so little point checking!
    //if (!check_plugin_visible('forum')) {
    //    return false;
    //}

    $res = $DB->get_records_select('forum', "course = '$cid' AND intro LIKE '%$search%'", array('id, intro'));
    $ret = array();
    foreach ($res as $row) {
        // TODO: not checked for instance visibility
        $ret[] = html_writer::link(new moodle_url('/mod/forum/view.php', array('f' => $row->id)), $row->intro);
    }
    return $ret;
}

/*
 * Search forum discussions for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_forum_discussions($search, $cid) {
    global $CFG, $DB;

    $res = $DB->get_records_select('forum_discussions', "course = '$cid' AND name LIKE '%$search%'", array('id, name'));

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('forum', $row)) {
            $ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->id)), $row->name);
        } else {
            $ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->id)), $row->name, array('class' => 'dimmed_text'));
        }

    }
    return $ret;
}

/*
 * Search forum posts for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_forum_posts($search, $cid) {
    global $CFG, $DB;

    $sql = "SELECT ".$CFG->prefix."forum_posts.id AS pid, ".$CFG->prefix."forum.id,
                ".$CFG->prefix."forum_posts.discussion, subject,
                ".$CFG->prefix."forum_discussions.course
            FROM ".$CFG->prefix."forum_posts, ".$CFG->prefix."forum_discussions, ".$CFG->prefix."forum
            WHERE ".$CFG->prefix."forum_posts.discussion = ".$CFG->prefix."forum_discussions.id
            AND ".$CFG->prefix."forum_discussions.forum = ".$CFG->prefix."forum.id
            AND ".$CFG->prefix."forum_discussions.course = '$cid'
            AND (".$CFG->prefix."forum_posts.subject LIKE '%$search%' OR ".$CFG->prefix."forum_posts.message LIKE '%$search%');";
    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        if (instance_is_visible('forum', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$row->discussion.'#p'.$row->pid.'">'.$row->subject."</a>\n";
        } else {
            $ret[] = '<a '.HIDDEN.'href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$row->discussion.'#p'.$row->pid.'">'.$row->subject."</a>\n";
            //$ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->discussion, '#p' => $row->id)), $row->subject);
            // tried using html_writer::link here but it can't handle the # on the end.
        }

    }
    return $ret;
}



/*
 * Search forum titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_glossary_titles($search, $cid) {
    global $CFG, $DB;

    if (!check_plugin_visible('glossary')) {
        return false;
    }

    $res = $DB->get_records_select('glossary', "course = '$cid' AND name LIKE '%$search%' OR intro LIKE '%$search%'", array('id, name'));
    $ret = array();
    foreach ($res as $row) {
        // TODO: not checked for instance visibility
        $ret[] = html_writer::link(new moodle_url('/mod/glossary/view.php', array('id' => $row->id)), $row->name);

    }
    return $ret;
}



/*
 * Search labels for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_labels($search, $cid) {
    global $CFG, $DB, $COURSE;

    if (!check_plugin_visible('label')) {
        return false;
    }

    //$res = $DB->get_records_select('label', "course = '$cid' AND name LIKE '%$search%'", array('id, name'));

    $sql = "SELECT ".$CFG->prefix."label.id, ".$CFG->prefix."label.name, ".$CFG->prefix."course_modules.section, ".$CFG->prefix."course_modules.course
            FROM ".$CFG->prefix."label, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."label.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."modules.name = 'label'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."label.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        // TODO: sections appear to be wrong.

        // Check each instance's visibility. Use only if visible.
        // Or, have results returned for teachers showing hidden elements, much like the course proper.
        if (instance_is_visible('label', $row)) {
            $ret[] = 'Search term found in a label in <a href="'.$CFG->wwwroot.'/course/view.php?id='.$cid.'#section-'.$row->section.'">section '.$row->section."</a>\n";
        } else {
            $ret[] = '<a '.HIDDEN.'href="'.$CFG->wwwroot.'/course/view.php?id='.$cid.'#section-'.$row->section.'">Search term found in a <em>hidden</em> label in section '.$row->section."</a>\n";
        }

    }
    return $ret;
}




require_once($CFG->libdir . '/formslib.php');
/**
 * Settings form for the code checker.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_searchthiscourse_form extends moodleform {

    protected function definition() {
        //global $path;
        $mform = $this->_form;

        $a = new stdClass();
        //$a->link = html_writer::link('http://docs.moodle.org/en/Development:Coding_style',
        //        get_string('moodlecodingguidelines', 'local_codechecker'));
        //$a->path = html_writer::tag('tt', 'local/codechecker');
        $mform->addElement('static', '', '', get_string('info', 'block_searchthiscourse', $a));

        $mform->addElement('text', 'path', get_string('path', 'block_searchthiscourse'));

        $mform->addElement('submit', 'submitbutton', get_string('pluginname', 'block_searchthiscourse').'!');
    }
}
