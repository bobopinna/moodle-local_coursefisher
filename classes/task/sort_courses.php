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

namespace local_coursefisher\task;

use core\task\adhoc_task;

/**
 * Class containing the adhoc task for sort courses.
 *
 * @package   local_coursefisher
 * @copyright 2022 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sort_courses extends adhoc_task {
    /**
     * Run the sorting task.
     */
    public function execute() {
        $info = $this->get_custom_data();
        $categoryid = $info->categoryid;
        $sortcoursesby = $info->sortcoursesby;

        $category = \core_course_category::get($categoryid);
        if (!empty($category)) {
            \core_course\management\helper::action_category_resort_courses($category, $sortcoursesby);
        }
    }

}
