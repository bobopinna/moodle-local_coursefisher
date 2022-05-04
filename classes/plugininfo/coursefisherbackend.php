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
 * Subplugin info class.
 *
 * @package   local_coursefisher
 * @copyright 2014 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefisher\plugininfo;

use core\plugininfo\base;

/**
 * Subplugin info class.
 *
 * @package   local_coursefisher
 * @copyright 2014 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursefisherbackend extends base {

    /**
     * Do not allow users to uninstall backend plugins as it could cause coursefisher to break.
     *
     * @return bool
     */
    public function is_uninstall_allowed() {
        return false;
    }
}
