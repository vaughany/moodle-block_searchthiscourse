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

// Make obvious adjustments to the search terms
$search = clean_search_terms($search);

// Log use of the block.
add_to_log($course->id, 'searchthiscourse', 'search', 'search.php?id='.$course->id.'&amp;search='.urlencode($search), $search);

// Navigation elements.
$PAGE->navbar->add(get_string('pluginname', 'block_searchthiscourse'), new moodle_url('/blocks/searchthiscourse/search.php', array('id' => $course->id)));
$PAGE->navbar->add(s($search, true));

// Nice page things.
$PAGE->set_title(get_string('searchresults', 'block_searchthiscourse'));
$PAGE->set_heading($course->fullname);

// Do some nice output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('searchresults', 'block_searchthiscourse'));
if ($can_edit) {
    echo get_string('strapline', 'block_searchthiscourse');
}
echo html_writer::tag('hr', null);

// Assignment. /////////////////////////////////////////////////////////////////////////////////////

// Assignment titles.
$res = search_assignment_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('assignment_title', 'block_searchthiscourse'), 'assignment');
} else {
    display_no_result(get_string('assignment_title', 'block_searchthiscourse'), 'assignment');
}

// Assignment content.
// This search goes through submitted work so make it available to teachers or greater only.
if ($can_edit) {
    $res = search_assignment_submission($search, $course->id);
    if ($res) {
        display_result($res, get_string('assignment_content', 'block_searchthiscourse'), 'assignment');
    } else {
        display_no_result(get_string('assignment_content', 'block_searchthiscourse'), 'assignment');
    }
}

// Book. ///////////////////////////////////////////////////////////////////////////////////////////

// For non-core modules, we check for installation first, then plugin visibility.
// The Book module is a 3rd party module for 2.0-2.2, but has beem moved into core for 2.3.
if (check_plugin_installed('book')) {
    // Book titles.
    $res = search_book_titles($search, $course->id);
    if ($res) {
        display_result($res, get_string('book_titles', 'block_searchthiscourse'), 'book');
    } else {
        display_no_result(get_string('book_titles', 'block_searchthiscourse'), 'book');
    }

    // Book content.
    $res = search_book_content($search, $course->id);
    if ($res) {
        display_result($res, get_string('book_content', 'block_searchthiscourse'), 'book');
    } else {
        display_no_result(get_string('book_content', 'block_searchthiscourse'), 'book');
    }
}

// Chat ////////////////////////////////////////////////////////////////////////////////////////////

// Chat titles.
$res = search_chat_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('chat_titles', 'block_searchthiscourse'), 'chat');
} else {
    display_no_result(get_string('chat_titles', 'block_searchthiscourse'), 'chat');
}

// Chat entries
if ($can_edit) {
    $res = search_chat_entries($search, $course->id);
    if ($res) {
        display_result($res, get_string('chat_conversations', 'block_searchthiscourse'), 'chat');
    } else {
        display_no_result(get_string('chat_conversations', 'block_searchthiscourse'), 'chat');
    }
}

// Checklists. /////////////////////////////////////////////////////////////////////////////////////

// Checklist titles.
$res = search_checklist_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('checklist_titles', 'block_searchthiscourse'), 'checklist');
} else {
    display_no_result(get_string('checklist_titles', 'block_searchthiscourse'), 'checklist');
}

// Choice //////////////////////////////////////////////////////////////////////////////////////////

// Choice titles.
$res = search_choice_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('choice_titles', 'block_searchthiscourse'), 'choice');
} else {
    display_no_result(get_string('choice_titles', 'block_searchthiscourse'), 'choice');
}

// Choice options.
$res = search_choice_options($search, $course->id);
if ($res) {
    display_result($res, get_string('choice_options', 'block_searchthiscourse'), 'choice');
} else {
    display_no_result(get_string('choice_options', 'block_searchthiscourse'), 'choice');
}

// Course //////////////////////////////////////////////////////////////////////////////////////////

// Course name.
$res = search_course_names($search, $course->id);
if ($res) {
    display_result($res, get_string('course_names', 'block_searchthiscourse'), null);
} else {
    display_no_result(get_string('course_names', 'block_searchthiscourse'), null);
}

// Course summary.
$res = search_course_summary($search, $course->id);
if ($res) {
    display_result($res, get_string('course_summary', 'block_searchthiscourse'), null);
} else {
    display_no_result(get_string('course_summary', 'block_searchthiscourse'), null);
}

// Course section names.
$res = search_course_section_names($search, $course->id);
if ($res) {
    display_result($res, get_string('course_topic_titles', 'block_searchthiscourse'), null);
} else {
    display_no_result(get_string('course_topic_titles', 'block_searchthiscourse'), null);
}

// Database ////////////////////////////////////////////////////////////////////////////////////////

// Database titles.
$res = search_data_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('database_titles', 'block_searchthiscourse'), 'data');
} else {
    display_no_result(get_string('database_titles', 'block_searchthiscourse'), 'data');
}

// Database fields.
if ($can_edit) {
    $res = search_data_fields($search, $course->id);
    if ($res) {
        display_result($res, get_string('database_fields', 'block_searchthiscourse'), 'data');
    } else {
        display_no_result(get_string('database_fields', 'block_searchthiscourse'), 'data');
    }
}

// Database content.
$res = search_data_content($search, $course->id);
if ($res) {
    display_result($res, get_string('database_content', 'block_searchthiscourse'), 'data');
} else {
    display_no_result(get_string('database_content', 'block_searchthiscourse'), 'data');
}

// Feedback. ///////////////////////////////////////////////////////////////////////////////////////

// Feedback names.
$res = search_feedback_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('feedback_names', 'block_searchthiscourse'), 'feedback');
} else {
    display_no_result(get_string('feedback_names', 'block_searchthiscourse'), 'feedback');
}

// Feedback questions.
if ($can_edit) {
    $res = search_feedback_questions($search, $course->id);
    if ($res) {
        display_result($res, get_string('feedback_questions', 'block_searchthiscourse'), 'feedback');
    } else {
        display_no_result(get_string('feedback_questions', 'block_searchthiscourse'), 'feedback');
    }
}

// Feedback answers.
if ($can_edit) {
    $res = search_feedback_answers($search, $course->id);
    if ($res) {
        display_result($res, get_string('feedback_answers', 'block_searchthiscourse'), 'feedback');
    } else {
        display_no_result(get_string('feedback_answers', 'block_searchthiscourse'), 'feedback');
    }
}

// Files. //////////////////////////////////////////////////////////////////////////////////////////

// File names.
/*$res = search_filenames($search, $course->id);
if ($res) {
    display_result($res, get_string('file_titles', 'block_searchthiscourse'), 'files');
} else {
    display_no_result(get_string('file_titles', 'block_searchthiscourse'), 'files');
}*/

// Folder. /////////////////////////////////////////////////////////////////////////////////////////

// Folder names.
$res = search_folder_names($search, $course->id);
if ($res) {
    display_result($res, get_string('folder_names', 'block_searchthiscourse'), 'folder');
} else {
    display_no_result(get_string('folder_names', 'block_searchthiscourse'), 'folder');
}

// Forums. /////////////////////////////////////////////////////////////////////////////////////////

// Forum titles.
$res = search_forum_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('forum_titles', 'block_searchthiscourse'), 'forum');
} else {
    display_no_result(get_string('forum_titles', 'block_searchthiscourse'), 'forum');
}

// Forum discussions.
$res = search_forum_discussions($search, $course->id);
if ($res) {
    display_result($res, get_string('forum_discussions', 'block_searchthiscourse'), 'forum');
} else {
    display_no_result(get_string('forum_discussions', 'block_searchthiscourse'), 'forum');
}

// Forum posts.
$res = search_forum_posts($search, $course->id);
if ($res) {
    display_result($res, get_string('forum_posts', 'block_searchthiscourse'), 'forum');
} else {
    display_no_result(get_string('forum_posts', 'block_searchthiscourse'), 'forum');
}

// Glossaries //////////////////////////////////////////////////////////////////////////////////////

// Glossary titles.
$res = search_glossary_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('glossary_titles', 'block_searchthiscourse'), 'glossary');
} else {
    display_no_result(get_string('glossary_titles', 'block_searchthiscourse'), 'glossary');
}

// Glossary entries.
$res = search_glossary_entries($search, $course->id);
if ($res) {
    display_result($res, get_string('glossary_entries', 'block_searchthiscourse'), 'glossary');
} else {
    display_no_result(get_string('glossary_entries', 'block_searchthiscourse'), 'glossary');
}

// Labels //////////////////////////////////////////////////////////////////////////////////////////

// Labels.
$res = search_labels($search, $course->id);
if ($res) {
    // Label mod has no icon.
    display_result($res, get_string('labels', 'block_searchthiscourse'));
} else {
    display_no_result(get_string('labels', 'block_searchthiscourse'));
}

// Lesson //////////////////////////////////////////////////////////////////////////////////////////

// Lesson titles.
$res = search_lesson_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('lesson_titles', 'block_searchthiscourse'), 'lesson');
} else {
    display_no_result(get_string('lesson_titles', 'block_searchthiscourse'), 'lesson');
}

// Lesson pages.
$res = search_lesson_pages($search, $course->id);
if ($res) {
    display_result($res, get_string('lesson_pages', 'block_searchthiscourse'), 'lesson');
} else {
    display_no_result(get_string('lesson_pages', 'block_searchthiscourse'), 'lesson');
}

// Lesson answers?
/*
$res = search_lesson_answers($search, $course->id);
if ($res) {
    display_result($res, get_string('lesson_answers', 'block_searchthiscourse'), 'lesson');
} else {
    display_no_result(get_string('lesson_answers', 'block_searchthiscourse'), 'lesson');
}
*/

// Pages. //////////////////////////////////////////////////////////////////////////////////////////

// Page titles.
$res = search_page_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('page_titles', 'block_searchthiscourse'), 'page');
} else {
    display_no_result(get_string('page_titles', 'block_searchthiscourse'), 'page');
}

// Page content.
$res = search_page_content($search, $course->id);
if ($res) {
    display_result($res, get_string('page_content', 'block_searchthiscourse'), 'page');
} else {
    display_no_result(get_string('page_content', 'block_searchthiscourse'), 'page');
}

// Slideshow. //////////////////////////////////////////////////////////////////////////////////////

if (check_plugin_installed('slideshow')) {
    // Slideshow name.
    $res = search_slideshow_names($search, $course->id);
    if ($res) {
        display_result($res, get_string('slideshow_names', 'block_searchthiscourse'), 'slideshow');
    } else {
        display_no_result(get_string('slideshow_names', 'block_searchthiscourse'), 'slideshow');
    }

    // Slideshow captions.
    $res = search_slideshow_captions($search, $course->id);
    if ($res) {
        display_result($res, get_string('slideshow_captions', 'block_searchthiscourse'), 'slideshow');
    } else {
        display_no_result(get_string('slideshow_captions', 'block_searchthiscourse'), 'slideshow');
    }
}

// URLs. ///////////////////////////////////////////////////////////////////////////////////////////

// URL titles.
$res = search_url_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('url_titles', 'block_searchthiscourse'), 'url');
} else {
    display_no_result(get_string('url_titles', 'block_searchthiscourse'), 'url');
}

// URLs.
$res = search_urls($search, $course->id);
if ($res) {
    display_result($res, get_string('urls', 'block_searchthiscourse'), 'url');
} else {
    display_no_result(get_string('urls', 'block_searchthiscourse'), 'url');
}

// Wiki ////////////////////////////////////////////////////////////////////////////////////////////

// Wiki titles.
$res = search_wiki_titles($search, $course->id);
if ($res) {
    display_result($res, get_string('wiki_titles', 'block_searchthiscourse'), 'wiki');
} else {
    display_no_result(get_string('wiki_titles', 'block_searchthiscourse'), 'wiki');
}

// Wiki pages.
$res = search_wiki_pages($search, $course->id);
if ($res) {
    display_result($res, get_string('wiki_pages', 'block_searchthiscourse'), 'wiki');
} else {
    display_no_result(get_string('wiki_pages', 'block_searchthiscourse'), 'wiki');
}

// Wiki versions (history).
$res = search_wiki_versions($search, $course->id);
if ($res) {
    display_result($res, get_string('wiki_versions', 'block_searchthiscourse'), 'wiki');
} else {
    display_no_result(get_string('wiki_versions', 'block_searchthiscourse'), 'wiki');
}

echo $OUTPUT->footer();
