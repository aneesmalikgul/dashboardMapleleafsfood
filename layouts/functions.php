<?php
function homeURL()
{

    // Get the protocol (http or https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

    // Get the host (domain name)
    $host = $_SERVER['HTTP_HOST'];

    // Construct the base URL
    $baseUrl = $protocol . "://" . $host . "/tnml_report";

    return $baseUrl;
}
