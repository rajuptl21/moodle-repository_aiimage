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

use repository_aiimage\constants;
use repository_aiimage\utils;
use stored_file;

/**
 * Class imagegen
 *
 * @package    repository_aiimage
 * @copyright  2025 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class imagegen
{

    /**
     * @var bool|object
     */
    protected $conf = false;

    /**
     * imagegen constructor.
     */
    function __construct()
    {
        global $DB;
        $this->conf = get_config(constants::M_SHORTNAME);
    }


    /**
     * @param string $imagedata
     * @return string
     */
    public function make_image_smaller($imagedata)
    {
        global $CFG;
        require_once($CFG->libdir . '/gdlib.php');

        if (empty($imagedata)) {
            return $imagedata;
        }

        // Create temporary files for resizing
        $randomid = uniqid();
        $temporiginal = $CFG->tempdir . '/aigen_orig_' . $randomid;
        file_put_contents($temporiginal, $imagedata);

        // Resize to reasonable dimensions
        $resizedimagedata = \resize_image($temporiginal, 500, 500, true);

        if (!$resizedimagedata) {
            // If resizing fails, use the original image data
            $resizedimagedata = $imagedata;
        }

        // Clean up temporary file
        if (file_exists($temporiginal)) {
            unlink($temporiginal);
        }

        return $resizedimagedata;
    }

    /*
     * Generates structured data using the CloudPoodll service.
     *
     * @param string $prompt The prompt to generate data for.
     * @param int $draftid The draft item ID to associate the new file with.
     * @param stored_file $file The stored_file object containing the image to edit.
     * @param string $filename The desired filename for the new draft file.
     * @return array|false Returns an array with draft file URL, draft item ID, term ID, and base64 data, or false on failure.
     */
    public function edit_image($prompt, $draftid, $file, $filename)
    {
        $providerrespose = $this->call_ai_provider_edit_image($prompt, $draftid, $file, $filename);
        if (!is_null($providerrespose)) {
            return $providerrespose;
        }
        $params = $this->prepare_edit_image_payload($prompt, $file);
        if ($params) {
            $url = utils::get_cloud_poodll_server() . "/webservice/rest/server.php";
            $resp = utils::curl_fetch($url, $params, true);
            $base64data = $this->process_generate_image_response($resp);
            if ($base64data) {
                // Generate draft file
                $filerecord = $this->base64ToFile($base64data, $draftid, $filename);
                if ($filerecord) {
                    $draftid = $filerecord['itemid'];
                    $draftfileurl = \moodle_url::make_draftfile_url(
                        $draftid,
                        $filerecord['filepath'],
                        $filerecord['filename'],
                        false
                    );
                    return [
                        'drafturl' => $draftfileurl->out(false),
                        'draftitemid' => $draftid,
                        'filename' => $filename,
                        'error' => false,
                    ];
                }
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * Generates structured data using the CloudPoodll service.
     *
     * @param string $prompt The prompt to generate data for.
     * @param int $draftid The draft item ID to associate the new file with.
     * @param string $filename The desired filename for the new draft file.
     * @return array|false Returns an array with draft file URL, draft item ID, term ID, and base64 data, or false on failure.
     */
    public function generate_image($prompt, $draftid, $filename)
    {
        $providerrespose = $this->call_ai_provider_create_image($prompt, $draftid, $filename);
        if (!is_null($providerrespose)) {
            return $providerrespose;
        }
        $params = $this->prepare_generate_image_payload(($prompt));
        if ($params) {
            $url = utils::get_cloud_poodll_server() . "/webservice/rest/server.php";
            $resp = utils::curl_fetch($url, $params);
            $base64data = $this->process_generate_image_response($resp);
            if ($base64data) {
                // Generate draft file
                $filerecord = $this->base64ToFile($base64data, $draftid, $filename);
                if ($filerecord) {
                    $draftid = $filerecord['itemid'];
                    $draftfileurl = \moodle_url::make_draftfile_url(
                        $draftid,
                        $filerecord['filepath'],
                        $filerecord['filename'],
                        false,
                    );
                    return [
                        'drafturl' => $draftfileurl->out(false),
                        'draftitemid' => $draftid,
                        'filename' => $filename,
                        'error' => false,
                    ];
                }
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * @param string $base64data
     * @param int $draftid
     * @param string $filename
     * @return array|false
     */
    public function base64ToFile($base64data, $draftid, $filename)
    {
        global $USER;

        if (empty($base64data)) {
            return false;
        }

        $fs = get_file_storage();

        $filerecord = [
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftid,
            'filepath' => '/',
            'filename' => $filename,
        ];

        // Create file content
        $filecontent = base64_decode($base64data);
        //Check its good file content
        if (!$filecontent || !self::validate_image_content($filecontent)) {
            return false;
        }
        try {
            // Check if the file already exists
            $existingfile = $fs->get_file_by_hash(sha1($filecontent));
            if ($existingfile) {
                return $filerecord;
            } else {
                $thefile = $fs->create_file_from_string($filerecord, $filecontent);
                if ($thefile) {
                    return $filerecord;
                } else {
                    return false;
                }
            }
        } catch (\moodle_exception $e) {
            return false; // Handle error "gracefully"
        }
    }

    // Validate base64 to be sure nothing sinister has arrived
    private static function validate_image_content($filecontent) {
        // Check if it's actually an image
        $imageinfo = getimagesizefromstring($filecontent);
        return $imageinfo !== false && in_array($imageinfo[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP]);
    }


    /**
     * @param string $prompt
     * @param stored_file $file
     * @param string|null $token
     * @return array|false
     */
    public function prepare_edit_image_payload($prompt, $file, $token = null)
    {
        global $USER;

        if (!empty($this->conf->apiuser) && !empty($this->conf->apisecret)) {
            if (is_null($token)) {
                $token = utils::fetch_token($this->conf->apiuser, $this->conf->apisecret);
            }
            if (empty($token)) {
                return false;
            }
            if (!($file || !$file instanceof \stored_file)) {
                return false;
            }

            // Fetch base64 data from the storedfile
            $filecontent = $file->get_content();
            $base64data = base64_encode($filecontent);
            if (!$base64data) {
                return false;
            }

            $params["wstoken"] = $token;
            $params["wsfunction"] = 'local_cpapi_call_ai';
            $params["moodlewsrestformat"] = 'json';
            $params['appid'] = 'repository_aiimage';
            $params['action'] = 'edit_image';
            $params["subject"] = $base64data;
            $params["prompt"] = $prompt;
            $params["language"] = "en-US";
            $params["region"] = $this->conf->awsregion;
            $params['owner'] = hash('md5', $USER->username);

            return $params;

        } else {
            return false;
        }
    }

    /**
     * @param string $prompt
     * @param string|null $token
     * @return array|false
     */
    public function prepare_generate_image_payload($prompt, $token = null)
    {
        global $USER;

        if (!empty($this->conf->apiuser) && !empty($this->conf->apisecret)) {
            if (is_null($token)) {
                $token = utils::fetch_token($this->conf->apiuser, $this->conf->apisecret);
            }
            if (empty($token)) {
                return false;
            }

            $params["wstoken"] = $token;
            $params["wsfunction"] = 'local_cpapi_call_ai';
            $params["moodlewsrestformat"] = 'json';
            $params['appid'] = 'repository_aiimage';
            $params['action'] = 'generate_images';
            $params["subject"] = '1';
            $params["prompt"] = $prompt;
            $params["language"] = "en-US";
            $params["region"] = $this->conf->awsregion;
            $params['owner'] = hash('md5', $USER->username);

            return $params;

        } else {
            return false;
        }
    }

    /**
     * @param string $resp
     * @return string|null
     */
    public function process_generate_image_response($resp)
    {
        $respobj = json_decode($resp);
        $ret = new \stdClass();
        if (isset($respobj->returnCode)) {
            $ret->success = $respobj->returnCode == '0' ? true : false;
            $ret->payload = json_decode($respobj->returnMessage);
        } else {
            $ret->success = false;
            $ret->payload = "unknown problem occurred";
        }

        if ($ret && $ret->success) {
            if (isset($ret->payload[0]->url)) {
                $url = $ret->payload[0]->url;
                $rawdata = file_get_contents($url);
                if ($rawdata !== false) {
                    $smallerdata = $this->make_image_smaller($rawdata);
                    $base64data = base64_encode($smallerdata);
                    return $base64data;
                }
            } else if (isset($ret->payload[0]->b64_json)) {
                // If the payload has a base64 encoded image, use that.
                $rawbase64data = $ret->payload[0]->b64_json;
                $rawdata = base64_decode($rawbase64data);
                //Check its good image data
                if (!$rawdata || !self::validate_image_content($rawdata)) {
                    return false;
                }
                $smallerdata = $this->make_image_smaller($rawdata);
                $base64data = base64_encode($smallerdata);
                return $base64data;
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function can_edit_image() {
        $providerid = $this->conf->apiprovider ?? constants::CLOUDPOODLL_OPTION;
        if ($providerid == constants::CLOUDPOODLL_OPTION) {
            return true;
        }
        if (!class_exists(\core_ai\manager::class)) {
            return false;
        }
        $manager = \core\di::get(\core_ai\manager::class);

        $providerinstances = $manager->get_provider_instances(['id' => $providerid]);
        $providerinstance = reset($providerinstances);
        if (empty($providerinstance)) {
            return false;
        }
        $providernamearr = explode('_', $providerinstance->provider, 2);
        $providername = array_pop($providernamearr);
        return in_array(strtolower($providername), [
            'gemini'
        ]);
    }

    /**
     * @param string $prompt
     * @param int $draftid
     * @param string $filename
     * @return array|false|null
     */
    public function call_ai_provider_create_image($prompt, $draftid, $filename) {
        global $USER;
        $context = \context_system::instance();
        $actionclass = \core_ai\aiactions\generate_image::class;
        $providerid = $this->conf->apiprovider ?? constants::CLOUDPOODLL_OPTION;
        if ($providerid > 0 && class_exists(\core_ai\manager::class)) {
            $manager = \core\di::get(\core_ai\manager::class);

            $providerinstances = $manager->get_provider_instances(['id' => $providerid]);
            /** @var \core_ai\provider $providerinstance */
            $providerinstance = reset($providerinstances);
            $providerenabled = !empty($providerinstance) &&
                $manager->is_action_enabled(
                    $providerinstance->provider,
                    $actionclass,
                    $providerinstance->id
                );
            if ($providerenabled) {
                // Prepare the action.
                $paramstructure = [
                    'contextid' => $context->id,
                    'prompttext' => $prompt,
                    'aspectratio' => optional_param('aspectratio', 'square', PARAM_ALPHA),
                    'quality' => optional_param('quality', 'standard', PARAM_ALPHA),
                    'numimages' => optional_param('numimages', 1, PARAM_INT),
                    'style' => optional_param('style', 'natural', PARAM_ALPHA),
                ];
                $action = new $actionclass(
                    contextid: $paramstructure['contextid'],
                    userid: $USER->id,
                    prompttext: $paramstructure['prompttext'],
                    quality: $paramstructure['quality'],
                    aspectratio: $paramstructure['aspectratio'],
                    numimages: $paramstructure['numimages'],
                    style: $paramstructure['style'],
                );

                $reflclass = new \ReflectionClass($manager);
                $reflmethod = $reflclass->getMethod('call_action_provider');
                $reflmethod->setAccessible(true);

                $result = $reflmethod->invoke($manager, $providerinstance, $action);

                $reflmethod2 = $reflclass->getMethod('store_action_result');
                $reflmethod2->setAccessible(true);

                $reflmethod2->invoke($manager, $providerinstance, $action, $result);

                if (!$result->get_success()) {
                    return false;
                }

                $draftfile = $result->get_response_data()['draftfile'] ?? null;
                return $this->process_ai_generated_file($draftfile, $draftid, $filename);
            }
        }
        return null;
    }

    /**
     * @param string $prompt
     * @param int $draftid
     * @param stored_file $file
     * @param string $filename
     * @return array|false|null
     */
    public function call_ai_provider_edit_image($prompt, $draftid, $file, $filename) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/repository/aiimage/aiimplementation/core_ai/aiactions/edit_image.php');
        $context = \context_system::instance();
        $actionclass = \core_ai\aiactions\generate_image::class;
        $providerid = $this->conf->apiprovider ?? constants::CLOUDPOODLL_OPTION;
        if ($providerid > 0 && class_exists(\core_ai\manager::class)) {
            $manager = \core\di::get(\core_ai\manager::class);

            $providerinstances = $manager->get_provider_instances(['id' => $providerid]);
            /** @var \core_ai\provider $providerinstance */
            $providerinstance = reset($providerinstances);
            $providerenabled = !empty($providerinstance) &&
                $manager->is_action_enabled(
                    $providerinstance->provider,
                    $actionclass,
                    $providerinstance->id
                );

            if ($providerenabled) {
                require_once($CFG->dirroot . '/repository/aiimage/aiimplementation/'.$providerinstance->provider.'/process_edit_image.php');
                // Prepare the action.
                $paramstructure = [
                    'contextid' => $context->id,
                    'prompttext' => $prompt,
                    'aspectratio' => optional_param('aspectratio', 'square', PARAM_ALPHA),
                    'quality' => optional_param('quality', 'standard', PARAM_ALPHA),
                    'numimages' => optional_param('numimages', 1, PARAM_INT),
                    'style' => optional_param('style', 'natural', PARAM_ALPHA),
                ];
                $action = new \core_ai\aiactions\edit_image(
                    contextid: $paramstructure['contextid'],
                    userid: $USER->id,
                    prompttext: $paramstructure['prompttext'],
                    quality: $paramstructure['quality'],
                    aspectratio: $paramstructure['aspectratio'],
                    numimages: $paramstructure['numimages'],
                    style: $paramstructure['style'],
                    stored_file: $file
                );

                $reflclass = new \ReflectionClass($manager);
                $reflmethod = $reflclass->getMethod('call_action_provider');
                $reflmethod->setAccessible(true);

                $result = $reflmethod->invoke($manager, $providerinstance, $action);

                $reflmethod2 = $reflclass->getMethod('store_action_result');
                $reflmethod2->setAccessible(true);

                $reflmethod2->invoke($manager, $providerinstance, $action, $result);

                if (!$result->get_success()) {
                    return false;
                }

                $draftfile = $result->get_response_data()['draftfile'] ?? null;
                return $this->process_ai_generated_file($draftfile, $draftid, $filename);
            }
        }
        return null;
    }

    /**
     * Process a stored_file generated by an AI provider and convert it to a draft file.
     *
     * @param stored_file|null $draftfile The stored_file object containing the AI-generated image.
     * @param int $draftid The draft item ID to associate the new file with.
     * @param string $filename The desired filename for the new draft file.
     * @return array|false An array containing draft file URL, draft item ID, filename, and error status, or false on failure.
     */
    public function process_ai_generated_file($draftfile, $draftid, $filename) {
        if (empty($draftfile)) {
            return false;
        }
        /** @var \stored_file $draftfile */
        $smallerdata = $this->make_image_smaller($draftfile->get_content());
        $base64data = base64_encode($smallerdata);
        if (empty($base64data)) {
            return false;
        }
        // Generate draft file
        $filerecord = $this->base64ToFile($base64data, $draftid, $filename);
        if ($filerecord) {
            $draftid = $filerecord['itemid'];
            $draftfileurl = \moodle_url::make_draftfile_url(
                $draftid,
                $filerecord['filepath'],
                $filerecord['filename'],
                false,
            );
            return [
                'drafturl' => $draftfileurl->out(false),
                'draftitemid' => $draftid,
                'filename' => $filename,
                'error' => false,
            ];
        }
        return false;
    }

}
