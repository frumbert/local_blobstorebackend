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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_blobstorebackend
 * @copyright  2026 YOURNAME
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Retrieves data from the blobstorebackend table based on context.
 *
 * @param string $context1 Context 1.
 * @param string|null $context2 Context 2.
 * @param string|null $context3 Context 3.
 * @param string|null $context4 Context 4.
 * @return stdClass|false The data record if found, false otherwise.
 */
function local_blobstorebackend_get_data($context1, $context2 = null, $context3 = null, $context4 = null) {
    global $DB;

    if ($context1 === null) {
        return false;
    }

    $conditions = ['context1' => $context1];

    if ($context2 !== null) {
        $conditions['context2'] = $context2;
    }
    if ($context3 !== null) {
        $conditions['context3'] = $context3;
    }
    if ($context4 !== null) {
        $conditions['context4'] = $context4;
    }

    return $DB->get_record('blobstorebackend', $conditions, '*', IGNORE_MISSING);
}
function local_blobstorebackend_get_all_data($context1, $context2 = null, $context3 = null, $context4 = null) {
    global $DB;

    if ($context1 === null) {
        return false;
    }

    $conditions = ['context1' => $context1];

    if ($context2 !== null) {
        $conditions['context2'] = $context2;
    }
    if ($context3 !== null) {
        $conditions['context3'] = $context3;
    }
    if ($context4 !== null) {
        $conditions['context4'] = $context4;
    }

    $records = $DB->get_records('blobstorebackend', $conditions, 'data');

    return $records ?: false; // empty array → false
}


function local_blobstorebackend_get_html($file) {
  global $DB;
  $context1 = substr($file, 0, strpos($file, ".")); // .html or .json
  $record = $DB->get_record('blobstorebackend', ["context1" => $context1] , 'data');
  return $record ? $record->data : '';
}

/**
 * Sets data in the blobstorebackend table based on context.
 *
 * @param mixed $data The data to store (will be json_encoded).
 * @param string $context1 Context 1.
 * @param string|null $context2 Context 2.
 * @param string|null $context3 Context 3.
 * @param string|null $context4 Context 4.
 * @return int The id of the inserted or updated record.
 */
function local_blobstorebackend_set_data($data, $context1, $context2 = null, $context3 = null, $context4 = null) {
    global $DB, $USER;

    $conditions = ['context1' => $context1];
    if ($context2 !== null) {
        $conditions['context2'] = $context2;
    }
    if ($context3 !== null) {
        $conditions['context3'] = $context3;
    }
    if ($context4 !== null) {
        $conditions['context4'] = $context4;
    }

    $record = $DB->get_record('blobstorebackend', $conditions, '*', IGNORE_MISSING);

    if (is_array($data) || is_object($data)) {
      $data = json_encode($data);
    }

    if ($record) {
        $record->data = $data;
        $record->userid = $USER->id;
        $record->timecreated = time();
        $DB->update_record('blobstorebackend', $record);
        return $record->id;
    } else {
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->context1 = $context1;
        $record->context2 = $context2;
        $record->context3 = $context3;
        $record->context4 = $context4;
        $record->data = $data;
        $record->timecreated = time();
        return $DB->insert_record('blobstorebackend', $record);
    }
}

function local_blobstorebackend_check_authorization() {
  $headers = getallheaders();
  if ("localhost:8081"==$_SERVER['HTTP_HOST']) return true; // bypass for local
  if (!isset($headers['Authorization'])) {
    local_blobstorebackend_send_response(array('error' => "I'm a teapot"), 418); // Garbage in, garbage out
  }
  $thishost = md5(strtolower($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']));
  if ($thishost !== $headers['Authorization']) {
      local_blobstorebackend_send_response(array('error' => 'Unauthorized'), 401);
  }

}

// moodle's internal param functions dont get from PUT etc
// this merges GET, POST and PUT fields together into one object
function local_blobstorebackend_get_request_data () {
  return array_merge(empty($_POST) ? array() : $_POST, (array) json_decode(file_get_contents('php://input'), true), $_GET);
}

// We'll want to know if the method is GET, PUT, HEAD, POST or OPTIONS
function local_blobstorebackend_get_method () {
  return $_SERVER['REQUEST_METHOD'];
}

// We'll want to know the public url for the download.php script
function local_blobstorebackend_get_publicurl () {
  return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] . '/local/blobstorebackend/download.php';
}

// send a http response with the appropriate headers
function local_blobstorebackend_send_response ($response, $code = 200) {
  header("Content-Type: application/json");
  http_response_code($code);
  if (is_array($response)) {
    $response = (object) $response;
  }
  die(json_encode($response));
}

// get the path to the blobstore
// this could be a config setting, but it's easier to hardcode it
function local_blobstorebackend_get_db() {
  global $CFG;
  return $CFG->dataroot . DIRECTORY_SEPARATOR . 'blobstorebackend' . DIRECTORY_SEPARATOR;
}

function local_blobstorebackend_Wrap($text, $tag = 'p') {
  $lines = [];
  foreach (explode(PHP_EOL, $text) as $line) {
    $lines[] = '<' . $tag . '>' . $line . '</' . $tag . '>';
  }
  return implode('', $lines);
}

// when compiling the responses, we want to group them by page, and filter them to the specified user
function local_blobstorebackend_CollateResponses($user,$context) {
  $records = local_blobstorebackend_get_all_data($context, null, $user);
  $pages = [];
  foreach ($records as $record) {
    $pages[] = (array) json_decode($record->data);
  }
  $ordered = [];
  foreach ($pages as $value) {
      $page = $value['page'];
      unset($value['page']);
      $ordered[$page][] = $value;
  }

  return $ordered;
}

function local_blobstorebackend_ImportFromDisk() {
  global $CFG, $DB;
  $scan_root = $CFG->dataroot . DIRECTORY_SEPARATOR . 'blobstorebackend' . DIRECTORY_SEPARATOR;
  if (is_dir($scan_root)) {
    $iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($scan_root, \RecursiveDirectoryIterator::SKIP_DOTS),
    \RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
      if ($file->isFile() && strtolower($file->getExtension()) === 'html') {
        $content = file_get_contents($file->getPathname());
        $context1 = basename($file->getPath());
        $context2 = $file->getBasename('.html');
        local_blobstorebackend_set_data($content, $context1, $context2);
      } else if ($file->isFile() && $file->getFilename() === 'db.json') {
        $content = file_get_contents($file->getPathname());
        $path = str_replace($scan_root, '', $file->getPath());
        $contexts = array_values(array_filter(explode(DIRECTORY_SEPARATOR, $path)));

        $context1 = $contexts[0] ?? null;
        $context2 = $contexts[1] ?? null;
        $context3 = $contexts[2] ?? null;
        $context4 = $contexts[3] ?? null;

        $id = local_blobstorebackend_set_data($content, $context1, $context2, $context3, $context4);

        echo "<li>inserted record @ ", $id;

        if (!empty($id) && !empty($context3)) {
          $userstring = urldecode(base64_decode($context3));
          $userstring = rtrim($userstring, '?'); // Remove trailing '?' if it exists.

echo "&middot; " . $userstring, PHP_EOL;


          // Split lastname from the rest of the string.
          $parts = explode(',', $userstring, 2);
          if (count($parts) === 2) {
            $lastname = trim($parts[0]);
            $rest = trim($parts[1]);

            // Find the position of the first digit to separate firstname and username.
            if (preg_match('/(\d+)/', $rest, $matches, PREG_OFFSET_CAPTURE)) {
              $splitpos = $matches[0][1];
              $firstname = trim(substr($rest, 0, $splitpos));
              $username = trim(substr($rest, $splitpos));

              echo print_r(['lastname' => $lastname, 'firstname' => $firstname, 'username' => $username], true);

              // Find user in the database.
              $user = $DB->get_record('user', ['lastname' => $lastname, 'firstname' => $firstname, 'username' => $username], 'id');
              if ($user) {
                $DB->set_field('blobstorebackend', 'userid', $user->id, ['id' => $id]);
              }
            }
          }
        }
      }
    }
  }
}

