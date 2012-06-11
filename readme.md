# SearchThisCourse block for Moodle 2

Deep-searches through a whole course for a keyword or phrase.

## Introduction

We have global searches and course searches and forum searches and a host of other search options, but what we don't have is the ability to search exactly one whole, entire course and all that course's plugin instances for a keyword (or keywords). This is known as 'deep searching'.

## Licence

SearchThisCourse block for Moodle 2, copyright &copy; 2012, Paul Vaughan.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

## Purpose

Being able to type any keyword of your choice and search for it whether it exists in a Label, Forum post, Book chapter, Page or any core Moodle activity is something we don't often need to do, but have had no way of doing. Until now.

Currently it will search the current course in the following places:

### *Standard* Moodle 2.2 modules:

* Assignment
    * titles
    * submissions (*teachers only*)
* Book (core module in 2.3, but a third-party plugin for 2.2)
* Chat
    * titles
    * messages (*teachers only*)
* Choice
    * titles
    * options
* Database
    * titles
    * fields (*teachers only*)
    * content
* Feedback
    * titles
    * questions (*teachers only*)
    * answers (*teachers only*)
* Folder
* Forum
    * titles
    * discussions
    * posts
* Glossary
    * titles
    * entries
* Labels
* Lesson
    * titles
    * pages
* Page
    * titles
    * content
* URLs
    * titles
    * URL itself
* Wiki
    * titles
    * pages
    * versions (the history of page edits)

### Does not yet search through these *standard* Moodle 2.2 modules:

* File          42816
* Quiz          579
* Resource      -
* Survey        1
* Workshop      -

### Probably will never search through these *standard* Moodle 2.2 modules:

* IMS content package
* LTI
* SCORM package

### Searches through these *core* Moodle areas:

* Course name / description / section names

### Does not yet search through these *core* Moodle areas:

* User descriptions / custom fields

### Searches through these *third-party* plugins:

* Book
    * titles
    * content
* Checklist
* Slideshow
    * names
    * captions

### Does not yet search through these *third-party* plugins:

* Certificate
* HotPot
* Journal
* OU blog
* OU wiki
* Realtime Quiz
* Scheduler

Note that these plugins are on this list only because these are the third party plugins already in use by South Devon College. There are many more plugins, we just don't use them all. :)

If you'd like to request a new plugin be searched by this plugin, [raise an isue on GitHub](https://github.com/vaughany/moodle-block_searchthiscourse/issues) and I will see what I can do.  Alternatively, fork the repository, fix the problem and submit a pull request.

## Installation

Installation is a matter of copying files to the correct location within your Moodle installation, but it is always wise to test new plugins in a sandbox environment first, and have the ability to roll back changes.

Download the archive and extract the files, or [clone the repository from GitHub](https://github.com/vaughany/moodle-block_searchthiscourse). You should see the following files and structure:

    searchthiscourse/
    |-- block_searchthiscourse.php
    |-- lang
    |   `-- en
    |       `-- block_searchthiscourse.php
    |-- readme.md
    |-- search.php
    |-- styles.css
    -- version.php

Copy the 'searchthiscourse' folder into your Moodle installation's **blocks** folder.

Log in to your Moodle as Admin and click on Notifications on the Admin menu.

The block should successfully install. If you receive any error messages at this point, please [raise an issue on GitHub](https://github.com/vaughany/moodle-block_searchthiscourse/issues) giving as much detail as possible.

Add the block to a page. The block is able to be placed anywhere within Moodle, and is visible to all users.

## Use

Type a search term into the box, click search. :)

## Configuration

This block has none at this time.

## Known Issues

This block has none at this time, aside the third-party plugins not yet added to the search.

Should you find a bug, have an issue, feature request or new language pack, please [log an issue in the tracker](https://github.com/vaughany/moodle-block_searchthiscourse/issues) or fork the repo, fix the problem and submit a pull request.

## To do

There is a list of *issues* (problems as well as improvements) [on GitHub](https://github.com/vaughany/moodle-block_searchthiscourse/issues). This list will be addressed as time and necessity dictates. Bugs will always be given top priority.

## Acknowledgements

Thanks.

## History

Beta testing.
