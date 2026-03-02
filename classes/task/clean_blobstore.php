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
 * This task runs every couple of hours and sends followup emails to users who have completed a course
 * and who have not yet been emailed.
 * It relies on the 'followup_enabled', 'followup_template', 'followup_subject', and 'followup_delay' course custom fields
 *
 * @package   local_blobstorebackend
 * @copyright 2023 <tim.stclair@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_blobstorebackend\task;

/**
 * An example of a scheduled task.
 */
class clean_blobstore extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('clean_blobstore', 'local_blobstorebackend');
    }

    /**
     * This is the code that will be executed when the cron runs.
     */
    public function execute() {
        global $CFG;

        $scan_root = $CFG->dataroot . DIRECTORY_SEPARATOR . 'blobstorebackend' . DIRECTORY_SEPARATOR;

        if (!is_dir($scan_root)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($scan_root, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                // This will only remove empty directories. For nested non-empty directories,
                // the inner contents must be removed first, which CHILD_FIRST ensures.
                @rmdir($file->getPathname());
            } else if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
                if (filemtime($file->getPathname()) < time() - 3600) {
                    unlink($file->getPathname());
                }
            }
        }
    }
}