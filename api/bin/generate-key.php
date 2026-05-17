<?php
/*******************************************************************************
	Generate a new API key and append it to data/api_keys.json.

	Usage: php api/bin/generate-key.php "label for this consumer"

	The resulting key has form  hsc_<43 base64url chars>  (32 random bytes,
	base64url-encoded, no padding). hash_equals() is used for comparison
	in ApiKeyAuth so key length is not leaked.

*******************************************************************************/

if ($argc < 2) {
    fwrite(STDERR, "Usage: php api/bin/generate-key.php \"label\"\n");
    exit(1);
}

$label = trim($argv[1]);
if ($label === '') {
    fwrite(STDERR, "Label must not be empty.\n");
    exit(1);
}

$raw = random_bytes(32);
$key = 'hsc_' . rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

$file = __DIR__ . '/../../data/api_keys.json';
@mkdir(dirname($file), 0755, true);

$data = ['keys' => []];
if (file_exists($file)) {
    $parsed = json_decode((string)file_get_contents($file), true);
    if (is_array($parsed) && isset($parsed['keys']) && is_array($parsed['keys'])) {
        $data = $parsed;
    }
}

$data['keys'][] = [
    'key' => $key,
    'label' => $label,
    'revoked' => false,
];

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT) . "\n");

echo "Added key with label '{$label}':\n";
echo $key . "\n";
