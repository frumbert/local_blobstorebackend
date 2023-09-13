# Blob store backend

This is a Moodle local plugin that provides an endpoint for Rise/Storyline courses to send and receieve JSON data to. When it is not present on Moodle, a fallback external service provides the same.

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

## licence

GPL3 (moodle)