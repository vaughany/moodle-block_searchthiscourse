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

    echo $OUTPUT->box_start('generalbox');
    echo "<p>Found the following $title:</p>\n<ol>\n";
    foreach ($res as $item) {
        echo "<li>$item</li>\n";
    }
    echo "</ol>\n".$OUTPUT->box_end();
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
    $res = $DB->get_records_select('forum', "course = '$cid' AND intro LIKE '%$search%'", array('id, intro'));
    $ret = array();
    foreach ($res as $row) {
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
        $ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->id)), $row->name);

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

    $sql = "SELECT ".$CFG->prefix."forum_posts.id, ".$CFG->prefix."forum_posts.discussion, subject
            FROM ".$CFG->prefix."forum_posts, ".$CFG->prefix."forum_discussions
            WHERE ".$CFG->prefix."forum_posts.discussion = ".$CFG->prefix."forum_discussions.id
            AND ".$CFG->prefix."forum_discussions.course = '$cid'
            AND (".$CFG->prefix."forum_posts.subject LIKE '%$search%' OR ".$CFG->prefix."forum_posts.message LIKE '%$search%');";
            //AND ".$CFG->prefix."forum_posts.message LIKE '%$search%';";
    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        $ret[] = '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$row->discussion.'#p'.$row->id.'">'.$row->subject."</a>\n";
        //$ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->discussion, '#p' => $row->id)), $row->subject);
        // tried using html_writer::link here but it can't handle the # on the end.
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
    global $CFG, $DB;
    $res = $DB->get_records_select('label', "course = '$cid' AND name LIKE '%$search%'", array('id, name'));
    $ret = array();
    foreach ($res as $row) {
        //$ret[] = html_writer::link(new moodle_url('/mod/forum/view.php', array('f' => $row->id)), $row->name);
        $ret[] = "Yes, it's on a label on this course. Somewhere.";
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
