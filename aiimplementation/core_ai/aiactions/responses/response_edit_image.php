<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace core_ai\aiactions\responses;

use core_ai\aiactions\responses\response_generate_image;

/**
 * Class response_edit_image
 *
 * @package    core_ai
 * @copyright  2025 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class response_edit_image extends response_generate_image {

    /**
     * Constructor.
     *
     * @param bool $success The success status of the action.
     * @param int $errorcode Error code. Must exist if success is false.
     * @param string $errormessage Error message. Must exist if success is false
     */
    public function __construct(
        bool $success,
        int $errorcode = 0,
        string $errormessage = '',
    ) {
        response_base::__construct(
            success: $success,
            actionname: 'edit_image',
            errorcode: $errorcode,
            errormessage: $errormessage,
        );
    }

}