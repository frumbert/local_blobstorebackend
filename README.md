# Blob store backend

This is a Moodle local plugin that provides an endpoint for Rise/Storyline courses to send and receieve JSON data to. When it is not present on Moodle, a fallback external service provides the same. This means the interactions should still work when used on Review or even within the Rise editor.

## reason

  Storyline in Rise interactions don't get stored anywhere by RISE (not implemented by Articulate) so we need to store them ourselves.
  Using localStorage is not an option, as it is not shared between devices, and is not accessible to the teacher.
  This plugin provides a simple REST API to store and retrieve the responses, and a method for the learner to compile their notes to a formatted PDF file.
  A separate plugin may be created to display the notes to the teacher.

## file storage

  We use a file-based structure and iterate through the filesystem to find the relevant files on demand.
  This could be changed to use a database and file store and be aligned to the user, etc
  The current structure is:
  $CFG->dataroot (moodle data folder)
    /blobstorebackend (hard coded folder name used only by this plugin)
      /context (course id)
        /block (id assigned by RISE when adding an interaction)
          /user (base64 encoded concatenated learner name and username)
            db.json (the actual response, which includes the course name, the page name, the question and the answer)

## built-in extras

There are scripts that will try to match the background colour of the Storyline objects to the block colour used in Rise. This only works when the content is published on the same domain (such as when the Rise package is used in a LMS like Moodle). It also collapses the extra whitespace around the interactions where possible.

## assets folder

In this folder there are the Storyline 360 source files (`Download Button.story` and `Text Entry.story`) for the download button and text entry box. These need to be published to Review and then imported into Rise, then published to the LMS.

The `download.js` and `storyline.js` files are the scripts that are included in these storyline files already.

The `template.html` file is used by PDF generation. This can contain most HTML, reference images (local or external or data-url) and allows most CSS2 styling. The PDF generator automatically handles page breaks and is configured to generate A4 portrait sized documents with standard typefaces.

## licence

GPL3 (moodle)