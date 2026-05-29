<?php

// Function for fetching and serving file for download

// (Defining constants before function starts)
define ("SERVE_SUCCESS",          200);
define ("SERVE_ERROR_REQUEST",    400); // Not in use
define ("SERVE_ERROR_NOACCESS",   403);
define ("SERVE_ERROR_NOFILE",     404);

function serveCitation(array $pub): int {

    // ASSEMBLE FILE HERE

    $out = "";

    $out .= "TY  - \r\n";
    $out .= "ER  -\r\n";

    header('Content-Type: application/x-research-info-systems');
    header('Content-Length: ' . strlen($out));
    header('Content-Disposition: attachment; filename="FILENAME"');

    print $out;

    return SERVE_SUCCESS;

    // TO DO: IMPLEMENT GOAT COUNTER STATISTICS FOR FILE DOWNLOADS

}


?>