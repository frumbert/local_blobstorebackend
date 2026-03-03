<?php
// ini_set ('display_errors', 1); ini_set ('display_startup_errors', 1); error_reporting (E_ALL);
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
 * Version details
 *
 * @package    local
 * @subpackage blobstorebackend
 * @copyright  tim.stclair@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
  Storyline in Rise interactions don't get stored anywhere by RISE (not implemented by Articulate) so we need to store them ourselves.
  Using localStorage is not an option, as it is not shared between devices, and is not accessible to the teacher.
  This plugin provides a simple REST API to store and retrieve the responses, and a method for the learner to compile their notes to a formatted PDF file.
  A separate plugin may be created to display the notes to the teacher.

  There are various mechanisms to read/write this data.

  1. a Storyline embed inside Rise course
    - uses HEAD, OPTIONS, GET and PUT
  2. riseSCORMBridge
    - uses OPTIONS, POST, GET
    - GET /local/blobstorebackend/some-hash-value.html
    - POST to /local/blobstorebackend/generate
    - POST to /local/blobstorebackend/view
    - POST to /local/blobstorebackend/download (not fully implemented)

*/

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type, Cache-Control, X-Requested-With");
header("Access-Control-Allow-Methods: GET, PUT, HEAD, POST, OPTIONS");

require_once("../../config.php");
require_once("./vendor/autoload.php");
require_once("./locallib.php");

// common variables
$method       = local_blobstorebackend_get_method();
$data         = local_blobstorebackend_get_request_data();
$publicurl    = local_blobstorebackend_get_publicurl();
$salt         = $CFG->passwordsaltmain;
$url          = $data['url'] ?? '';
list($digest,$context,$block,$key) = array_pad(explode('/', rtrim($url, '/')), 4, null);

// router
switch ($method) {
  case "OPTIONS": /* ----------------- METHOD ----------------------- */
  case "HEAD": /* ----------------- METHOD ----------------------- */
    die();
    break;

  case "GET": /* ----------------- METHOD ----------------------- */
    if (str_ends_with($_SERVER['REQUEST_URI'], '.html') || str_ends_with($_SERVER['REQUEST_URI'], '.json'))  {
      if ($contents = local_blobstorebackend_get_html($url)) {
        die($contents);
      }
    }
    local_blobstorebackend_check_authorization();
    if ($digest == "dl") $block = "zip"; // possibly used by riseSCORMbridge downloader
    switch ($block) { // why is it block?
      case "cleanup": break; // handled in cron
      case "download":
        $pdf = new pdfExporter($digest,$context);
        $filename = md5($courseName.time().$salt);
        $courseName = $pdf->GetCourseName();
        $pdf->Export($filename);
        $result = new stdClass();
        $result->link = "{$publicurl}?hash={$filename}&filename={$courseName}.pdf";
        $result->filename = $courseName . '.pdf';
        local_blobstorebackend_send_response($result);
      break;

      case "collate":
          $results = local_blobstorebackend_CollateResponses($key,$course);
          local_blobstorebackend_send_response(['success' => true, 'records' => $results]);
      break;

      default:
        $data = local_blobstorebackend_get_data($context,$block,$digest,$key);
        if (!$data) {
          local_blobstorebackend_send_response(array('success' => false), 404);
        }
        // $data->success = true;
        $response = json_decode($data->data ?: "{}");
        if (isset($response->url)) unset($response->url);
        local_blobstorebackend_send_response($response);
    }
    break;

  case "PUT": /* ----------------- METHOD ----------------------- */
    local_blobstorebackend_check_authorization();
    unset($data['url']);
    local_blobstorebackend_set_data($data, $context,$block,$digest,$key); // data is php://input
    local_blobstorebackend_send_response(array('success' => true));
    break;

  case "POST": /* ----------------- METHOD ----------------------- */
    if (empty($digest)) { //  || empty($context) || empty($block))
      local_blobstorebackend_send_response(array('error' => 'Malformed request', 'data' => $data), 400);
    }

    $phpinput     = json_decode(file_get_contents("php://input"), true);
    $extension    = $phpinput['type'] ?? 'html';
    $course       = $phpinput['course'] ?? '';
    $learner      = $phpinput['learner'] ?? '';
    $interaction  = $phpinput['interaction'] ?? '';
    $content      = $phpinput['content'] ?? '';
    $question     = $phpinput['question'] ?? '';
    $key          = $phpinput['key'] ?? '';

    $publicurl = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST']."/local/blobstorebackend"; ///{$course}";
    $hashInput = $course . "|" . $learner . "|" . $interaction . "|" . $key;
    $hash = substr(hash('sha256', $hashInput), 0, 32); // safe hash

    switch ($digest) {
      case "generate":
        if ($extension === "json") {
          local_blobstorebackend_set_data($content, $hash);
        } else {
          $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
          $html .= "<style>body{font-family:sans-serif;}header{font-style:italic}</style>";
          $html .= "</head><body><header>" . local_blobstorebackend_Wrap($question) . "</header>";
          $html .= "<main>" . local_blobstorebackend_Wrap($content) . "</main>";
          $html .= "</body></html>";
          local_blobstorebackend_set_data($html, $hash);
        }
        local_blobstorebackend_send_response(array("url" => $publicurl . "/{$hash}.{$extension}"));
      break;

      case "view":
      // https://rise.frumbert.org/c50d03d51d814082715773fa35fc9c38.html
      // https://cpd.avant.org.au/local/blobstorebackend/715773fa35fc9c38/c50d03d51d814082715773fa35fc9c38.html
      if ($record = local_blobstorebackend_get_data($hash)) {
        $contents = $record->data;
        header('Content-Type: ' . strpos($content, "<html")===false ? 'application/json' : 'text/html');
        die($contents);
      }
      break;
    }
    break;

  default:
    local_blobstorebackend_send_response(array('error' => 'Bad method'), 405);

}