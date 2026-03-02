<?php

require_once("../../config.php");
require_once("./locallib.php");

// local_blobstorebackend_ImportFromDisk();
ini_set('xdebug.var_display_max_depth', 99);

$userid = "20231";
$user = "TXVraGxpZiUyQyUyME1vc3RhZmExNzU1MjM-";

$values = local_blobstorebackend_CollateResponses($user, "01a317315a34");

var_dump($values);