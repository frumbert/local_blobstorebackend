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
 * blobstore backend - link to report page for admins
 *
 * @package    local_blobstorebackend
 * @copyright  2025 Tim St Clair
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $CFG;

    $settings = new admin_settingpage('local_blobstorebackend', get_string('adminreport','local_blobstorebackend'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading(
        'local_blobstorebackend/hyperlink',
        get_string('report'), // Title of the setting
        html_writer::link(
            new moodle_url('/local/blobstorebackend/report.php'), // URL of the hyperlink
            get_string('userreport','local_blobstorebackend'), // Text for the hyperlink
            []
        )
    ));

}
