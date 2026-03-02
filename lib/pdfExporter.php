<?php

use Dompdf\Dompdf;

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
    $notes = local_blobstorebackend_CollateResponses($this->user,$this->context);
    foreach ($notes as $page => $notes) {
      return $notes[0]['course']; // stored in each page
    }
  }

  public function Export($filename) {
    $dompdf = new Dompdf();
    $parsedown = new Parsedown();
    $notes = local_blobstorebackend_CollateResponses($this->user,$this->context);

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
    $db = local_blobstorebackend_get_db();
    file_put_contents("{$db}{$filename}.pdf", $dompdf->output());
  }
}
