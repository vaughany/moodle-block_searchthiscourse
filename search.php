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
 * Code borrowed from /mod/forum/search.php
 *
 * @package    block
 * @subpackage searchthiscourse
 * @copyright  2012 Paul Vaughan, paulvaughan@southdevon.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id     = required_param('id', PARAM_INT);                          // course id
$search = trim(required_param('search', PARAM_NOTAGS));             // search string

$PAGE->set_pagelayout('standard');
$PAGE->set_url($FULLME);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course);

add_to_log($course->id, 'searchthiscourse', 'search', 'search.php?id='.$course->id.'&amp;search='.urlencode($search), $search);

$search = clean_search_terms($search);



// lots of strings we prolly don't need
//$strforums = get_string("modulenameplural", "forum");
$strsearch = get_string('search', 'forum');
$strsearchresults = get_string("searchresults", "forum");
//$strpage = get_string("page");

//$searchterms = str_replace('forumid:', 'instance:', $search);
//$searchterms = explode(' ', $searchterms);

//$searchform = forum_search_form($course, $search);

// nav
$PAGE->navbar->add($strsearch, new moodle_url('/blocks/searchthiscourse/search.php', array('id' => $course->id)));
$PAGE->navbar->add(s($search, true));

$PAGE->set_title($strsearchresults);
$PAGE->set_heading($course->fullname);


// here goes
$res = search_forum_posts($search, $course->id);
if ($res) {

    print_r($res);

    //echo $OUTPUT->box_start('generalbox', 'stc_forumposts');
    //echo $build;
    //echo $OUTPUT->box_end();

}


























echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nopostscontaining', 'forum', $search));

echo $OUTPUT->footer();
