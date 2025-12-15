<?php
echo "<h2>PHP Extensions Check</h2>";
echo "<p><strong>CURL enabled:</strong> " . (function_exists('curl_init') ? '✅ YES' : '❌ NO') . "</p>";
echo "<p><strong>file_get_contents enabled:</strong> " . (function_exists('file_get_contents') ? '✅ YES' : '❌ NO') . "</p>";
echo "<p><strong>allow_url_fopen:</strong> " . (ini_get('allow_url_fopen') ? '✅ YES' : '❌ NO') . "</p>";

if (function_exists('curl_init')) {
    echo "<p style='color: green;'>✅ CURL is available. Using CURL for OAuth.</p>";
} else {
    echo "<p style='color: orange;'>⚠️ CURL not available. Using file_get_contents instead.</p>";
}

phpinfo();
