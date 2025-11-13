<?php
/**
 * Sanitize CalDAV fixture data captured from iCloud
 *
 * This script processes raw fixture captures and sanitizes sensitive information
 * while preserving the structure needed for testing CalDAV workarounds.
 */

// Read all captured fixtures
$calendarsFiles = glob('/tmp/caldav_fixtures/calendars/*.txt');
$eventsFiles = glob('/tmp/caldav_fixtures/events/*.txt');

echo "Found " . count($calendarsFiles) . " calendar fixtures\n";
echo "Found " . count($eventsFiles) . " event fixtures\n";

$processedCount = 0;

// Process calendar fixtures
foreach ($calendarsFiles as $file) {
    $content = file_get_contents($file);

    // Sanitize user IDs and calendar UUIDs
    $content = preg_replace(
        '/\/\d+\/calendars\/[A-F0-9-]+\//',
        '/user123/calendars/TEST-CALENDAR-UUID/',
        $content
    );

    // Sanitize principal URLs
    $content = preg_replace(
        '/\/\d+\/principal\//',
        '/user123/principal/',
        $content
    );

    // Sanitize display names that might contain personal info
    $content = preg_replace(
        '/\[displayname\] => [^\n]+/',
        '[displayname] => Test Calendar',
        $content
    );

    // Save sanitized fixture
    $basename = 'calendar-' . basename($file);
    $outPath = __DIR__ . '/calendars/' . $basename;
    file_put_contents($outPath, $content);
    echo "Sanitized: $basename\n";
    $processedCount++;
}

// Process event fixtures
foreach ($eventsFiles as $file) {
    $content = file_get_contents($file);

    // Sanitize event UIDs
    $content = preg_replace(
        '/UID:[A-F0-9-]+@/',
        'UID:TEST-EVENT-UID-' . substr(md5($file), 0, 8) . '@',
        $content
    );

    // Sanitize URLs
    $content = preg_replace(
        '/URL:https?:\/\/[^\s]+/',
        'URL:https://example.com',
        $content
    );

    // Sanitize SUMMARY (event titles)
    $content = preg_replace(
        '/SUMMARY:[^\n]+/',
        'SUMMARY:Test Event',
        $content
    );

    // Sanitize DESCRIPTION
    $content = preg_replace(
        '/DESCRIPTION:[^\n]+/',
        'DESCRIPTION:Test Description',
        $content
    );

    // Sanitize LOCATION
    $content = preg_replace(
        '/LOCATION:[^\n]+/',
        'LOCATION:Test Location',
        $content
    );

    // Sanitize organizer and attendee emails
    $content = preg_replace(
        '/([A-Z-]+:[^:]*mailto:)[^@\s]+@[^\s]+/',
        '$1test@example.com',
        $content
    );

    // Sanitize calendar URLs in events
    $content = preg_replace(
        '/\/\d+\/calendars\/[A-F0-9-]+\//',
        '/user123/calendars/TEST-CALENDAR-UUID/',
        $content
    );

    // Save sanitized fixture
    $basename = 'event-' . basename($file);
    $outPath = __DIR__ . '/events/' . $basename;
    file_put_contents($outPath, $content);
    echo "Sanitized: $basename\n";
    $processedCount++;
}

echo "\nSuccessfully sanitized $processedCount fixtures\n";
echo "Fixtures saved to tests/fixtures/\n";
