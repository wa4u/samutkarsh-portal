@php
    $url = route('public.activity.show', $activity);
    $caption = $activity->shareCaption();
    $img = $activity->shareImageUrl();
    $enc = rawurlencode($url);
    $links = [
        ['LinkedIn',  '#0a66c2', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $enc],
        ['Facebook',  '#1877f2', 'https://www.facebook.com/sharer/sharer.php?u=' . $enc],
        ['X',         '#000000', 'https://twitter.com/intent/tweet?url=' . $enc . '&text=' . rawurlencode($activity->title)],
    ];
@endphp

<div style="display:flex; flex-direction:column; gap:1rem; font-size:0.875rem;">
    @unless ($activity->is_published)
        <div style="background:#fef3c7; color:#92400e; padding:0.6rem 0.8rem; border-radius:0.5rem;">
            ⚠ This activity is not published yet. Publish it first, or the shared link will show “not found”.
        </div>
    @endunless

    <div>
        <label style="font-weight:600; display:block; margin-bottom:0.25rem;">Caption (copy &amp; paste)</label>
        <textarea id="share-caption" readonly rows="7"
                  style="width:100%; padding:0.6rem; border:1px solid #d1d5db; border-radius:0.5rem; font-family:inherit; resize:vertical;">{{ $caption }}</textarea>
        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('share-caption').value); this.textContent='✓ Copied';"
                style="margin-top:0.5rem; background:#ea580c; color:#fff; border:0; padding:0.45rem 0.9rem; border-radius:0.5rem; font-weight:600; cursor:pointer;">Copy caption</button>
    </div>

    <div>
        <label style="font-weight:600; display:block; margin-bottom:0.25rem;">Link</label>
        <input id="share-url" readonly value="{{ $url }}"
               style="width:100%; padding:0.6rem; border:1px solid #d1d5db; border-radius:0.5rem;">
        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('share-url').value); this.textContent='✓ Copied';"
                style="margin-top:0.5rem; background:#475569; color:#fff; border:0; padding:0.45rem 0.9rem; border-radius:0.5rem; font-weight:600; cursor:pointer;">Copy link</button>
    </div>

    <div>
        <label style="font-weight:600; display:block; margin-bottom:0.4rem;">Post to</label>
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
            @foreach ($links as [$name, $color, $href])
                <a href="{{ $href }}" target="_blank" rel="noopener"
                   style="background:{{ $color }}; color:#fff; padding:0.45rem 0.9rem; border-radius:0.5rem; font-weight:600; text-decoration:none;">{{ $name }}</a>
            @endforeach
            @if ($img)
                <a href="{{ $img }}" target="_blank" rel="noopener" download
                   style="background:#e1306c; color:#fff; padding:0.45rem 0.9rem; border-radius:0.5rem; font-weight:600; text-decoration:none;">Download image (Instagram)</a>
            @endif
        </div>
        <p style="color:#64748b; margin-top:0.5rem;">
            LinkedIn / Facebook / X open a pre-filled share window. For Instagram, download the image and paste the caption.
            @unless ($img) <br>(No photo found for this month — the site logo will be used as the preview image.) @endunless
        </p>
    </div>
</div>
