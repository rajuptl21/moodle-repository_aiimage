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

namespace core_ai\aiactions;

require_once dirname(__FILE__) . '/responses/response_edit_image.php';

/**
 * Class edit_image
 *
 * @package    core_ai
 * @copyright  2025 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_image extends generate_image {
    /**
     * Create a new instance of the edit_image action.
     *
     * It’s responsible for performing any setup tasks,
     * such as getting additional data from the database etc.
     *
     * @param int $contextid The context id.
     * @param int $userid The user id making the request.
     * @param string $prompttext The prompt text used to generate the image.
     * @param string $quality The quality of the generated image.
     * @param string $aspectratio The aspect ratio of the generated image.
     * @param int $numimages The number of images to generate.
     * @param string $style The visual style of the generated image.
     * @param \stored_file $stored_file The file to edit.
     */
    public function __construct(
        int $contextid,
        /** @var int The user id requesting the action. */
        protected int $userid,
        /** @var string The prompt text used to generate the image */
        protected string $prompttext,
        /** @var string The quality of the generated image */
        protected string $quality,
        /** @var string The aspect ratio of the generated image */
        protected string $aspectratio,
        /** @var int The number of images to generate */
        protected int $numimages,
        /** @var string The visual style of the generated image */
        protected string $style,
        /** @var \stored_file The file to edit */
        protected \stored_file $stored_file
    ) {
        base::__construct($contextid);
    }

    /**
     * Get file to edit
     * @return \stored_file
     */
    public function get_storedfile(): \stored_file {
        return $this->stored_file;
    }

    /**
     * Get the class name of the response object.
     *
     * @return string The class name of the response object.
     */
    public static function get_response_classname(): string {
        return responses\response_edit_image::class;
    }

    /**
     * Return the correct table name for the action.
     *
     * @return string The correct table name for the action.
     */
    protected function get_tablename(): string {
        // Table name should always be in this format.
        return 'ai_action_generate_image';
    }
}
