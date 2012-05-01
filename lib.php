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
 *
 */
function search_forum_posts($search, $cid) {
    global $CFG, $DB;
    //$res = $DB->get_records_select(
    //        'forum_posts',
    //        //"course_id = $cid AND deleted = '0' AND (subject LIKE '%$search%' OR message LIKE '%$search%')",
    //        "subject LIKE '%$search%' OR message LIKE '%$search%'",
    //        array('id ASC', 'id, vote')
    //);

    $res = $DB->get_records_sql("
            SELECT d.id, p.id
            FROM mdl_forum_posts p, mdl_forum_discussions d
            WHERE p.discussion = d.id
            AND d.course = '$cid'
            AND (p.subject LIKE '%$search%' OR p.message LIKE '%$search%');");
    $link = array();
    foreach ($res as $row) {
        $link[] = '/mod/forum/discuss.php?d='.$row['d.id'].'#p'.$row['p.id'].'';
    }

    return $res;
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
