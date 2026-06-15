<?php
// Throwaway: split candidates into chunks (full text) for feedback classification.
$dir = __DIR__ . '/storage/app/private/whatsapp';
$cands = json_decode(file_get_contents("$dir/candidates.json"), true);
@mkdir("$dir/fb", 0777, true);

foreach (array_chunk($cands, 50) as $i => $chunk) {
    $slim = array_map(fn ($m) => [
        'idx'    => $m['idx'],
        'date'   => $m['date'],
        'text'   => mb_substr($m['plain'], 0, 1500),
    ], $chunk);
    file_put_contents("$dir/fb/chunk_$i.json",
        json_encode($slim, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
echo "chunks written\n";
