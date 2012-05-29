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

/*
Moodle activities in decending order of use
File            42544
URL             8599    :)
Label           6990    :)
Page            2192    :)
Assignment      2009
Folder          1323
SCORM package   958
Forum           932     :)
Feedback        641
Quiz            579
IMS content package 419
Book            194     :)
Choice          182
Slideshow       153
HotPot          90
Glossary        86      :)
Scheduler       57
Wiki            36
Lesson          35
Chat            18
OU wiki         15
Certificate     14
Database        12
Workshop        4
OU blog         3
Journal         1
Survey          1
External Tool   0
*/

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id     = required_param('id', PARAM_INT);                          // course id
$search = trim(required_param('search', PARAM_NOTAGS));             // search string

if (empty($search)) {
    redirect(new moodle_url('/course/view.php', array('id' => $id)));
}

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
//$strsearch = get_string('search', 'forum');
$strsearchresults = get_string("searchresults", "forum");
//$strpage = get_string("page");

//$searchterms = str_replace('forumid:', 'instance:', $search);
//$searchterms = explode(' ', $searchterms);

//$searchform = forum_search_form($course, $search);

// nav
$PAGE->navbar->add(get_string('pluginname', 'block_searchthiscourse'), new moodle_url('/blocks/searchthiscourse/search.php', array('id' => $course->id)));
$PAGE->navbar->add(s($search, true));

$PAGE->set_title($strsearchresults);
$PAGE->set_heading($course->fullname);


echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('nopostscontaining', 'forum', $search));
echo $OUTPUT->heading('SearchThisCourse Results');
echo 'Note that if any results are found in hidden resouces, they will <span class="dimmed_text">appear greyed out</span>, and are only visible to those users with Teacher rights or better.';
echo html_writer::tag('hr', null);

// Forums. /////////////////////////////////////////////////////////////////////////////////////////

// Forum titles.
$res = search_forum_titles($search, $course->id);
if ($res) {
    display_result_links($res, 'forum titles', 'forum');
} else {
    display_no_result('forum titles');
}

// Forum discussions.
$res = search_forum_discussions($search, $course->id);
if ($res) {
    display_result_links($res, 'forum discussions', 'forum');
} else {
    display_no_result('forum discussions');
}

// Forum posts.
$res = search_forum_posts($search, $course->id);
if ($res) {
    display_result_links($res, 'forum posts', 'forum');
} else {
    display_no_result('forum posts');
}

// Glossaries //////////////////////////////////////////////////////////////////////////////////////

// Glossary titles.
$res = search_glossary_titles($search, $course->id);
if ($res) {
    display_result_links($res, 'glossaries', 'glossary');
} else {
    display_no_result('glossaries');
}

// Glossary entries.
$res = search_glossary_entries($search, $course->id);
if ($res) {
    display_result_links($res, 'glossary entries', 'glossary');
} else {
    display_no_result('glossary entries');
}

// Labels //////////////////////////////////////////////////////////////////////////////////////////

// Labels.
$res = search_labels($search, $course->id);
if ($res) {
    display_result_links($res, 'labels');
} else {
    display_no_result('labels');
}

// Checklists. /////////////////////////////////////////////////////////////////////////////////////

// Checklist titles.
$res = search_checklist_titles($search, $course->id);
if ($res) {
    display_result_links($res, 'checklist titles', 'checklist');
} else {
    display_no_result('checklist titles');
}

// Files. //////////////////////////////////////////////////////////////////////////////////////////

// File names.
/*$res = search_filenames($search, $course->id);
if ($res) {
    display_result_links($res, 'file titles', 'files');
} else {
    display_no_result('file titles');
}*/

// URLs. ///////////////////////////////////////////////////////////////////////////////////////////

// URL titles.
$res = search_url_titles($search, $course->id);
if ($res) {
    display_result_links($res, 'URL titles', 'url');
} else {
    display_no_result('URL titles');
}

// URLs.
$res = search_urls($search, $course->id);
if ($res) {
    display_result_links($res, 'URLs', 'url');
} else {
    display_no_result('URLs');
}

// Pages. //////////////////////////////////////////////////////////////////////////////////////////

// Page titles.
$res = search_page_titles($search, $course->id);
if ($res) {
    display_result_links($res, 'page titles', 'page');
} else {
    display_no_result('page titles');
}

// Page content.
$res = search_page_content($search, $course->id);
if ($res) {
    display_result_links($res, 'page content', 'page');
} else {
    display_no_result('page content');
}

// Book. ///////////////////////////////////////////////////////////////////////////////////////////

// Book titles.
$res = search_book_titles($search, $course->id);
if ($res) {
    display_result_links($res, 'book titles', 'book');
} else {
    display_no_result('book titles');
}

// Book content.
$res = search_book_content($search, $course->id);
if ($res) {
    display_result_links($res, 'book content', 'book');
} else {
    display_no_result('book content');
}

// Assignment. /////////////////////////////////////////////////////////////////////////////////////

// Assignment titles.
$res = search_assignment_titles($search, $course->id);
if ($res) {
    display_result_links($res, 'assignment titles', 'assignment');
} else {
    display_no_result('assignment titles');
}

// Assignment content.
$res = search_assignment_submission($search, $course->id);
if ($res) {
    display_result_links($res, 'assignment content', 'assignment');
} else {
    display_no_result('assignment content');
}















//$sections = get_all_sections($id);
//print_object($sections);
//print_object($CFG->theme);

echo $OUTPUT->footer();