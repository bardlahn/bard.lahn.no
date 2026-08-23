<?php

// Function for fetching and serving file for download

function serveCitation(array $pub): int {

    // Constructing RIS content from publication data

    $pD = $pub['pub-data'];
    $pD['pub-type'] = strtolower($pD['pub-type']) ?? '';    

    $dl = "  - ";
    $ln = "\r\n";

    // Setting publication type - defaults to report
    $pType = match($pD['pub-type']) {
        'chapter' => 'CHAP',
        'book'    => 'BOOK',
        'article' => 'JOUR',
        'thesis'  => 'THES',
        default   => 'RPRT',
    };

    $out =  "TY"    .$dl.   $pType          .$ln;
    $out .= "TI"    .$dl.   ($pub['title'] ?? 'n/a').$ln;

    // Adding authors
    $auth = getAuthors($pub['authors'] ?? 'self');
    if ($auth) foreach ($auth as $au) {
        $out .= (!empty($au['name'])) ? 
            "AU"    .$dl.   $au['name']     .$ln : "";
    }

    // Adding publication year
    $date = match(true) {
        $pub['date'] instanceof DateTime => $pub['date'],
        is_numeric($pub['date']) && (int)$pub['date'] <= 9999
                                         => new DateTime((int)$pub['date'] . '-01-01'),
        is_numeric($pub['date'])         => (new DateTime())->setTimestamp((int)$pub['date']),
        default                          => new DateTime((string)$pub['date']),
    };
    $pYear = $date->format('Y') ?? 'n/a';
    $out .= "PY"    .$dl.   $pYear          .$ln;
    
    // Adding publication-specific fields
    if      ($pD['pub-type'] == 'chapter') {
        $out .= (!empty($pD['book'])) ?
            "T2"    .$dl.   $pD['book']     .$ln : "";
        $out .= (!empty($pD['pages'])) ?
            "SP"    .$dl.   $pD['pages']    .$ln : "";
        $eds = getAuthors($pD['editors']) ?? '';
        if ($eds) foreach ($eds as $a2) {
        $out .= (!empty($a2['name'])) ? 
            "A2"    .$dl.   $a2['name']     .$ln : "";
        }
    } elseif ($pD['pub-type'] == 'article') {
        $out .= (!empty($pD['journal'])) ?
            "T2"    .$dl.   $pD['journal']   .$ln : "";
        $out .= (!empty($pD['volume'])) ?
            "VL"    .$dl.   $pD['volume']    .$ln : "";
        $out .= (!empty($pD['issue'])) ?
            "IS"    .$dl.   $pD['issue']     .$ln : "";
        $out .= (!empty($pD['pages'])) ?
            "SP"    .$dl.   $pD['pages']     .$ln : "";
    } elseif ($pD['pub-type'] == 'thesis') {
        $out .= (!empty($pD['degree'])) ?
            "M3"    .$dl.   $pD['degree']    .$ln : "";
    } elseif ($pD['pub-type'] == 'report') {
        $out .= (!empty($pD['number'])) ?
            "SN"    .$dl.   $pD['number']    .$ln : "";
    }

    // Adding if available:
    // Publisher, place, URL, DOI, ISBN

    $out .= (!empty($pD['publisher'])) ?
        "PB"    .$dl.   $pD['publisher']     .$ln : "";
    $out .= (!empty($pD['place'])) ?
        "CY"    .$dl.   $pD['place']     .$ln : "";
    if (!empty($pD['doi'])) {
        $pURL = "https://dx.doi.org/".$pD['doi'];
        $out .= "DO".$dl.   $pD['doi']      .$ln;
    }
    $pURL = $pub['routes']['external'] ?? $pURL ?? '';
    $out .= (!empty($pURL)) ?
            "UR"    .$dl.   $pURL           .$ln : "";
    $out .= (!empty($pD['isbn'])) ?
            "SN"    .$dl.   $pD['isbn']     .$ln : "";

    // Adding 'end of record'
    $out .= "ER"    .$dl.$ln;    

    // Printing headers
    header('Content-Type: application/x-research-info-systems');
    header('Content-Length: ' . strlen($out));
    header('Content-Disposition: attachment; filename="'.($pub['slug'] ?? 'citation').'.ris"');

    // Before returning file: Logging and counting hit
    statCountPath($_SERVER['REQUEST_URI']);
    logEvent("Citation served successfully for publication: " . ($pub['title'] ?? 'n/a'), LOG_INFO);

    // Printing file content
    print $out;

    return SERVE_SUCCESS;

}


?>