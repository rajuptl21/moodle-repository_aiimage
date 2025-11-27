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
 * Strings for repository_aiimage.
 *
 * @package    repository_aiimage
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['apiprovider'] = 'Api provider';
$string['apiprovider_help'] = 'Choose api provider to use in image generation';
$string['configplugin'] = 'AI Image configuration';
$string['pluginname'] = 'AI Image';
$string['pluginname_help'] = 'Generate images from text prompts and insert them directly via the file picker.';
$string['privacy:metadata'] = 'The Cloud Poodll repository plugin does not store personal data.';
$string['configapikey'] = 'Cloud Poodll API key';
$string['configapikey_desc'] = 'Optional: If set, used when calling the external service. Not required for the built-in demo generator.';
$string['enterprompt'] = 'Enter a prompt...';
$string['promptplaceholdercanedit'] = 'Enter an image description (generate) or describe changes to the selected image (modify).';
$string['promptplaceholder'] = 'Enter an image description.';
$string['cap:view'] = 'Use the AI Image repository';
$string['aiimage:view'] = 'Use the AI Image repository';
$string['makeimage'] = 'Make image';
$string['imagetype'] = 'Image type';
$string['imagetype_flatvectorillustration'] = 'Flat vector illustration';
$string['imagetype_cartoon'] = 'Cartoon';
$string['imagetype_photorealistic'] = 'Photorealistic';
$string['imagetype_digitalpainting'] = 'Digital painting';
$string['imagetype_linedrawing'] = 'Line drawing';
$string['imagetype_3drender'] = '3D render';
$string['imagetype_infographic'] = 'Infographic';
$string['noimagesmessage'] = 'No images have been added yet.';
$string['noimagelabel'] = 'Do not use an existing image';
$string['provider:cloudpoodll'] = 'Cloud poodll';
$string['selectimage'] = 'Select image';
$string['imagepreview'] = 'Image preview';
$string['submitbutton'] = 'Make Image';

// Admin settings.
$string['apiuser'] = 'Poodll API User';
$string['apiuser_help'] = 'The Poodll account username that authorises Poodll on this site.';
$string['apisecret'] = 'Poodll API Secret';
$string['apisecret_help'] = 'The Poodll API secret. See <a href="https://support.poodll.com/support/solutions/articles/19000083076-cloud-poodll-api-secret">here</a> for more details.';
$string['cloudpoodllserver'] = 'Cloud Poodll Server';
$string['cloudpoodllserver_help'] = 'The server to use for Cloud Poodll. Only change this if Poodll has provided a different one.';
$string['awsregion'] = 'AWS Region';
$string['awsregion_help'] = 'The AWS region where your Poodll account is configured.';

// Region options.
$string['useast1'] = 'US East';
$string['tokyo'] = 'Tokyo, Japan';
$string['sydney'] = 'Sydney, Australia';
$string['dublin'] = 'Dublin, Ireland';
$string['ottawa'] = 'Ottawa, Canada';
$string['frankfurt'] = 'Frankfurt, Germany';
$string['london'] = 'London, U.K';
$string['saopaulo'] = 'Sao Paulo, Brazil';
$string['mumbai'] = 'Mumbai, India';
$string['singapore'] = 'Singapore';
$string['bahrain'] = 'Bahrain';
$string['capetown'] = 'Capetown, South Africa';
$string['ningxia'] = 'Ningxia, China';

$string['expiredays'] = 'Days to keep file';
$string['displaysubs'] = '{$a->subscriptionname} : expires {$a->expiredate}';
$string['noapiuser'] = "No API user entered. CloudPoodll repository  will not work correctly.";
$string['noapisecret'] = "No API secret entered. CloudPoodll repository  will not work correctly.";
$string['credentialsinvalid'] = "The API user and secret entered could not be used to get access. Please check them.";
$string['appauthorised'] = "CloudPoodll repository is authorised for this site.";
$string['appnotauthorised'] = "CloudPoodll repository is NOT authorised for this site.";
$string['refreshtoken'] = "Refresh license information";
$string['notokenincache'] = "Refresh to see license information. Contact Poodll support if there is a problem.";
// these errors are displayed on activity page
$string['nocredentials'] = 'API user and secret not entered. Please enter them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['novalidcredentials'] = 'API user and secret were rejected and could not gain access. Please check them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['nosubscriptions'] = "There is no current subscription for this site/plugin.";
$string['getfreetrial'] = 'Get a free trial of Poodll';
$string['fillcredentials'] = "Set API user and secret with existing credentials";
$string['privacy:metadata:repository_aiimage'] = 'The AI Image repository plugin does not store any personal data, but does transmit user data from Moodle to the remote system.';
$string['privacy:metadata:repository_aiimage:instructions'] = 'The AI Image repository user supplied image make/edit instructions.';
$string['privacy:metadata:repository_aiimage:imagedata'] = 'The AI Image repository user supplied image data (editing).';
