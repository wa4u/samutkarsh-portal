<?php
// Throwaway: build a committable gallery manifest (dates + image filenames,
// NO phone data) from the parsed messages, grouping photos by date.
$root = dirname(__DIR__, 2); // project root from scripts/whatsapp/
$dir = $root . '/storage/app/private/whatsapp';
$messages = json_decode(file_get_contents("$dir/messages.json"), true);

// Group photos by calendar month (date-level albums were too fragmented).
$byMonth = [];
foreach ($messages as $m) {
    if (empty($m['date']) || empty($m['attachments'])) {
        continue;
    }
    $ym = substr($m['date'], 0, 7); // YYYY-MM
    foreach ($m['attachments'] as $att) {
        if (($att['type'] ?? null) !== 'image' || empty($att['src'])) {
            continue;
        }
        $byMonth[$ym][] = basename($att['src']);
    }
}
ksort($byMonth);

$months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
           '07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];

$manifest = [];
$totalImgs = 0;
foreach ($byMonth as $ym => $files) {
    $files = array_values(array_unique($files));
    [$y, $mo] = explode('-', $ym);
    $manifest[] = [
        'date'   => "$ym-01",
        'year'   => (int) $y,
        'title'  => 'Samutkarsh activities — ' . $months[$mo] . ' ' . $y,
        'slug'   => 'samutkarsh-' . $ym,
        'images' => $files,
    ];
    $totalImgs += count($files);
}

$out = $root . '/database/seeders/data';
@mkdir($out, 0777, true);
file_put_contents("$out/gallery_manifest.json",
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo count($manifest) . " date-albums, $totalImgs images → database/seeders/data/gallery_manifest.json\n";
echo "album image counts: ";
$counts = array_map(fn ($g) => count($g['images']), $manifest);
rsort($counts);
echo implode(',', array_slice($counts, 0, 15)) . " ...\n";
