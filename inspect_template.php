<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

if (!isset($_FILES['template']) || $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
    die('Template upload failed');
}

$tmpPath = $_FILES['template']['tmp_name'];

try {
    $template = new TemplateProcessor($tmpPath);

    // 🔥 THIS IS THE MOST IMPORTANT LINE
    $variables = $template->getVariables();

    echo "<h2>Detected Variables</h2>";

    if (empty($variables)) {
        echo "<p style='color:red'>❌ No variables detected in this document.</p>";
        echo "<p>This means PHPWord CANNOT SEE your placeholders.</p>";
        exit;
    }

    echo "<ul>";
    foreach ($variables as $var) {
        echo "<li><strong>\${$var}</strong></li>";
    }
    echo "</ul>";

    echo "<hr><h3>Replacement Test</h3>";

    // Try replacing everything with TEST_VALUE
    foreach ($variables as $var) {
        $template->setValue($var, "TEST_VALUE");
    }

    $output = sys_get_temp_dir() . '/test_output.docx';
    $template->saveAs($output);

    echo "<p>✅ Test document generated:</p>";
    echo "<a href='download_test.php?file=" . urlencode($output) . "' target='_blank'>Download Test File</a>";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
