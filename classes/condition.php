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
 * Condition main class.
 *
 * @package   availability_courseenrollmentcount
 * @copyright 2023 Tina John
 * @author    Tina John <johnt.22.tijo@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_courseenrollmentcount;

use \completion_info;
use \core_availability\info;
use \coding_exception;
use \stdClass;


class condition extends \core_availability\condition {

    /** @var int nrofcourseenrollment  */
    private $nrofcourseenrollment;
    /** @var string cpmop 0 = = 1 = < 2 > 3 <= 4 >=  */
    private $cpmop;
    /** @var boolean */
    private $showit;

    /**
     * Constructor.
     *
     * @param stdClass $structure Data structure from JSON decode
     * @throws coding_exception If invalid data.
     */
    public function __construct($structure) {
        // is there a number
        if (!property_exists($structure, 'cnt')) {
            $this->nrofcourseenrollment = 7;
        } else if (is_int($structure->cnt)) {
            $this->nrofcourseenrollment = $structure->cnt;
        } else {
            throw new coding_exception('Invalid number for course completions' . $structure->cnt);
        }
        // is there an operator
        if (!property_exists($structure, 'cpmop')) {
            $this->cpmop = 1;
        } else if (is_int($structure->cpmop)) {
            $this->cpmop = $structure->cpmop;
        } else {
            throw new coding_exception('Invalid operator for numbers of course completion');
        }
    }

    /**
     * Saves tree data back to a structure object.
     *
     * @return stdClass Structure object (ready to be made into JSON format)
     */
    public function save() {
        return (object)['type' => 'courseenrollmentcount', 
                        'cpmop' => $this->cpmop, 
                        'cnt' => $this->nrofcourseenrollment];
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $coursecompleted default empty string
     * @return stdClass Object representing condition
     */
    public static function get_json() {
        return (object)['type' => 'courseenrollmentcount', 
                        'cpmop' => $this->cpmop, 
                        'cnt' => $this->nrofcourseenrollment];
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     *
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @param bool $grabthelot Performance hint: if true, caches information
     *   required for all course-modules, to make the front page and similar
     *   pages work more quickly (works only for current user)
     * @param int $userid User ID to check availability for
     * @return bool True if available
     */
    public function is_available($not, info $info, $grabthelot, $userid) {
        global $USER, $DB;
        // $completioninfo = new \completion_info($info->get_course());
        // $allow = $completioninfo->is_course_complete($userid != $USER->id ? $userid : $USER->id);
        // unset($completioninfo);
        //$ncomplcourse = $DB->count_records_sql("SELECT COUNT(*) FROM {course_completions} WHERE userid = ?", [$USER->id]);

        // User ID for which you want to count course enrollments
        $userId = $USER->id;

        // SQL query to count course enrollments
        $sql = "SELECT COUNT(*) FROM {enrol} e
                INNER JOIN {user_enrolments} ue ON e.id = ue.enrolid
                WHERE ue.userid = ?";

        // Execute the SQL query
        $enrollmentCount = $DB->count_records_sql($sql, [$userId]);

        // $enrollmentCount now contains the number of course enrollments for the user

        if($enrollmentCount >= $this->nrofcourseenrollment) {
            $allow = TRUE;
        } else {
            $allow = FALSE;
        }
        if ($not) {
            $allow = !$allow;
        }
        $this->showit = $allow;
        return $allow;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param bool $not Set true if we are inverting the condition
     * @param info $info Item we're checking
     * @return string Information string (for admin) about all restrictions on
     *   this item
     */
    public function get_description($full, $not, info $info) {
        return get_string($not ? 'getdescriptionnot' : 'getdescription', 'availability_courseenrollmentcount', $this->nrofcourseenrollment);
    }

    /**
     * Obtains a representation of the options of this condition as a string,
     * for debugging.
     *
     * @return string Text representation of parameters
     */
    protected function get_debug_string() {
        return true ? '#' . 'True' : 'False';
    }
}
