<?php
namespace blobstorebackend;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class course_select_form extends \moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Fetch all visible courses with at least one SCORM activity.
        $sql = "SELECT c.id, c.fullname
                  FROM {course} c
                  JOIN {course_modules} cm ON cm.course = c.id
                  JOIN {modules} m ON m.id = cm.module
                 WHERE c.visible = 1 AND m.name = 'scorm'
              GROUP BY c.id, c.fullname
              ORDER BY c.fullname";
        $courses = $DB->get_records_sql($sql);

        // Prepare course options for the select element.
        $courseoptions = [];
        foreach ($courses as $course) {
            $courseoptions[$course->id] = $course->fullname;
        }

        // Add a select element for courses.
        $mform->addElement('select', 'courseid', get_string('selectcourse', 'local_blobstorebackend'), $courseoptions);
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $this->_customdata['selectedcourseid'] ?? 0);
        $mform->addRule('courseid', null, 'required', null, 'client');

        // Add action buttons.
        $this->add_action_buttons(true, get_string('submit'));
    }
}