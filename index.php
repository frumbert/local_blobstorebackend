<?php

// handy for when you don't have xdebug
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

  We use a file-based structure and iterate through the filesystem to find the relevant files on demand.
  This could be changed to use a database and file store and be aligned to the user, etc
  The current structure is:
  $CFG->dataroot (moodle data folder)
    /blobstorebackend (hard coded folder name used only by this plugin)
      /context (course id)
        /block (id assigned by RISE when adding an interaction)
          /user (base64 encoded concatenated learner name and username)
            db.json (the actual response, which includes the course name, the page name, the question and the answer)
*/

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type, Cache-Control, X-Requested-With");
header("Access-Control-Allow-Methods: GET, PUT, HEAD, OPTIONS");

require_once("../../config.php");
require_once("./vendor/autoload.php");

use Dompdf\Dompdf;

// no we aren't really using the moodle methods
// this merges GET, POST and PUT fields together into one object
function get_request_data () {
  return array_merge(empty($_POST) ? array() : $_POST, (array) json_decode(file_get_contents('php://input'), true), $_GET);
}

// We'll want to know if the method is GET, PUT, HEAD or OPTIONS
function get_method () {
  return $_SERVER['REQUEST_METHOD'];
}

// We'll want to know the public url for the download.php script
function get_publicurl () {
  return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] . '/local/blobstorebackend/download.php';
}

// send a http response with the appropriate headers
function send_response ($response, $code = 200) {
  header("Content-Type: application/json");
  http_response_code($code);
  if (is_array($response)) {
    $response = (object) $response;
  }
  die(json_encode($response));
}

// get the path to the blobstore
// this could be a config setting, but it's easier to hardcode it
function get_db() {
  global $CFG;
  return $CFG->dataroot . DIRECTORY_SEPARATOR . 'blobstorebackend' . DIRECTORY_SEPARATOR;
}

// when compiling the responses, we want to group them by page, and filter them to the specified user
function CollateResponses($user,$context) {
  $ordered = [];
  // recurse through the filesystem to find all files for matching users
  $db = get_db() ."{$context}";
  $blocks = array_diff(scandir($db), array('..', '.'));
  $pages = [];
  foreach ($blocks as $index => $block) {
    $file = $db . DIRECTORY_SEPARATOR . $block . DIRECTORY_SEPARATOR . $user . DIRECTORY_SEPARATOR .'db.json';
    if (file_exists($file)) {
      $pages[] = (array) json_decode(file_get_contents($file));
    }
  }
  $ordered = [];
  foreach ($pages as $value) {
      $page = $value['page'];
      unset($value['page']);
      $ordered[$page][] = $value;
  }

  return $ordered;
}

// Dompdf does the heavy lifting here, which can convert HTML to PDF, handle page breaks, etc
// The PDF is converted from HTML, which can contain CSS2 styles, images, etc
class pdfExporter {
  protected $user;
  protected $context;
  protected $template;

  function __construct($user, $context) {
    $this->user = $user;
    $this->context = $context;
    $this->template = file_get_contents('./assets/template.html');
  }

  public function GetCourseName() {
    $notes = CollateResponses($this->user,$this->context);
    foreach ($notes as $page => $notes) {
      return $notes[0]['course']; // stored in each page
    }
  }

  public function Export($filename) {
    $dompdf = new Dompdf();
    $parsedown = new Parsedown();
    $notes = CollateResponses($this->user,$this->context);

    $title = $this->GetCourseName();

    // inject the notes into the template. Using markdown for simpicity, could be avoided
    $md = [];
    foreach ($notes as $page => $notes) {
      $md[] = "## {$page}\n";
      foreach ($notes as $note) {
        $md[] = "**{$note['question']}**\n";
        $md[] = $note['answer'] . "\n";
      }
      $md[] = "---\n";
    }
    $md = implode("\n", $md);
    $this->template = str_replace('{{title}}', $title, $this->template);
    $this->template = str_replace('{{notes}}', $parsedown->text($md), $this->template);

    $dompdf->loadHtml($this->template);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // store the result in a location that the downloader can access
    $db = get_db();
    file_put_contents("{$db}{$filename}.pdf", $dompdf->output());
  }
}

// after calling the download script, call this to clean up older generated pdfs
function CleanDownloads() {
  $x = 1 * 60 * 60; // 1 hour
  $current_time = time();
  $db = get_db();
  $files = glob($db . DIRECTORY_SEPARATOR . '*.pdf');
  foreach ($files as $file) {
    if (is_file($file)) {
      if ($current_time - filemtime($file) >= $x) {
        unlink($file);
      }
    }
  }
}

// some helper variables
$headers = getallheaders();
$method = get_method();
$data = get_request_data();
$publicurl = get_publicurl();
$salt = $CFG->passwordsaltmain;

// that's all we need for CORS
if ($method == "OPTIONS") die();

// check the authorization header is valid 
if (!isset($headers['Authorization'])) {
    send_response(array('error' => "I'm a teapot"), 418); // Garbage in, garbage out
}

// quick check to see if the source is recognised
$thishost = md5(strtolower($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']));
if ($thishost !== $headers['Authorization']) {
    send_response(array('error' => 'Unauthorized'), 401);
}

// that's all we need for testing the plugin's existence
if ($method == "HEAD") die();

// extract variables from the url (simple routing via htaccess)
$url = $data['url'] ?? null;
list($digest,$context,$block) = explode('/', $url);

if (empty($digest) || empty($context) || empty($block)) {
  send_response(array('error' => 'Malformed request', 'data' => $data), 400);
}

$db = get_db() . "{$context}/{$block}/{$digest}";

// $user = base64_decode(strtr($digest, '._-', '+/='));
// PROD - salt the earth to ensure abstraction in case of disk compromise
// $digest = sha1($digest.$salt);
// $context = sha1($context.$salt);

switch ($method) {
  case "GET":
    switch ($block) {
      case "cleanup":
        CleanDownloads();
      break;

      case "download":
        $pdf = new pdfExporter($digest,$context);
        $filename = md5($courseName.time().$salt);
        $courseName = $pdf->GetCourseName();
        $pdf->Export($filename);
        $result = new stdClass();
        $result->link = "{$publicurl}?hash={$filename}&filename={$courseName}.pdf";
        $result->filename = $courseName . '.pdf';
        send_response($result);
      break;

      case "collate":
          $results = CollateResponses($key,$course);
          send_response(['success' => true, 'records' => $results]);
      break;

      default:
        if (!file_exists("{$db}/db.json")) {
          send_response(array('success' => false), 404); // on initial load, the path & file won't exist
        }
        $data = file_get_contents("{$db}/db.json");
        send_response(json_decode($data));
    }
  break;

  case "PUT":
    if (!file_exists($db)) { // create the directory if it doesn't exist
      mkdir($db, 0775, true);
    }
    unset($data['context']); // this is already in the db path
    unset($data['url']); // this was just for routing, we don't need it in the db
    file_put_contents("{$db}/db.json", json_encode($data));
    send_response(array(
      'success' => true,
      // 'value' => $data, // useful for debugging
      // 'db' => $db // only for debugging, exposes server paths
    ));
  break;

  default:
    send_response(array('error' => 'Bad method'), 405);

}
