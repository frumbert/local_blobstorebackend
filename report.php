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
 * Report on stored inputs for a course
 *
 * @package     local_blobstorebackend
 * @copyright   2025 Tim St Clair
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once(__DIR__ . '/classes/course_select_form.php'); // Form class for course selection.
require_once(__DIR__ . '/locallib.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context); // Ensure the user is a site admin.

// Initialize variables.
$selectedcourseid = optional_param('courseid', 0, PARAM_INT); // Get selected course ID from form submission or default to 0.
$course = null;

$download = optional_param('download', false, PARAM_ALPHAEXT);
$sifirst = optional_param('sifirst', 'all', PARAM_NOTAGS);
$silast  = optional_param('silast',  'all', PARAM_NOTAGS);
$displayoptions = ['sifirst' => $sifirst, 'silast' => $silast];

$tsort = optional_param('tsort', 'firstnamelastname', PARAM_ALPHAEXT);
$tdir = optional_param('tdir', 4, PARAM_INT); // 4 is asc, 3 is desc
$dir = $tdir === 4 ? SORT_ASC : SORT_DESC;  // for my own clarity

// If a course is selected, fetch its details.
if ($selectedcourseid) {
    $course = $DB->get_record('course', ['id' => $selectedcourseid], '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    // require_capability('local/blobstorebackend:manage', $context);
}

// Set up the page (this must happen before any output).
$PAGE->set_context($context);
$PAGE->set_url('/local/blobstorebackend/report.php', ['courseid' => $selectedcourseid, 'sifirst' => $sifirst, 'silast' => $silast]);
$PAGE->set_title(get_string('reportheader', 'local_blobstorebackend'));
$PAGE->set_heading(get_string('name', 'local_blobstorebackend'));
$PAGE->set_pagelayout('admin');

// Output starts here.
if (empty($download)) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('reportheader', 'local_blobstorebackend'));

    // Display the course selection form.
    $form = new \blobstorebackend\course_select_form(null, ['selectedcourseid' => $selectedcourseid]);
    $form->display();
}

// If a course is selected, display the SCORM report.
if ($course) {
    $courseObj = new \core_course_list_element($course);
    if (empty($download)) echo $OUTPUT->heading(format_string($course->fullname), 3);

    // Fetch SCORM activities in the course.
    $sql = "SELECT cm.id AS cmid, scorm.name
              FROM {course_modules} cm
              JOIN {modules} m ON m.id = cm.module
              JOIN {scorm} scorm ON scorm.id = cm.instance
             WHERE cm.course = :courseid AND m.name = 'scorm'";
    $scorms = $DB->get_records_sql($sql, ['courseid' => $courseObj->id]);
    $has_multiple_scos = count($scorms) > 1;
    if ($has_multiple_scos) {
        $columns = array('content','page','firstnamelastname','email','question','answer');
        $headers = array(get_string('content'),get_string('page'),get_string('fullnameuser'),get_string('email'),get_string('question'),get_string('answer'));
    } else {
        $columns = array('page','firstnamelastname','email','question','answer');
        $headers = array(get_string('page'),get_string('fullnameuser'),get_string('email'),get_string('question'),get_string('answer'));
    }

    $sort_index = array_search($tsort, $columns);

    // do we know the riseid?
    $riseid = '';
    foreach (\core_course\customfield\course_handler::create()->get_instance_data($course->id, true) as $field) {
        $fd = new \core_customfield\output\field_data($field);
        $name = $fd->get_shortname();
        $value = $fd->get_value();
        switch ($name) {
            case "rise_identity":
                $riseid = $value;
                break;
        }
    }
// echo html_writer::tag('h3', 'rise-id=' . $riseid);
// echo html_writer::empty_tag('hr');

    if (empty($download)) {
        $table = new \flexible_table('local-blobstorebackend-report');

        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->define_baseurl($PAGE->url);

        $table->sortable(true);
        $table->collapsible(true);
        $table->set_attribute('class', 'generaltable generalbox');
        $table->column_class('content', 'bold');
        $table->column_class('page', 'bold');

        $table->setup();

        echo $OUTPUT->initials_bar($sifirst, 'firstinitial', get_string('firstname'), 'sifirst', $PAGE->url);
        echo $OUTPUT->initials_bar($silast,  'lastinitial',  get_string('lastname'),  'silast',  $PAGE->url);
    }

    if ($scorms) {

        if (!empty($download)) {
            $shortname = format_string($course->shortname, true, array('context' => $context));
            $filename = clean_filename($shortname); //  ".format_string($scorm->name, true));
        }

        foreach ($scorms as $scorm) {
            // echo html_writer::tag('h3', format_string($scorm->name));
            // Add logic to display additional SCORM report data here.

            // Get active enrolled users in the course.
            $allnames = \core_user\fields::get_name_fields();
            $enrolledusers = get_enrolled_users(context_course::instance($courseObj->id), '', 0, 'u.id, u.username, u.email, u.department, ' . implode(',', $allnames));

            // Apply A-Z initial filters.
            if ($sifirst !== 'all') {
                $enrolledusers = array_filter($enrolledusers, fn($u) => strtoupper(substr($u->firstname, 0, 1)) === strtoupper($sifirst));
            }
            if ($silast !== 'all') {
                $enrolledusers = array_filter($enrolledusers, fn($u) => strtoupper(substr($u->lastname, 0, 1)) === strtoupper($silast));
            }

            $rows = [];
            foreach ($enrolledusers as $user) {
                // Construct the digest for the user.
                $digest = blobstoreencoding("{$user->lastname}, {$user->firstname}{$user->username}");

                $records = local_blobstorebackend_get_all_data($riseid, null, $digest);
                if ($records) foreach ($records as $record) {
                    $userdata = json_decode($record->data, true);
                    if ($userdata) {
                        $row = [];
                        $page = "";
                        $question = "";
                        $answer = "";
                        foreach ($userdata as $key => $value) {
                            switch ($key) {
                                case "page": $page = $value; break;
                                case "question": $question = $value; break;
                                case "answer": $answer = $value; break;
                                case "course": break;
                            }
                        }
                        if ($has_multiple_scos) $row[] = $scorm->name;
                        $row[] = $page;
                        $row[] = fullname($user);//  $user->firstname;
                        //$row[] = $user->lastname;
                        $row[] = $user->email;
                        $row[] = $question;
                        $row[] = (empty($download)) ? $answer : str_replace(["\r", "\n"], '', $answer);

                        // if (empty($download)) $table->add_data($row);
                        $rows[] = $row;
                    }
                }
            }
        }

        // since we have virtual columns that don't come from sql, we have to sort the rows ourselves
        sort_by_column($rows, $sort_index, $dir);

        // either download or show the results
        if (empty($download)) {
            foreach ($rows as $row) {
                $table->add_data($row);
            }
            $table->finish_output($rows);

            if (count($rows)) {

                echo \html_writer::start_tag('table', array('class' => 'boxaligncenter')).\html_writer::start_tag('tr');
                echo \html_writer::end_tag('td');
                echo \html_writer::start_tag('td');
                echo $OUTPUT->single_button(new \moodle_url($PAGE->url,
                                                            array('download' => 'Excel') + $displayoptions),
                                                            get_string('downloadexcel'));
                echo \html_writer::end_tag('td');
                echo \html_writer::start_tag('td');
                echo $OUTPUT->single_button(new \moodle_url($PAGE->url,
                                                            array('download' => 'CSV') + $displayoptions),
                                                            get_string('downloadtext'));
                echo \html_writer::end_tag('td');
                echo \html_writer::end_tag('tr').\html_writer::end_tag('table');
            }

        } else if ($download == 'Excel') {
                require_once("$CFG->libdir/excellib.class.php");

                $filename .= ".xls";
                $workbook = new \MoodleExcelWorkbook("-");
                $workbook->send($filename);
                $sheettitle = get_string('report', 'scorm');
                $myxls = $workbook->add_worksheet($sheettitle);
                // Format types.
                $format = $workbook->add_format();
                $format->set_bold(0);
                $formatbc = $workbook->add_format();
                $formatbc->set_bold(1);
                $formatbc->set_align('center');
                $formatb = $workbook->add_format();
                $formatb->set_bold(1);
                $formaty = $workbook->add_format();
                $formaty->set_bg_color('yellow');
                $formatc = $workbook->add_format();
                $formatc->set_align('center');
                $formatr = $workbook->add_format();
                $formatr->set_bold(1);
                $formatr->set_color('red');
                $formatr->set_align('center');
                $formatg = $workbook->add_format();
                $formatg->set_bold(1);
                $formatg->set_color('green');
                $formatg->set_align('center');

                $colnum = 0;
                foreach ($headers as $item) {
                    $myxls->write(0, $colnum, $item, $formatbc);
                    $colnum++;
                }
                $rownum = 1;
                foreach ($rows as $row) {
                    $colnum = 0;
                    foreach ($row as $item) {
                        $myxls->write($rownum, $colnum, $item, $format);
                        $colnum++;
                    }
                    $rownum++;
                }
                $workbook->close();

        } else if ($download == 'CSV') {
            $csvexport = new \csv_export_writer();
            $csvexport->set_filename($filename, ".txt");
            foreach ($headers as $header) {
                $csvexport->add_data($header);
            }
            foreach ($rows as $row) {
                $csvexport->add_data($row);
            }
            $csvexport->download_file();
            exit;
        }

        $table->finish_output();
    } else {
        echo $OUTPUT->notification(get_string('noscormactivities', 'local_blobstorebackend'), 'info');
    }
}

if (empty($download)) {
    echo $OUTPUT->footer();
}

function get_db() {
  global $CFG;
  return $CFG->dataroot . DIRECTORY_SEPARATOR . 'blobstorebackend' . DIRECTORY_SEPARATOR;
}

// match the base64/encoding used by the front end js
function blobstoreencoding($value) {
    $step1 = rawurlencode($value);
    $step2 = base64_encode($step1);
    $step3 = str_replace(['+', '/', '='], ['-', '_', ''], $step2);
    return $step3;
}

function sort_by_column(&$array, $columnIndex, $direction = SORT_ASC) {
    usort($array, function ($a, $b) use ($columnIndex, $direction) {
        if ($a[$columnIndex] == $b[$columnIndex]) {
            return 0;
        }
        return ($direction === SORT_ASC)
            ? ($a[$columnIndex] < $b[$columnIndex] ? -1 : 1)
            : ($a[$columnIndex] > $b[$columnIndex] ? -1 : 1);
    });
}