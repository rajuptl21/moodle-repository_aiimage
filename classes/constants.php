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

namespace repository_aiimage;

defined('MOODLE_INTERNAL') || die();


/**
 * Class constants
 *
 * @package    repository_aiimage
 * @copyright  2025 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class constants {

    // Component name, db tables, strings that are fixed and used around the plugin
    const M_COMPONENT = 'repository_aiimage';

    /**
     * Shortname of the plugin
     */
    const M_SHORTNAME = 'aiimage';

    /**
     * Default CloudPoodll server
     */
    const M_DEFAULT_CLOUDPOODLL = "cloud.poodll.com";

    /**
     * Option value for CloudPoodll API provider
     */
    const CLOUDPOODLL_OPTION = -1;

    /**
     * Plugin settings page URL
     */
    const M_PLUGINSETTINGS = 'admin/repository.php?action=edit&repos=aiimage';

}
