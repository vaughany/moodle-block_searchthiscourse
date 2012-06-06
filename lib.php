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
$can_edit   = has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $COURSE->id));

// Some functions summarise the content they get from the database to this number of characters.
$summary_length = 75;

/**
 * This function takes each word out of the search string, makes sure they are at least
 * two characters long and returns an array containing every good word.
 *
 * @param string $words     String containing space-separated strings to search for
 * @param int $len          Int defining minimum length of search word
 * @param string $prefix    String to prepend to the each token taken out of $words
 * @returns array
 */
function clean_search_terms($words, $len=2) {
    $searchterms = explode(' ', $words);
    foreach ($searchterms as $key => $searchterm) {
        if (strlen($searchterm) < $len) {
            unset($searchterms[$key]);
            // } else if ($prefix) {
            // $searchterms[$key] = $prefix.$searchterm;
        }
    }
    return trim(implode(' ', $searchterms));
}


/**
 * This function shortens content to a more usable length.
 * @param string $content       String to trim
 * @returns string
 */
function summarise_content($content) {
    global $summary_length;
    if (strlen($content) < $summary_length) {
        // Strip tags, get the slice of the string, trim the result, add dots.
        return trim(strip_tags($content));
    } else {
        // Strip tags, get the slice of the string, trim the result, add dots.
        return trim(substr(strip_tags($content), 0, $summary_length)).'...';
    }
}


/*
 * Regular use function for displaying the results of searches in a nice way.
 * @param object $res       Database result object.
 * @param string $title     Text snippet of the searched area.
 */
function display_result_links($res, $title, $module = null) {
    global $CFG, $OUTPUT;

    $listtype = (count($res) > 1) ? 'ol' : 'ul';

    if ($module) {
        $img = '<img src="'.$CFG->wwwroot.'/theme/image.php?theme='.$CFG->theme.'&image=icon&component='.$module.'" alt="'.$module.'" title="'.$module.'" /> ';
    } else {
        $img = '';
    }

    // echo $OUTPUT->box_start('generalbox');
    echo "<p>{$img}Found the following $title:</p>\n<$listtype>\n";
    foreach ($res as $item) {
        echo "<li>$item</li>\n";
    }
    echo "</$listtype>\n";
    // echo $OUTPUT->box_end();
}


/*
 * Regular use function for displaying the lack of search results.
 * @param string $title     Text snippet of the searched area.
 */
function display_no_result($title, $module = null) {
    global $CFG;

    if ($module) {
        $img = '<img src="'.$CFG->wwwroot.'/theme/image.php?theme='.$CFG->theme.'&image=icon&component='.$module.'" alt="'.$module.'" title="'.$module.'" /> ';
    } else {
        $img = '';
    }

    echo "<p>{$img}Did not find the search term in $title.</p>\n";
}



/**
 * Check the plugin is installed.
 * @param string $name      Name of block or module (will take either).
 * @return true or false
 */
function check_plugin_installed($name) {
    global $DB;
    $module = $DB->get_record('modules', array('name' => $name), 'id');
    if ($module) {
        return true;
    } else {
        $block = $DB->get_record('block', array('name' => $name), 'id');
        if ($block) {
            return true;
        } else {
            return false;
        }
    }
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
 * Search forum titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_forum_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    // Forums cannot be hidden globally, so little point checking!
    // if (!check_plugin_visible('forum')) {
    //    return false;
    // }

    $res = $DB->get_records_select('forum', "course = '$cid' AND intro LIKE '%$search%'", array('id, intro'));
    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('forum', $row)) {
            $ret[] = html_writer::link(new moodle_url('/mod/forum/view.php', array('f' => $row->id)), $row->intro);
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = html_writer::link(new moodle_url('/mod/forum/view.php', array('f' => $row->id)), $row->intro, array('class' => 'dimmed_text'));
            }
        }
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
    global $CFG, $DB, $can_edit;

    $res = $DB->get_records_select('forum_discussions', "course = '$cid' AND name LIKE '%$search%'", array('id, name'));
    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('forum', $row)) {
            $ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->id)), $row->name);
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->id)), $row->name, array('class' => 'dimmed_text'));
            }
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
    global $CFG, $DB, $can_edit;

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
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$row->discussion.'#p'.$row->pid.'">'.$row->subject."</a>\n";
                // $ret[] = html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $row->discussion, '#p' => $row->id)), $row->subject);
                // tried using html_writer::link here but it can't handle the # on the end.
            }
        }

    }
    return $ret;
}



/*
 * Search glossary titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_glossary_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('glossary')) {
        return false;
    }

    $res = $DB->get_records_select('glossary', "course = '$cid' AND name LIKE '%$search%' OR intro LIKE '%$search%'", array('id, name'));
    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('forum', $row)) {
            $ret[] = html_writer::link(new moodle_url('/mod/glossary/view.php', array('id' => $row->id)), $row->name);
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = html_writer::link(new moodle_url('/mod/glossary/view.php', array('id' => $row->id)), $row->name, array('class' => 'dimmed_text'));
            }
        }
    }
    return $ret;
}

/*
 * Search glossary entries for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_glossary_entries($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('glossary')) {
        return false;
    }

    // TODO: This gets the user to the glossary, but not the specific entry
    // TODO: Strip HTML from the returned fields.

    $sql = "SELECT ".$CFG->prefix."glossary_entries.id, glossaryid, concept, ".$CFG->prefix."course_modules.id AS cmid,
                ".$CFG->prefix."glossary.course, ".$CFG->prefix."glossary_entries.definition
            FROM ".$CFG->prefix."glossary_entries, ".$CFG->prefix."glossary, ".$CFG->prefix."modules, ".$CFG->prefix."course_modules
            WHERE ".$CFG->prefix."glossary_entries.glossaryid = ".$CFG->prefix."glossary.id
            AND ".$CFG->prefix."glossary.course = '".$cid."'
            AND (concept LIKE '%$search%' OR definition LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'glossary'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."course_modules.course = ".$CFG->prefix."glossary.course;";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        if (instance_is_visible('label', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/glossary/view.php?id='.$row->cmid.'"> '.$row->concept.'</a>: ('.strip_tags($row->definition).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/glossary/view.php?id='.$row->cmid.'"> '.$row->concept.'</a>: ('.strip_tags($row->definition).")\n";
            }
        }

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
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('label')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."label.id, ".$CFG->prefix."label.name, ".$CFG->prefix."course_modules.section, ".$CFG->prefix."course_modules.course
            FROM ".$CFG->prefix."label, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."label.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."label.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'label'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."label.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        // Check each instance's visibility. Use only if visible.
        // Or, have results returned for teachers showing hidden elements, much like the course proper.
        if (instance_is_visible('label', $row)) {
            $ret[] = 'Search term found in a label in <a href="'.$CFG->wwwroot.'/course/view.php?id='.$cid.'#section-'.($row->section-1).'">section '.($row->section-1)."</a>\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/course/view.php?id='.$cid.'#section-'.($row->section-1).'">Search term found in a <em>hidden</em> label in section '.($row->section-1)."</a>\n";
            }
        }

    }
    return $ret;
}



/*
 * Search checklist titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_checklist_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('checklist')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."checklist.id, ".$CFG->prefix."checklist.name, ".$CFG->prefix."checklist.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."checklist, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."checklist.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."checklist.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'checklist'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."checklist.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('checklist', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/checklist/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/checklist/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search url titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_url_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('url')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."url.id, ".$CFG->prefix."url.name, ".$CFG->prefix."url.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."url, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."url.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."url.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'url'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."url.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('url', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/url/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/url/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search urls for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_urls($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('url')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."url.id, ".$CFG->prefix."url.name, ".$CFG->prefix."url.externalurl, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."url, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."url.course = ".$CFG->prefix."course_modules.course
            AND externalurl LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'url'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."url.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('url', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/url/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: (<a href="'.$row->externalurl.'">'.$row->externalurl."</a>)\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/url/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: (<a href="'.$row->externalurl.'">'.$row->externalurl."</a>)\n";
            }
        }
    }
    return $ret;
}


/*
 * Search page titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_page_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('page')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."page.id, ".$CFG->prefix."page.name, ".$CFG->prefix."page.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."page, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."page.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."page.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'page'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."page.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('page', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/page/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/page/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search page content for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_page_content($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('page')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."page.id, ".$CFG->prefix."page.name, ".$CFG->prefix."page.intro, ".$CFG->prefix."page.content, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."page, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."page.course = ".$CFG->prefix."course_modules.course
            AND content LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'page'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."page.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        // Summarising the page's content.
        $summary = summarise_content($row->content);

        if (instance_is_visible('page', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/page/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.$summary.")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/page/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.$summary.")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search filenames for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
/*
function search_filenames($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('files')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."files.id, ".$CFG->prefix."files.filename, ".$CFG->prefix."files.userid, ".$CFG->prefix."files.filesize, ".$CFG->prefix."files.mimetype,
                ".$CFG->prefix."files.author, ".$CFG->prefix."course_modules.section, ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."files, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."files.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."modules.name = 'files'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."files.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('url', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/url/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: (<a href="'.$row->externalurl.'">'.$row->externalurl."</a>)\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/url/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: (<a href="'.$row->externalurl.'">'.$row->externalurl."</a>)\n";
            }
        }
    }
    return $ret;
}*/


/*
 * Search book titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_book_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('book')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."book.id, ".$CFG->prefix."book.name, ".$CFG->prefix."book.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."book, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."book.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."book.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'book'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."book.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('book', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/book/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/book/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search book content for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_book_content($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('book')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."book_chapters.id, ".$CFG->prefix."book_chapters.title, ".$CFG->prefix."book_chapters.content, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."book, ".$CFG->prefix."book_chapters, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."book.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."book_chapters.bookid = ".$CFG->prefix."book.id
            AND (title LIKE '%$search%' OR content LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'book'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."book.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        // Summarising the page's content.
        $summary = summarise_content($row->content);

        if (instance_is_visible('book', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/book/view.php?id='.$row->cmid.'&chapterid='.$row->id.'"> '.$row->title.'</a>: ('.$summary.")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/book/view.php?id='.$row->cmid.'&chapterid='.$row->id.'"> '.$row->title.'</a>: ('.$summary.")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search assignment titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_assignment_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('assignment')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."assignment.id, ".$CFG->prefix."assignment.name, ".$CFG->prefix."assignment.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."assignment, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."assignment.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."assignment.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'assignment'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."assignment.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('assignment', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/assignment/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/assignment/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search assignment content for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_assignment_submission($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('assignment')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."assignment_submissions.id, ".$CFG->prefix."assignment.name, ".$CFG->prefix."assignment_submissions.data1, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid, ".$CFG->prefix."assignment_submissions.userid AS uid
            FROM ".$CFG->prefix."assignment, ".$CFG->prefix."assignment_submissions, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."assignment.course = ".$CFG->prefix."course_modules.course
            AND (data1 LIKE '%$search%' OR data2 LIKE '%$search%' OR submissioncomment LIKE '%$search%')
            AND ".$CFG->prefix."assignment_submissions.assignment = ".$CFG->prefix."assignment.id
            AND ".$CFG->prefix."modules.name = 'assignment'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."assignment.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        // Summarising the assignment's content.
        $summary = summarise_content($row->data1);

        if (instance_is_visible('assignment', $row)) {
            // $ret[] = '<a href="'.$CFG->wwwroot.'/mod/assignment/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.$summary.")\n";
            // http://172.21.4.85/sdcmoodle2/mod/assignment/type/online/file.php?id=75&userid=3
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/assignment/type/online/file.php?id='.$row->cmid.'&userid='.$row->uid.'"> '.$row->name.'</a>: ('.$summary.")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/assignment/type/online/file.php?id='.$row->cmid.'&userid='.$row->uid.'"> '.$row->name.'</a>: ('.$summary.")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search folder names for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_folder_names($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('folder')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."folder.id, ".$CFG->prefix."folder.name, ".$CFG->prefix."folder.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."folder, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."folder.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."folder.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'folder'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."folder.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('folder', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/folder/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/folder/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search feedback titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_feedback_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('feedback')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."feedback.id, ".$CFG->prefix."feedback.name, ".$CFG->prefix."feedback.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."feedback, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."feedback.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."feedback.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'feedback'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."feedback.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('feedback', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/feedback/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/feedback/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search feedback questions for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_feedback_questions($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('feedback')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."feedback_item.id, ".$CFG->prefix."feedback.name, ".$CFG->prefix."feedback_item.name AS fname,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."feedback, ".$CFG->prefix."feedback_item, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."feedback.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."feedback.id = ".$CFG->prefix."feedback_item.feedback
            AND ".$CFG->prefix."feedback_item.name LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'feedback'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."feedback.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('feedback', $row)) {
                $ret[] = '<a href="'.$CFG->wwwroot.'/mod/feedback/edit.php?id='.$row->cmid.'"> '.$row->fname.'</a>: ('.strip_tags($row->name).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/feedback/edit.php?id='.$row->cmid.'"> '.$row->fname.'</a>: ('.strip_tags($row->name).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search feedback answers for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_feedback_answers($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('feedback')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."feedback_value.id, ".$CFG->prefix."feedback.name, ".$CFG->prefix."course_modules.course,
                ".$CFG->prefix."course_modules.id AS cmid, ".$CFG->prefix."feedback_value.value
            FROM ".$CFG->prefix."feedback, ".$CFG->prefix."feedback_completed, ".$CFG->prefix."feedback_value, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."feedback.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."feedback.id = ".$CFG->prefix."feedback_completed.feedback
            AND ".$CFG->prefix."feedback_completed.feedback = ".$CFG->prefix."feedback_value.completed
            AND ".$CFG->prefix."feedback_value.value LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'feedback'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."feedback.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('feedback', $row)) {
                $ret[] = '<a href="'.$CFG->wwwroot.'/mod/feedback/analysis.php?id='.$row->cmid.'"> '.$row->value.'</a>: ('.strip_tags($row->name).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/feedback/analysis.php?id='.$row->cmid.'"> '.$row->value.'</a>: ('.strip_tags($row->name).")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search chat titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_chat_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('chat')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."chat.id, ".$CFG->prefix."chat.name, ".$CFG->prefix."chat.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."chat, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."chat.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."chat.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'chat'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."chat.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('chat', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/chat/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/chat/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search glossary entries for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_chat_entries($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('chat')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."chat_messages.id, ".$CFG->prefix."chat_messages.chatid, message, ".$CFG->prefix."chat.name, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid,
                ".$CFG->prefix."user.firstname, ".$CFG->prefix."user.lastname, ".$CFG->prefix."user.id AS uid
            FROM ".$CFG->prefix."chat, ".$CFG->prefix."chat_messages, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules, ".$CFG->prefix."user
            WHERE ".$CFG->prefix."chat.id = ".$CFG->prefix."chat_messages.chatid
            AND message LIKE '%$search%'
            AND system = '0'
            AND ".$CFG->prefix."modules.name = 'chat'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."chat.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."'
            AND ".$CFG->prefix."chat_messages.userid = ".$CFG->prefix."user.id
            ORDER BY timestamp ASC;";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {

        if (instance_is_visible('chat', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$row->uid.'">'.$row->firstname.' '.$row->lastname.'</a>: <a href="'.$CFG->wwwroot.'/mod/chat/report.php?id='.$row->cmid.'"> '.$row->message.'</a>: ('.strip_tags($row->name).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/user/profile.php?id='.$row->uid.'">'.$row->firstname.' '.$row->lastname.'</a>: <a href="'.$CFG->wwwroot.'/mod/chat/report.php?id='.$row->cmid.'"> '.$row->message.'</a>: ('.strip_tags($row->name).")\n";
            }
        }

    }
    return $ret;
}

/*
 * Search choice titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_choice_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('choice')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."choice.id, ".$CFG->prefix."choice.name, ".$CFG->prefix."choice.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."choice, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."choice.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."choice.name LIKE '%$search%' OR intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'choice'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."choice.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('choice', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/choice/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/choice/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->intro).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search choice options for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_choice_options($search, $cid) {
    global $CFG, $DB, $can_edit, $USER;

    if (!check_plugin_visible('choice')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."choice_options.id, ".$CFG->prefix."choice.id, ".$CFG->prefix."choice.name, text, ".$CFG->prefix."choice.intro, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."choice, ".$CFG->prefix."choice_options, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."choice.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."choice_options.choiceid = ".$CFG->prefix."choice.id
            AND text LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'choice'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."choice.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('choice', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/course/mod.php?sesskey='.$USER->sesskey.'&sr=1&update='.$row->cmid.'"> '.strip_tags($row->text).'</a>: ('.strip_tags($row->name).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/course/mod.php?sesskey='.$USER->sesskey.'&sr=1&update='.$row->cmid.'"> '.strip_tags($row->text).'</a>: ('.strip_tags($row->name).")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search lesson titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_lesson_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('choice')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."lesson.id, ".$CFG->prefix."lesson.name, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."lesson, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."lesson.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."lesson.name LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'lesson'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."lesson.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('choice', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/lesson/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->name).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/lesson/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->name).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search lesson pages for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_lesson_pages($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('choice')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."lesson_pages.id, ".$CFG->prefix."lesson_pages.title, ".$CFG->prefix."lesson.name, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."lesson, ".$CFG->prefix."lesson_pages, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."lesson.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."lesson.id = ".$CFG->prefix."lesson_pages.lessonid
            AND (".$CFG->prefix."lesson_pages.title LIKE '%$search%' OR ".$CFG->prefix."lesson_pages.contents LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'lesson'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."lesson.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('choice', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/lesson/view.php?id='.$row->cmid.'&pageid='.$row->id.'">'.$row->title.'</a>: ('.strip_tags($row->name).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/lesson/view.php?id='.$row->cmid.'&pageid='.$row->id.'">'.$row->title.'</a>: ('.strip_tags($row->name).")\n";
            }
        }
    }
    return $ret;
}


/*
 * Search wiki titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_wiki_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('wiki')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."wiki.id, ".$CFG->prefix."wiki.name, firstpagetitle, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."wiki, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."wiki.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."wiki.name LIKE '%$search%' OR ".$CFG->prefix."wiki.intro LIKE '%$search%' OR firstpagetitle LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'wiki'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."wiki.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('wiki', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/wiki/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->firstpagetitle).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/wiki/view.php?id='.$row->cmid.'"> '.$row->name.'</a>: ('.strip_tags($row->firstpagetitle).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search wiki pages for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_wiki_pages($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('wiki')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."wiki_pages.id, ".$CFG->prefix."wiki_pages.subwikiid, ".$CFG->prefix."wiki.name, title, cachedcontent, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."wiki, ".$CFG->prefix."wiki_pages, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."wiki.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."wiki_pages.subwikiid = ".$CFG->prefix."wiki.id
            AND ".$CFG->prefix."wiki_pages.title LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'wiki'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."wiki.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('wiki', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/wiki/view.php?pageid='.$row->id.'"> '.$row->title.'</a>: ('.strip_tags($row->name).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/wiki/view.php?pageid='.$row->id.'"> '.$row->title.'</a>: ('.strip_tags($row->name).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search wiki versions (history) for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_wiki_versions($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('wiki')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."wiki_versions.id, ".$CFG->prefix."wiki_pages.id AS pid, ".$CFG->prefix."wiki_versions.content, ".$CFG->prefix."wiki.name, title, cachedcontent, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."wiki, ".$CFG->prefix."wiki_versions, ".$CFG->prefix."wiki_pages, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."wiki.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."wiki_versions.pageid = ".$CFG->prefix."wiki_pages.id
            AND ".$CFG->prefix."wiki_pages.subwikiid = ".$CFG->prefix."wiki.id
            AND ".$CFG->prefix."wiki_versions.content LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'wiki'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."wiki.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('wiki', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/wiki/history.php?pageid='.$row->pid.'"> '.strip_tags($row->content).'</a>: ('.strip_tags($row->title).")\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/wiki/history.php?pageid='.$row->pid.'"> '.strip_tags($row->content).'</a>: ('.strip_tags($row->title).")\n";
            }
        }
    }
    return $ret;
}

/*
 * Search data[base] titles for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_data_titles($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('data')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."data.id, ".$CFG->prefix."data.name, ".$CFG->prefix."course_modules.section,
                ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."data, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."data.course = ".$CFG->prefix."course_modules.course
            AND (".$CFG->prefix."data.name LIKE '%$search%' OR ".$CFG->prefix."data.intro LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'data'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."data.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('data', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/data/view.php?id='.$row->cmid.'"> '.$row->name."</a>\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/data/view.php?id='.$row->cmid.'"> '.$row->name."</a>\n";
            }
        }
    }
    return $ret;
}

/*
 * Search data[base] fields for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_data_fields($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('data')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."data.id, ".$CFG->prefix."data.name, ".$CFG->prefix."data_fields.name AS dfname,
                ".$CFG->prefix."course_modules.section, ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."data, ".$CFG->prefix."data_fields, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."data.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."data_fields.dataid = ".$CFG->prefix."data.id
            AND (".$CFG->prefix."data_fields.name LIKE '%$search%' OR ".$CFG->prefix."data_fields.description LIKE '%$search%')
            AND ".$CFG->prefix."modules.name = 'data'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."data.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('data', $row)) {
            $ret[] = '<a href="'.$CFG->wwwroot.'/mod/data/field.php?d='.$row->id.'"> '.$row->dfname.'</a> (<a href="'.$CFG->wwwroot.'/mod/data/view.php?id='.$row->cmid.'">'.$row->name."</a>)\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<a class="dimmed_text" href="'.$CFG->wwwroot.'/mod/data/field.php?d='.$row->id.'"> '.$row->dfname.'</a> (<a href="'.$CFG->wwwroot.'/mod/data/view.php?id='.$row->cmid.'">'.$row->name."</a>)\n";
            }
        }
    }
    return $ret;
}

/*
 * Search data[base] content for the keyword
 *
 * @param string $search    Search word or phrase.
 * @param int $cid          Course ID.
 * @returns array
 */
function search_data_content($search, $cid) {
    global $CFG, $DB, $can_edit;

    if (!check_plugin_visible('data')) {
        return false;
    }

    $sql = "SELECT ".$CFG->prefix."data_content.id, ".$CFG->prefix."data.id AS did, ".$CFG->prefix."data.name, content,
                ".$CFG->prefix."course_modules.section, ".$CFG->prefix."course_modules.course, ".$CFG->prefix."course_modules.id AS cmid
            FROM ".$CFG->prefix."data, ".$CFG->prefix."data_content, ".$CFG->prefix."data_fields, ".$CFG->prefix."course_modules, ".$CFG->prefix."modules
            WHERE ".$CFG->prefix."data.course = ".$CFG->prefix."course_modules.course
            AND ".$CFG->prefix."data_content.fieldid = ".$CFG->prefix."data_fields.id
            AND ".$CFG->prefix."data_fields.dataid = ".$CFG->prefix."data.id
            AND ".$CFG->prefix."data_content.content LIKE '%$search%'
            AND ".$CFG->prefix."modules.name = 'data'
            AND ".$CFG->prefix."modules.id = ".$CFG->prefix."course_modules.module
            AND ".$CFG->prefix."data.id = ".$CFG->prefix."course_modules.instance
            AND ".$CFG->prefix."course_modules.course = '".$cid."';";

    $res = $DB->get_records_sql($sql);

    $ret = array();
    foreach ($res as $row) {
        if (instance_is_visible('data', $row)) {
            $ret[] = strip_tags($row->content).' (<a href="'.$CFG->wwwroot.'/mod/data/view.php?id='.$row->cmid.'">'.strip_tags($row->name)."</a>)\n";
        } else {
            // Show hidden items only if the user has the required capability.
            if ($can_edit) {
                $ret[] = '<span class="dimmed_text">'.strip_tags($row->content).' (<a href="'.$CFG->wwwroot.'/mod/data/view.php?id='.$row->cmid.'">'.strip_tags($row->name)."</a>)</span>\n";
            }
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
        // global $path;
        $mform = $this->_form;

        $a = new stdClass();
        // $a->link = html_writer::link('http://docs.moodle.org/en/Development:Coding_style',
        //        get_string('moodlecodingguidelines', 'local_codechecker'));
        // $a->path = html_writer::tag('tt', 'local/codechecker');
        $mform->addElement('static', '', '', get_string('info', 'block_searchthiscourse', $a));

        $mform->addElement('text', 'path', get_string('path', 'block_searchthiscourse'));

        $mform->addElement('submit', 'submitbutton', get_string('pluginname', 'block_searchthiscourse').'!');
    }
}
