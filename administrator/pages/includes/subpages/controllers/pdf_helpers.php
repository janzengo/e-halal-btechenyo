<?php
// Shared functions for PDF generation

if (!function_exists('check_tcpdf_exists')) {
    function check_tcpdf_exists() {
        $base_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $tcpdf_path = $base_path . '/vendor/tecnickcom/tcpdf/tcpdf.php';
        
        if (!file_exists($tcpdf_path)) {
            throw new Exception("TCPDF library not found at: " . $tcpdf_path);
        }
        return $tcpdf_path;
    }
}

// Add any other shared functions here 