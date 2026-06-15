<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ONE-TIME: parse the cleaned WhatsApp chat export (HTML) into structured
 * messages so they can be classified and imported as Activities.
 *
 *   php artisan activities:parse-whatsapp https://…/chat_cleaned.html
 *   php artisan activities:parse-whatsapp storage/app/whatsapp/chat.html
 *
 * Writes two files on the 'local' disk:
 *   - whatsapp/messages.json   every message (date, sender, text, attachments)
 *   - whatsapp/candidates.json the substantive text messages worth classifying
 */
class ParseWhatsappActivities extends Command
{
    protected $signature = 'activities:parse-whatsapp {source : URL or local path to the chat HTML}
        {--out=whatsapp/messages.json : output path on the local disk}
        {--min=60 : minimum plain-text length to count as a candidate}';

    protected $description = 'Parse the WhatsApp chat export into structured JSON (one-time)';

    public function handle(): int
    {
        $source = $this->argument('source');

        $this->info("Loading {$source} …");
        if (Str::startsWith($source, 'http')) {
            $html = Http::timeout(60)->get($source)->throw()->body();
        } else {
            $html = @file_get_contents($source) ?: @file_get_contents(base_path($source));
        }

        if (! $html) {
            $this->error('Could not read the source.');
            return self::FAILURE;
        }

        $messages = $this->parse($html);
        $this->info('Parsed ' . count($messages) . ' messages.');

        $candidates = array_values(array_filter($messages, function (array $m) {
            return $m['text'] !== ''
                && mb_strlen($m['plain']) >= (int) $this->option('min')
                && ! Str::contains($m['plain'], 'Media omitted');
        }));

        Storage::put($this->option('out'), json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        Storage::put('whatsapp/candidates.json', json_encode($candidates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info(count($candidates) . ' candidate messages → ' . Storage::path('whatsapp/candidates.json'));
        $this->info('All messages → ' . Storage::path($this->option('out')));

        return self::SUCCESS;
    }

    /** @return array<int,array<string,mixed>> */
    private function parse(string $html): array
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        libxml_clear_errors();

        // The single .wrap container holds day dividers and messages in order.
        $wrap = null;
        foreach ($dom->getElementsByTagName('div') as $div) {
            if ($this->hasClass($div, 'wrap')) { $wrap = $div; break; }
        }
        if (! $wrap) {
            return [];
        }

        $messages = [];
        $currentDay = null;
        $idx = 0;

        foreach ($wrap->childNodes as $node) {
            if (! $node instanceof DOMElement || $node->tagName !== 'div') {
                continue;
            }

            if ($this->hasClass($node, 'day')) {
                $currentDay = $this->parseDay(trim($node->textContent));
                continue;
            }

            if (! $this->hasClass($node, 'msg') || $this->hasClass($node, 'system')) {
                continue;
            }

            $sender = $text = $time = '';
            $attachments = [];

            foreach ($node->childNodes as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                if ($this->hasClass($child, 'sender')) {
                    $sender = trim($child->textContent);
                } elseif ($this->hasClass($child, 'text')) {
                    $text = $this->innerHtml($child);
                } elseif ($this->hasClass($child, 'time')) {
                    $time = trim($child->textContent);
                } elseif ($this->hasClass($child, 'att')) {
                    foreach ($child->getElementsByTagName('img') as $img) {
                        $attachments[] = ['type' => 'image', 'src' => $img->getAttribute('src')];
                    }
                    foreach ($child->getElementsByTagName('video') as $vid) {
                        $attachments[] = ['type' => 'video', 'src' => $vid->getAttribute('src')];
                    }
                    foreach ($child->getElementsByTagName('a') as $a) {
                        $attachments[] = ['type' => 'file', 'src' => $a->getAttribute('href')];
                    }
                }
            }

            $messages[] = [
                'idx'         => $idx++,
                'date'        => $currentDay,                       // Y-m-d or null
                'time'        => $time,
                'sender'      => $this->normalizeSender($sender),
                'sender_raw'  => $sender,
                'text'        => $text,                              // limited HTML
                'plain'       => $this->toPlain($text),
                'attachments' => $attachments,
            ];
        }

        return $messages;
    }

    private function hasClass(DOMNode $node, string $class): bool
    {
        if (! $node instanceof DOMElement) {
            return false;
        }
        return in_array($class, preg_split('/\s+/', $node->getAttribute('class')) ?: [], true);
    }

    /** Serialize a node's children to HTML, kept to a safe tag subset. */
    private function innerHtml(DOMElement $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }
        $html = strip_tags($html, '<strong><b><em><i><br>');
        return trim($html);
    }

    private function toPlain(string $html): string
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // collapse 3+ blank lines, trim each line
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }

    private function parseDay(string $text): ?string
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $text)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Phone-number senders must never reach the public site — drop to a label. */
    private function normalizeSender(string $sender): string
    {
        return preg_match('/^\+?\d[\d\s]{6,}$/', $sender) ? 'Group member' : $sender;
    }
}
