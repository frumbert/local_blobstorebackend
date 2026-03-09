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
 * Read-only SCORM content preview.
 *
 * Opens a Rise SCORM package in a full-screen iframe with a minimal SCORM 1.2
 * API stub, bypassing the standard Moodle SCORM player machinery.
 *
 * Query params:
 *   cmid  (int)    - course module id of the SCORM activity
 *   block (string) - optional context2 / Storyline block id for deep-linking
 *   page  (string) - lesson title text to auto-click on the Rise overview page
 *
 * @package     local_blobstorebackend
 * @copyright   2026 Tim St Clair
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$cmid  = required_param('cmid',  PARAM_INT);
$block = optional_param('block', '', PARAM_ALPHANUMEXT);
$page  = optional_param('page',  '', PARAM_TEXT);

[$course, $cm] = get_course_and_cm_from_cmid($cmid, 'scorm');
$scorm   = $DB->get_record('scorm', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

// Find the primary SCO launch entry; fall back to any entry if no 'sco' type found.
$sco = $DB->get_record_sql(
    "SELECT * FROM {scorm_scoes} WHERE scorm = :scormid AND scormtype = 'sco' ORDER BY sortorder ASC LIMIT 1",
    ['scormid' => $scorm->id]
);
if (!$sco) {
    $sco = $DB->get_record_sql(
        "SELECT * FROM {scorm_scoes} WHERE scorm = :scormid ORDER BY sortorder ASC LIMIT 1",
        ['scormid' => $scorm->id]
    );
}
if (!$sco) {
    throw new \moodle_exception('cannotfindsco', 'scorm');
}

$revision   = (int)($scorm->revision ?? 0);
$launchfile = ltrim($sco->launch, '/');
$launchurl  = (new moodle_url(
    "/pluginfile.php/{$context->id}/mod_scorm/content/{$revision}/{$launchfile}"
))->out(false);

$PAGE->set_context($context);
$PAGE->set_url('/local/blobstorebackend/preview.php', ['cmid' => $cmid, 'block' => $block, 'page' => $page]);
$PAGE->set_title(format_string($scorm->name) . ' — Preview');
$PAGE->set_heading(format_string($scorm->name));
$PAGE->set_pagelayout('embedded');

echo $OUTPUT->header();

$launchurljson   = json_encode($launchurl);
$blockjson       = json_encode($block);
$pagejson        = json_encode($page);
$studentnamejson = json_encode(fullname($USER));
$studentidjson   = json_encode($USER->username);
$titleh          = htmlspecialchars(format_string($scorm->name), ENT_QUOTES, 'UTF-8');
?>
<style>
    html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
    #preview-bar {
        position: fixed; top: 0; left: 0; right: 0;
        height: 36px;
        padding: 0 12px;
        background: #1a1a2e;
        color: #eee;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.8em;
        font-family: sans-serif;
        z-index: 9999;
    }
    #preview-title { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: bold; }
    #scorm-status  { font-style: italic; opacity: 0.75; white-space: nowrap; }
    #scorm-preview {
        position: fixed;
        top: 36px; left: 0; right: 0; bottom: 0;
        width: 100%;
        height: calc(100% - 36px);
        border: none;
    }
</style>
<div id="preview-bar">
    <span id="preview-title"><?= $titleh ?></span>
    <span id="scorm-status">Loading…</span>
</div>
<iframe id="scorm-preview" name="scorm-preview" allowfullscreen src="about:blank"></iframe>
<script>
(function () {
    'use strict';

    // Initial SCORM 1.2 data model — enough for Rise to initialise without errors.
    var store = {
        'cmi.core.student_id':             <?= $studentidjson ?>,
        'cmi.core.student_name':           <?= $studentnamejson ?>,
        'cmi.core.lesson_status':          'not attempted',
        'cmi.core.lesson_location':        '',
        'cmi.core.entry':                  'ab-initio',
        'cmi.core.credit':                 'no-credit',
        'cmi.core.exit':                   '',
        'cmi.core.score.raw':              '',
        'cmi.core.score.min':              '0',
        'cmi.core.score.max':              '100',
        'cmi.suspend_data':                '',
        'cmi.launch_data':                 '',
        'cmi.student_preference.language': ''
    };

    function status(msg) {
        document.getElementById('scorm-status').textContent = msg;
    }

    // SCORM 1.2 API — placed on window so the iframe finds it via window.parent.API.
    window.API = {
        LMSInitialize:     function (s) { status('Active'); return 'true'; },
        LMSFinish:         function (s) { status('Done');   return 'true'; },
        LMSGetValue:       function (e) {
            return Object.prototype.hasOwnProperty.call(store, e) ? store[e] : '';
        },
        LMSSetValue:       function (e, v) { store[e] = v; return 'true'; },
        LMSCommit:         function (s)    { return 'true'; },
        LMSGetLastError:   function ()     { return '0'; },
        LMSGetErrorString: function (c)    { return ''; },
        LMSGetDiagnostic:  function (c)    { return ''; }
    };

    // Expose the block id so a custom script inside the Rise content can read
    // window.parent._previewBlock and navigate to the right lesson.
    window._previewBlock = <?= $blockjson ?>;

    // Lesson title to auto-click on the Rise overview page.
    window._previewPage = <?= $pagejson ?>;

    // ------------------------------------------------------------------
    // Auto-navigate to a lesson by matching .overview-list-item__title
    // text against the supplied page title. Rise is a SPA so the overview
    // list may not exist at iframe load time — use MutationObserver to
    // wait for the DOM to settle before searching.
    // ------------------------------------------------------------------
    function normTitle(s) {
        return s.trim().replace(/\s+/g, ' ').toLowerCase();
    }

    function tryClickLesson(doc, target) {
        var titles = doc.querySelectorAll('.overview-list-item__title');
        for (var i = 0; i < titles.length; i++) {
            if (normTitle(titles[i].textContent) === target) {
                var link = titles[i].closest('.overview-list-item__link');
                if (link) {
                    status('Navigating…');
                    link.click();
                    setTimeout(function () { status('Ready'); }, 2000);
                    return true;
                }
            }
        }
        return false;
    }

    function waitAndClickLesson(doc, pageTitle) {
        status('Finding lesson…');
        var target = normTitle(pageTitle);
        var attempts = 0;

        var interval = setInterval(function () {
            attempts++;
            if (tryClickLesson(doc, target) || attempts >= 30) {
                clearInterval(interval);
                if (attempts >= 30) status('Ready — lesson not found');
            }
        }, 500);
    }

    // Build launch URL. No hash — we want the overview page so we can click
    // the matching lesson. (If _previewBlock deep-linking is implemented
    // later, move the hash back here and skip waitAndClickLesson.)
    var launchURL = <?= $launchurljson ?>;

    var iframe = document.getElementById('scorm-preview');
    iframe.addEventListener('load', function () {
        if (iframe.src === 'about:blank') return;
        if (!window._previewPage) { status('Ready'); return; }

        // The outer iframe is just a shell; Rise renders inside a nested
        // #content-frame iframe. Poll for that inner iframe to appear and
        // finish loading, then run the lesson-click loop inside it.
        var outerDoc = iframe.contentDocument;
        var polls = 0;
        var findInner = setInterval(function () {
            polls++;
            var inner = outerDoc.getElementById('content-frame');
            if (inner) {
                clearInterval(findInner);
                // If it has already loaded, go straight to clicking.
                // Otherwise wait for its load event.
                if (inner.contentDocument && inner.contentDocument.readyState === 'complete') {
                    waitAndClickLesson(inner.contentDocument, window._previewPage);
                } else {
                    inner.addEventListener('load', function () {
                        waitAndClickLesson(inner.contentDocument, window._previewPage);
                    });
                }
            } else if (polls >= 40) {
                // 20 seconds — give up looking for the inner frame.
                clearInterval(findInner);
                status('Ready — inner frame not found');
            }
        }, 500);
    });
    iframe.src = launchURL;
}());
</script>
<?php
echo $OUTPUT->footer();
