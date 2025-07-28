<?php

// --- IMPORTANT: Replace with your actual client site's secret key ---
$clientSiteSecretKey = 'HyrmLxMg4rvOoTZ'; // <--- PASTE YOUR KEY HERE

// --- Sample Payload (matches the structure your provider site sends) ---
// Ensure this structure exactly matches what your provider site will send,
// including data types (e.g., numbers as numbers, not strings).
$payload = [
    'wager_code' => 'TEST_WAGER_123', // A unique ID for this test
    'game_type_id' => 1, // Example game type ID
    'players' => [
        [
            'player_id' => 'P111113',
            'balance' => 3200.00, // New balance after transaction
        ],
        // Add more players if your test scenario involves them
    ],
    'banker_balance' => 159900.00, // Banker's final balance
    'timestamp' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ISO8601), // Current timestamp in ISO 8601 format
    'total_player_net' => 100.00, // Example net change
    'banker_amount_change' => -100.00, // Example banker change
];

// Sort the payload keys alphabetically for consistent signature generation
// This is CRUCIAL for both provider and client to generate the same hash
ksort($payload);

// Encode the sorted payload to JSON
$jsonPayload = json_encode($payload);

// Generate the signature
$signature = hash_hmac('sha256', $jsonPayload, $clientSiteSecretKey);

echo "--- Payload for Postman (Copy this entire JSON block) ---\n";
// Add the signature to the payload for the final JSON to send
$payload['signature'] = $signature;
echo json_encode($payload, JSON_PRETTY_PRINT);
echo "\n\n--- Raw JSON Payload for Hashing (for debugging) ---\n";
echo $jsonPayload;
echo "\n\n--- Generated Signature ---\n";
echo $signature;
echo "\n";

// Helper function to get current timestamp (if not using Carbon/Laravel)
if (!function_exists('now')) {
    function now() {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
?>