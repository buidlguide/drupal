
The Admin Notes module creates a block that displays a textarea pre-filled with the existing comment for that specific page, if any.

Each comment is associated with a page.

Installation
============
- Download and extract to modules folder (usually sites/all/modules)
- Go to admin/build/modules and enable Admin Notes, which is in the Admin fieldgroup
- Go to admin/build/blocks and place the 'Admin Notes' block where ever you would like it to display.
   - By default the block is set to only display on admin pages. You can change this (like any other block configuration) to suite your needs.
- Go to admin/user/permissions and set the 'access admin notes' permission to the appropriate role(s)

Usage
======
The 'Admin Notes' block will be setup and ready to use after installation and can be used to record any information on a given page that you need to remember about that page.

You can use it as a todo list, a code snippet log, or just about anything else you can think of where you need to keep notes.

There is a report that users with the 'access admin notes' permission can access at admin/reports/admin_notes that will show a table of all recorded notes.

Maintainers
===========
bocaj - Current active maintainer
aaron - Initial project organizer

Special Thanks to:
==================
The Journal module maintainers. I was able to save a lot of time building this module by referring to theirs!http://drupal.org/projects/journal
