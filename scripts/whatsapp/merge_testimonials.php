<?php
// Throwaway: merge feedback out_*.json, join with parsed messages, scrub phone
// numbers, and write a committable testimonials seed.
$root = dirname(__DIR__, 2); // project root from scripts/whatsapp/
$dir = $root . '/storage/app/private/whatsapp';
$messages = [];
foreach (json_decode(file_get_contents("$dir/messages.json"), true) as $m) {
    $messages[$m['idx']] = $m;
}

function scrub(string $s): string
{
    $s = preg_replace('/\+?91[\s\-]?\d{5}[\s\-]?\d{5}/', '', $s);
    $s = preg_replace('/\b\d{5}[\s\-]?\d{5}\b/', '', $s);
    $s = preg_replace('/\b\d{10}\b/', '', $s);
    // Drop "Cell number :" / "Mobile:" lines left dangling after number removal.
    $s = preg_replace('/(?im)^\s*(cell|mobile|phone|contact|ph)\s*(no\.?|number)?\s*[:\-]?\s*$/m', '', $s);
    return trim($s);
}

$rows = [];
for ($i = 0; $i < 10; $i++) {
    $f = "$dir/fb/out_$i.json";
    if (! is_file($f)) { continue; }
    foreach (json_decode(file_get_contents($f), true) ?: [] as $fb) {
        $m = $messages[$fb['idx'] ?? -1] ?? null;
        if (! $m || empty($m['text'])) { continue; }

        $name = trim((string) ($fb['author_name'] ?? '')) ?: null;
        $role = trim((string) ($fb['role'] ?? '')) ?: null;
        if (! $name) {
            $name = $role ?: 'Samutkarsh student';
        }

        $rows[] = [
            'author_name' => scrub($name),
            'role'        => $role,
            'center'      => trim((string) ($fb['center'] ?? '')) ?: null,
            'event'       => trim((string) ($fb['event'] ?? '')) ?: null,
            'body'        => scrub($m['text']),
            'source_hash' => md5($m['date'] . '|' . $m['sender_raw'] . '|' . $m['plain']),
            'date'        => $m['date'],
        ];
    }
}

// Stable order: oldest first.
usort($rows, fn ($a, $b) => strcmp($a['date'], $b['date']));

$out = $root . '/database/seeders/data';
@mkdir($out, 0777, true);
file_put_contents("$out/testimonials.json",
    json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo count($rows) . " testimonials exported.\n";
$left = 0;
foreach ($rows as $r) {
    if (preg_match('/\b\d{10}\b|\d{5}[\s\-]?\d{5}/', $r['body'] . ' ' . $r['author_name'])) { $left++; }
}
echo "rows with phone-like digits remaining: $left\n";
foreach ($rows as $r) {
    echo '  · ' . $r['date'] . '  ' . str_pad((string) $r['author_name'], 22) . ' ' . ($r['role'] ?: '-') . "\n";
}
