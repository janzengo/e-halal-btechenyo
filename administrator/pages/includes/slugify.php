<?php

/**
 * Convert text to a URL-friendly format
 * @param string $text The text to convert
 * @return string Slugified text
 */
function slugify($text) {
    // Remove any non-alphanumeric characters
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Transliterate non-Latin characters
    if (function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    
    // Remove any characters that are not letters, numbers, or hyphens
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Trim hyphens from start and end of string
    $text = trim($text, '-');
    
    // Replace multiple hyphens with a single hyphen
    $text = preg_replace('~-+~', '-', $text);
    
    // Convert to lowercase
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
} 