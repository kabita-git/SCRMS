<?php
// Ensure this script can only be run from the command line (CLI)
if (php_sapi_name() !== 'cli') {
    die("Direct web access is forbidden.");
}

if ($argc < 2) {
    die("Usage: php background-mailer.php <job_file_path>\n");
}

$jobFile = $argv[1];

if (!file_exists($jobFile)) {
    die("Job file not found: $jobFile\n");
}

$content = file_get_contents($jobFile);
$jobData = json_decode($content, true);

if (!$jobData || !isset($jobData['to']) || !isset($jobData['subject']) || !isset($jobData['message'])) {
    unlink($jobFile);
    die("Invalid job payload.\n");
}

// Require the MailManager class to perform the actual synchronous email transfer
require_once __DIR__ . '/Mailer.php';

// Call the synchronous mail sending method
MailManager::send($jobData['to'], $jobData['subject'], $jobData['message']);

// Delete the temporary job queue file
unlink($jobFile);
