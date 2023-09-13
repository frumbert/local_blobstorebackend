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
 * Version details
 *
 * @package    local
 * @subpackage blobstorebackend
 * @copyright  tim.stclair@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$hash = optional_param('hash', null, PARAM_TEXT);
$filename = optional_param('filename', null, PARAM_TEXT);

if (empty($hash)) {
  die('No hash provided');
}

// stream the file from the moodle dataroot back to the browser
$file = $CFG->dataroot . DIRECTORY_SEPARATOR . 'blobstorebackend' . DIRECTORY_SEPARATOR . $hash . '.pdf';

if (!file_exists($file)) {
  die('File not found');
}

$name = empty($filename) ? basename($file) : $filename;

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $name . '"');
header('Content-Length: ' . filesize($file));
readfile($file);