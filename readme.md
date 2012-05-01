# SearchThisCourse block for Moodle 2.x

Searches through a course and all it's resources for a keyword.

## Introduction

We have global searches and course searches and forum searches and a host of other search options, but what we don't have is the ability to search exactly one whole, entire course for a keyword (or keywords).

## Licence

SearchThisCourse block for Moodle 2.x, copyright &copy; 2012, Paul Vaughan.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

## Purpose

Being able to type 'cheese' (a keyword of your choice) and have it found whether it exists in a Label, Forum post or Book page is something we don't often need to do, but have had no way of doing. Until now.

## Installation

Installation is a matter of copying files to the correct location within your Moodle installation, but it is always wise to test new plugins in a sandbox environment first, and have the ability to roll back changes.

Download the archive and extract the files, or [clone the repository from GitHub](https://github.com/vaughany/moodle-block_xkcd). You should see the following files and structure:

    xkcd/
    |-- block_searchthiscourse.php
    |-- lang
    |   `-- en
    |       `-- block_searchthiscourse.php
    |-- readme.md
    |-- search.php
    |-- styles.css
    `-- version.php

Copy the 'searchthiscourse' folder into your Moodle installation's **blocks** folder.

Log in to your Moodle as Admin and click on Notifications on the Admin menu.

The block should successfully install. If you receive any error messages at this point, please [raise an issue on GitHub](https://github.com/vaughany/moodle-block_searchthiscourse/issues) giving as much detail as possible.

Add the block to a page. The block is able to be placed anywhere within Moodle, and is visible to all users.

## Use

Type a search term into the box, click search. :)

## Configuration

This block has none at this time.

## Known Issues

This block has none at this time.

Should you find a bug, have an issue, feature request or new language pack, please [log an issue in the tracker](https://github.com/vaughany/moodle-block_searchthiscourse/issues) or fork the repo, fix the problem and submit a pull request.

## To do

* Lots, at this time.

## Acknowledgements

Thanks.

## History

Still in alpha at this time.
