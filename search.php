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

$id         = required_param('id', PARAM_INT);                          // course id
$search     = trim(required_param('search', PARAM_NOTAGS));             // search string
$can_edit   = has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $COURSE->id));

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
// $strforums = get_string("modulenameplural", "forum");
// $strsearch = get_string('search', 'forum');
$strsearchresults = get_string("searchresults", "forum");
// $strpage = get_string("page");

// $searchterms = str_replace('forumid:', 'instance:', $search);
// $searchterms = explode(' ', $searchterms);

// $searchform = forum_search_form($course, $search);

// nav
$PAGE->navbar->add(get_string('pluginname', 'block_searchthiscourse'), new moodle_url('/blocks/searchthiscourse/search.php', array('id' => $course->id)));
$PAGE->navbar->add(s($search, true));

$PAGE->set_title($strsearchresults);
$PAGE->set_heading($course->fullname);


echo $OUTPUT->header();
// echo $OUTPUT->heading(get_string('nopostscontaining', 'forum', $search));
echo $OUTPUT->heading('SearchThisCourse Results');
if ($can_edit) {
    echo 'Note that if any results are found in hidden resouces, they will <span class="dimmed_text">appear greyed out</span>, and are only visible to those users with Teacher rights or better.';
}
echo html_writer::tag('hr', null);

// Assignment. /////////////////////////////////////////////////////////////////////////////////////

// Assignment titles.
$res = search_assignment_titles($search, $course->id);
if ($res) {
    display_result($res, 'assignment titles', 'assignment');
} else {
    display_no_result('assignment titles', 'assignment');
}

// Assignment content.
// This search goes through submitted work so make it available to teachers or greater only.
if ($can_edit) {
    $res = search_assignment_submission($search, $course->id);
    if ($res) {
        display_result($res, 'assignment content', 'assignment');
    } else {
        display_no_result('assignment content', 'assignment');
    }
}

// Book. ///////////////////////////////////////////////////////////////////////////////////////////

// Book titles.
// For non-core modules (as of 2.2) we check for installation first, then plugin visibility.
if (check_plugin_installed('book')) {
    $res = search_book_titles($search, $course->id);
    if ($res) {
        display_result($res, 'book titles', 'book');
    } else {
        display_no_result('book titles', 'book');
    }

    // Book content.
    $res = search_book_content($search, $course->id);
    if ($res) {
        display_result($res, 'book content', 'book');
    } else {
        display_no_result('book content', 'book');
    }
}

// Chat ////////////////////////////////////////////////////////////////////////////////////////////

// Chat titles.
$res = search_chat_titles($search, $course->id);
if ($res) {
    display_result($res, 'chat titles', 'chat');
} else {
    display_no_result('chat titles', 'chat');
}

// Chat entries
if ($can_edit) {
    $res = search_chat_entries($search, $course->id);
    if ($res) {
        display_result($res, 'chat conversations', 'chat');
    } else {
        display_no_result('chat conversations', 'chat');
    }
}

// Checklists. /////////////////////////////////////////////////////////////////////////////////////

// Checklist titles.
$res = search_checklist_titles($search, $course->id);
if ($res) {
    display_result($res, 'checklist titles', 'checklist');
} else {
    display_no_result('checklist titles', 'checklist');
}

// Choice //////////////////////////////////////////////////////////////////////////////////////////

// Choice titles.
$res = search_choice_titles($search, $course->id);
if ($res) {
    display_result($res, 'choice titles', 'choice');
} else {
    display_no_result('choice titles', 'choice');
}

// Choice options.
$res = search_choice_options($search, $course->id);
if ($res) {
    display_result($res, 'choice options', 'choice');
} else {
    display_no_result('choice options', 'choice');
}

// Course //////////////////////////////////////////////////////////////////////////////////////////

// Course name.
$res = search_course_names($search, $course->id);
if ($res) {
    display_result($res, 'course names', null);
} else {
    display_no_result('course names', null);
}

// Course summary.
$res = search_course_summary($search, $course->id);
if ($res) {
    display_result($res, 'course summary', null);
} else {
    display_no_result('course summary', null);
}

// Course section names.
$res = search_course_section_names($search, $course->id);
if ($res) {
    display_result($res, 'course topic titles', null);
} else {
    display_no_result('course topic titles', null);
}

// Database ////////////////////////////////////////////////////////////////////////////////////////

// Database titles.
$res = search_data_titles($search, $course->id);
if ($res) {
    display_result($res, 'database titles', 'data');
} else {
    display_no_result('database titles', 'data');
}

// Database fields.
if ($can_edit) {
    $res = search_data_fields($search, $course->id);
    if ($res) {
        display_result($res, 'database fields', 'data');
    } else {
        display_no_result('database fields', 'data');
    }
}

// Database content.
$res = search_data_content($search, $course->id);
if ($res) {
    display_result($res, 'database content', 'data');
} else {
    display_no_result('database content', 'data');
}

// Feedback. ///////////////////////////////////////////////////////////////////////////////////////

// Feedback names.
$res = search_feedback_titles($search, $course->id);
if ($res) {
    display_result($res, 'feedback names', 'feedback');
} else {
    display_no_result('feedback names', 'feedback');
}

// Feedback questions.
if ($can_edit) {
    $res = search_feedback_questions($search, $course->id);
    if ($res) {
        display_result($res, 'feedback questions', 'feedback');
    } else {
        display_no_result('feedback questions', 'feedback');
    }
}

// Feedback answers.
if ($can_edit) {
    $res = search_feedback_answers($search, $course->id);
    if ($res) {
        display_result($res, 'feedback answers', 'feedback');
    } else {
        display_no_result('feedback answers', 'feedback');
    }
}

// Files. //////////////////////////////////////////////////////////////////////////////////////////

// File names.
/*$res = search_filenames($search, $course->id);
if ($res) {
    display_result($res, 'file titles', 'files');
} else {
    display_no_result('file titles', 'files');
}*/

// Folder. /////////////////////////////////////////////////////////////////////////////////////////

// Folder names.
$res = search_folder_names($search, $course->id);
if ($res) {
    display_result($res, 'folder names', 'folder');
} else {
    display_no_result('folder names', 'folder');
}

// Forums. /////////////////////////////////////////////////////////////////////////////////////////

// Forum titles.
$res = search_forum_titles($search, $course->id);
if ($res) {
    display_result($res, 'forum titles', 'forum');
} else {
    display_no_result('forum titles', 'forum');
}

// Forum discussions.
$res = search_forum_discussions($search, $course->id);
if ($res) {
    display_result($res, 'forum discussions', 'forum');
} else {
    display_no_result('forum discussions', 'forum');
}

// Forum posts.
$res = search_forum_posts($search, $course->id);
if ($res) {
    display_result($res, 'forum posts', 'forum');
} else {
    display_no_result('forum posts', 'forum');
}

// Glossaries //////////////////////////////////////////////////////////////////////////////////////

// Glossary titles.
$res = search_glossary_titles($search, $course->id);
if ($res) {
    display_result($res, 'glossaries', 'glossary');
} else {
    display_no_result('glossaries', 'glossary');
}

// Glossary entries.
$res = search_glossary_entries($search, $course->id);
if ($res) {
    display_result($res, 'glossary entries', 'glossary');
} else {
    display_no_result('glossary entries', 'glossary');
}

// Labels //////////////////////////////////////////////////////////////////////////////////////////

// Labels.
$res = search_labels($search, $course->id);
if ($res) {
    // Label mod has no icon.
    display_result($res, 'labels');
} else {
    display_no_result('labels');
}

// Lesson //////////////////////////////////////////////////////////////////////////////////////////

// Lesson titles.
$res = search_lesson_titles($search, $course->id);
if ($res) {
    display_result($res, 'lesson titles', 'lesson');
} else {
    display_no_result('lesson titles', 'lesson');
}

// Lesson pages.
$res = search_lesson_pages($search, $course->id);
if ($res) {
    display_result($res, 'lesson pages', 'lesson');
} else {
    display_no_result('lesson pages', 'lesson');
}

// Lesson answers?
/*
$res = search_lesson_answers($search, $course->id);
if ($res) {
    display_result($res, 'lesson answers', 'lesson');
} else {
    display_no_result('lesson answers', 'lesson');
}
*/

// Pages. //////////////////////////////////////////////////////////////////////////////////////////

// Page titles.
$res = search_page_titles($search, $course->id);
if ($res) {
    display_result($res, 'page titles', 'page');
} else {
    display_no_result('page titles', 'page');
}

// Page content.
$res = search_page_content($search, $course->id);
if ($res) {
    display_result($res, 'page content', 'page');
} else {
    display_no_result('page content', 'page');
}

// URLs. ///////////////////////////////////////////////////////////////////////////////////////////

// URL titles.
$res = search_url_titles($search, $course->id);
if ($res) {
    display_result($res, 'URL titles', 'url');
} else {
    display_no_result('URL titles', 'url');
}

// URLs.
$res = search_urls($search, $course->id);
if ($res) {
    display_result($res, 'URLs', 'url');
} else {
    display_no_result('URLs', 'url');
}

// Wiki ////////////////////////////////////////////////////////////////////////////////////////////

// Wiki titles.
$res = search_wiki_titles($search, $course->id);
if ($res) {
    display_result($res, 'wiki titles', 'wiki');
} else {
    display_no_result('wiki titles', 'wiki');
}

// Wiki pages.
$res = search_wiki_pages($search, $course->id);
if ($res) {
    display_result($res, 'wiki pages', 'wiki');
} else {
    display_no_result('wiki pages', 'wiki');
}

// Wiki versions (history).
$res = search_wiki_versions($search, $course->id);
if ($res) {
    display_result($res, 'wiki versions', 'wiki');
} else {
    display_no_result('wiki versions', 'wiki');
}










// $sections = get_all_sections($id);
// print_object($sections);
// print_object($CFG->theme);

echo $OUTPUT->footer();