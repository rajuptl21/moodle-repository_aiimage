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

/**
 * Main repository class for Cloud Poodll AI image generator.
 *
 * @package    repository_aiimage
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core repository base lives at /public/repository/lib.php relative to public dirroot.
// In this migrated structure $CFG->dirroot already points to the public root (see public/lib/setup.php).
require_once($CFG->dirroot . '/repository/lib.php');

use repository_aiimage\constants;
use repository_aiimage\utils;
use repository_aiimage\imagegen;

/**
 * Repository implementation for generating images from prompts.
 */
class repository_aiimage extends repository
{

    /**
     * @var imagegen|null imagegen instance
     */
    protected $_imagegen = null;

    /**
     * Supported return types.
     * @return int
     */
    public function supported_returntypes()
    {
        return FILE_INTERNAL;
    }

    public function supported_filetypes()
    {
        return array('web_image');
    }

    /**
     * Enable the global search (used here as a prompt input field).
     * @return bool
     */
    public function global_search()
    {
        return false; // Enables the search box used as prompt input.
    }

    /**
     * Indicates this repository uses Moodle files (draft area).
     * @return bool
     */
    public function has_moodle_files()
    {
        return true;
    }

    public function get_listing($path = '', $page = 0)
    {

        global $USER, $OUTPUT;

        // Empty List
        $list = [
            'list' => [],
            'manage' => false,
            'dynload' => true,
            'nologin' => true,
            'nosearch' => false,
            'issearchresult' => false,
            'path' => [],
        ];
        return $list;
    }

    public function print_login()
    {
        return $this->get_listing();
    }

    /**
     * Add Plugin settings input to Moodle form.
     *
     * @param MoodleQuickForm $mform Moodle form (passed by reference)
     * @param string $classname repository class name
     */
    public static function type_config_form($mform, $classname = 'repository')
    {
        global $CFG, $OUTPUT;

        parent::type_config_form($mform);

        $strrequired = get_string('required');

        $actionclass = \core_ai\aiactions\generate_image::class;
        if (class_exists(\core_ai\manager::class)) {
            $manager = \core\di::get(\core_ai\manager::class);
            $allproviders = $manager->get_providers_for_actions([$actionclass], true);
            if (!empty($allproviders[$actionclass])) {
                foreach ($allproviders[$actionclass] as $aiprovider) {
                    $aiproviderrecord = $aiprovider->to_record();
                    $options[$aiproviderrecord->id] = $aiproviderrecord->name;
                }
            }
        }

        $options[constants::CLOUDPOODLL_OPTION] = get_string('provider:cloudpoodll', constants::M_COMPONENT);

        // API Provider.
        $mform->addElement('select', 'apiprovider', get_string('apiprovider', constants::M_COMPONENT), $options);
        $mform->setType('apiprovider', PARAM_INT);
        $mform->setDefault('apiprovider', constants::CLOUDPOODLL_OPTION);
        $mform->addHelpButton('apiprovider', 'apiprovider', constants::M_COMPONENT);

        // API User.
        $mform->addElement('text', 'apiuser', get_string('apiuser', constants::M_COMPONENT));
        $mform->setType('apiuser', PARAM_TEXT);
        $mform->addHelpButton('apiuser', 'apiuser', constants::M_COMPONENT);
        $mform->hideIf('apiuser', 'apiprovider', 'neq', constants::CLOUDPOODLL_OPTION);

        // API Secret.
        $mform->addElement('text', 'apisecret', get_string('apisecret', constants::M_COMPONENT));
        $mform->setType('apisecret', PARAM_TEXT);
        $mform->addHelpButton('apisecret', 'apisecret', constants::M_COMPONENT);
        $mform->hideIf('apisecret', 'apiprovider', 'neq', constants::CLOUDPOODLL_OPTION);

        // Build our Poodll Credentials helper display.
        $cloudpoodllapiuser = get_config(constants::M_COMPONENT, 'apiuser');
        $cloudpoodllapisecret = get_config(constants::M_COMPONENT, 'apisecret');
        $showbelowapisecret = '';
        // If we have an API user and secret we fetch token.
        if (!empty($cloudpoodllapiuser) && !empty($cloudpoodllapisecret)) {
            $tokeninfo = utils::fetch_token_for_display($cloudpoodllapiuser, $cloudpoodllapisecret);
            $showbelowapisecret = $tokeninfo;
            // if we have no API user and secret we show a "fetch from elsewhere on site" or "take a free trial" link
        } else {
            $amddata = [];
            $cpcomponents = [
                'filter_poodll',
                'qtype_cloudpoodll',
                'mod_readaloud',
                'mod_wordcards',
                'mod_solo',
                'mod_englishcentral',
                'mod_pchat',
                'atto_cloudpoodll',
                'tinymce_cloudpoodll',
                'tiny_poodll',
                'assignsubmission_cloudpoodll',
                'assignfeedback_cloudpoodll',
            ];

            foreach ($cpcomponents as $cpcomponent) {
                switch ($cpcomponent) {
                    case 'filter_poodll':
                        $apiusersetting = 'cpapiuser';
                        $apisecretsetting = 'cpapisecret';
                        break;
                    case 'mod_englishcentral':
                        $apiusersetting = 'poodllapiuser';
                        $apisecretsetting = 'poodllapisecret';
                        break;
                    default:
                        $apiusersetting = 'apiuser';
                        $apisecretsetting = 'apisecret';
                }
                $cloudpoodllapiuser = get_config($cpcomponent, $apiusersetting);
                if (!empty($cloudpoodllapiuser)) {
                    $cloudpoodllapisecret = get_config($cpcomponent, $apisecretsetting);
                    if (!empty($cloudpoodllapisecret)) {
                        $amddata['apiuser'] = $cloudpoodllapiuser;
                        $amddata['apisecret'] = $cloudpoodllapisecret;
                        break;
                    }
                }
            }
            $showbelowapisecret = $OUTPUT->render_from_template(constants::M_COMPONENT . '/managecreds', $amddata);
        }


        // Add a link to create a new Poodll account if the API user is not set.
        $mform->addElement(
            'static',
            'poodllcredshelper',
            '',
            $showbelowapisecret,
        );
        $mform->hideIf('poodllcredshelper', 'apiprovider', 'neq', constants::CLOUDPOODLL_OPTION);
        $mform->hideIf('poodllcredshelper', 'apiuser', 'neq', '');

        // Cloud Poodll Server.
        $mform->addElement('text', 'cloudpoodllserver', get_string('cloudpoodllserver', constants::M_COMPONENT));
        $mform->setType('cloudpoodllserver', PARAM_URL);
        $mform->setDefault('cloudpoodllserver', 'https://cloud.poodll.com');
        $mform->addHelpButton('cloudpoodllserver', 'cloudpoodllserver', constants::M_COMPONENT);
        $mform->hideIf('cloudpoodllserver', 'apiprovider', 'neq', constants::CLOUDPOODLL_OPTION);

        // AWS Region.
        $regions = utils::get_region_options();
        $mform->addElement('select', 'awsregion', get_string('awsregion', constants::M_COMPONENT), $regions);
        $mform->setDefault('awsregion', 'useast1');
        $mform->addHelpButton('awsregion', 'awsregion', constants::M_COMPONENT);
        $mform->hideIf('awsregion', 'apiprovider', 'neq', constants::CLOUDPOODLL_OPTION);
    }

    /**
     * Option names of AI Image plugin.
     *
     * @inheritDocs
     */
    public static function get_type_option_names()
    {
        return [
            'apiprovider',
            'cloudpoodllserver',
            'apiuser',
            'apisecret',
            'awsregion',
            'pluginname',
        ];
    }

    /**
     * Render the search (prompt) input box.
     * @return string
     */
    public function print_search()
    {
        global $OUTPUT;

        $currentprompt = optional_param('s', '', PARAM_TEXT);
        $currentstyle = optional_param('imagetype', 'flat vector illustration', PARAM_TEXT);
        $currentimage = optional_param('selectedimage', '', PARAM_TEXT);

        $fileslist = $this->fetch_files_list();
        $imagelist = [];
        if ($canedit = $this->can_edit_image()) {
            foreach ($fileslist['list'] as $fileinfo) {
                $imagelist[] = [
                    'title' => $fileinfo['title'] ?? '',
                    'thumbnail' => $fileinfo['thumbnail'] ?? '',
                    'realthumbnail' => $fileinfo['realthumbnail'] ?? '',
                    'selected' => ($currentimage !== '' && ($fileinfo['title'] ?? '') === $currentimage),
                ];
            }
        }

        $styles = [];
        foreach ($this->fetch_image_options() as $option) {
            $styles[] = [
                'value' => $option->value,
                'label' => $option->label,
                'selected' => ($option->value === $currentstyle),
            ];
        }

        $context = [
            'formid' => 'aiimage-imagegen-' . uniqid('', true),
            'prompt' => $currentprompt,
            'imagestyles' => $styles,
            'images' => $imagelist,
            'showclearoption' => !empty($imagelist),
            'nocurrent' => ($currentimage === ''),
            'canedit' => $canedit,
            'sesskey' => sesskey()
        ];

        $out = html_writer::start_div('repository_aiimage_image_prompt_wrapper');
        $out .= $OUTPUT->render_from_template('repository_aiimage/imagegenform', $context);
        $out .= html_writer::end_div();
        return $out;
    }

    /**
     * @return imagegen
     */
    public function get_imagegen()
    {
        if (!isset($this->_imagegen)) {
            $this->_imagegen = new imagegen($this);
        }
        return $this->_imagegen;
    }

    public function can_edit_image()
    {
        return $this->get_imagegen()->can_edit_image();
    }

    /**
     * Generate an image from the prompt (search text) and present it as a single result.
     * @param string $searchtext
     * @param int $page
     * @return array
     */
    public function search($searchtext, $page = 0)
    {
        global $USER;

        // Default results structure.
        $results = [
            'list' => [],
            'page' => $page,
            'pages' => 1,
        ];

        // Get the submitted prompt.
        $prompt = trim($searchtext ?? '');
        $selectedimage = null;

        // No prompt, no results.
        if ($prompt === '') {
            return $results;
        }

        // Get the selected image style.
        $imagetype = optional_param('imagetype', 'flat vector illustration', PARAM_TEXT);

        // Get the selected image  (or no image selected to generate new ).
        $selectedimagefilename = optional_param('selectedimage', '', PARAM_TEXT);
        if (!empty($selectedimagefilename) && $this->can_edit_image()) {
            $selectedimage = $this->fetch_file_by_filename($selectedimagefilename);
            // If this fails that is bad, so just return no results
            if (!$selectedimage) {
                return $results;
            }
        }

        $draftid = file_get_unused_draft_itemid();

        // Create or edit image via function.
        if (!empty($selectedimagefilename) && !empty($selectedimage)) {
            $filename = $selectedimagefilename;
            $fileinfo = $this->get_imagegen()->edit_image(
                $prompt,
                $draftid,
                $selectedimage,
                $selectedimagefilename
            );

        } else {
            $prompt = $prompt . '[NB The image style is: ' . $imagetype . ']';
            $filename = 'imagegen_' . time() . '.png';
            $fileinfo = $this->get_imagegen()->generate_image(
                $prompt,
                $draftid,
                $filename
            );
        }

        // No fileinfo, no results.
        if (!$fileinfo) {
            return $results;
        }

        // Build the source reference for Moodle file API.
        $sourceinfo = [
            'contextid' => context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $fileinfo['draftitemid'],
            'filepath' => '/',
            'filename' => $fileinfo['filename'],
        ];
        $source = base64_encode(json_encode($sourceinfo));

        $results['list'][] = [
            'title' => $fileinfo['filename'],
            'shorttitle' => $fileinfo['filename'],
            'thumbnail' => $fileinfo['drafturl'],
            'thumbnail_height' => 256,
            'thumbnail_width' => 256,
            'source' => $source,
            'url' => $fileinfo['drafturl'],
        ];

        // This prevents showing the really big search results box. They can press a button to go back.
        $results['nosearch'] = true;

        // Return the results.
        return $results;
    }


    /**
     * Fetch available image style options.
     * @return array
     */
    public function fetch_image_options()
    {
        return [
            (object) ['value' => 'flat vector illustration', 'label' => get_string('imagetype_flatvectorillustration', constants::M_COMPONENT)],
            (object) ['value' => 'cartoon', 'label' => get_string('imagetype_cartoon', constants::M_COMPONENT)],
            (object) ['value' => 'photorealistic', 'label' => get_string('imagetype_photorealistic', constants::M_COMPONENT)],
            (object) ['value' => 'digital painting', 'label' => get_string('imagetype_digitalpainting', constants::M_COMPONENT)],
            (object) ['value' => 'line drawing', 'label' => get_string('imagetype_linedrawing', constants::M_COMPONENT)],
            (object) ['value' => '3d render', 'label' => get_string('imagetype_3drender', constants::M_COMPONENT)],
            (object) ['value' => 'infographic', 'label' => get_string('imagetype_infographic', constants::M_COMPONENT)],
        ];
    }

    /**
     * Fetch a file by filename from user draft area.
     * @param string $filename
     * @return \stored_file|null
    */
    public function fetch_file_by_filename($filename)
    {
        global $USER, $OUTPUT;

        $itemid = optional_param('itemid', 0, PARAM_INT);
        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        $file = $fs->get_file(
            $context->id,
            'user',
            'draft',
            $itemid,
            '/',
            $filename
        );
        return $file;
    }

    /**
     *
     * Fetch the list of existing mages from user draft area.
     * @return array
     */
    public function fetch_files_list()
    {
        global $USER, $OUTPUT;

        $result = [
            'list' => [],
        ];

        $itemid = optional_param('itemid', 0, PARAM_INT);
        if (empty($itemid)) {
            return $result;
        }

        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id,
            'user',
            'draft',
            $itemid,
            'timemodified DESC',
            false,
            0,
            0
        );

        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            // continue if the file extension is not png / jpeg / jpg /webp
            $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
            if (!in_array($extension, ['png', 'jpeg', 'jpg', 'webp'])) {
                continue;
            }


            $fileurl = moodle_url::make_draftfile_url($itemid, $file->get_filepath(), $file->get_filename());
            $entry = [
                'title' => $file->get_filename(),
                'thumbnail' => $OUTPUT->image_url(file_file_icon($file))->out(false),
            ];

            if ($imageinfo = $file->get_imageinfo()) {
                $entry['realthumbnail'] = $fileurl->out(false, [
                    'preview' => 'thumb',
                    'oid' => $file->get_timemodified(),
                ]);
            }

            $result['list'][] = $entry;
        }

        return $result;
    }

    /**
     * No specific init needed.
     * @return bool
     */
    public static function plugin_init()
    {
        return true;
    }

    /**
     * No external auth required.
     * @return bool
     */
    public function check_login()
    {
        return true; // False shows print_login form.
    }
}
