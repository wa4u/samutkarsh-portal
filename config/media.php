<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image optimisation pipeline
    |--------------------------------------------------------------------------
    | Mobile uploads are large; we down-scale, strip metadata, and transcode to
    | WebP. Three derivatives are produced per image:
    |   - master  : re-processable source (no watermark)
    |   - display : public full view (watermarked where applicable)
    |   - thumb   : grid / preview
    */
    'disk' => 'public',

    'webp_quality' => 80,

    'sizes' => [
        'master'  => 1600,   // max width/height, no watermark — kept for re-processing
        'display' => 1200,
        'thumb'   => 400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark (applied to the DISPLAY derivative only, non-destructively)
    |--------------------------------------------------------------------------
    | Logo is preferred; falls back to text if no logo file is present and text
    | is set. Watermarking is skipped silently if neither is available, so
    | uploads never fail because a watermark is missing.
    */
    'watermark' => [
        'enabled'    => env('WATERMARK_ENABLED', true),
        // Path on the configured disk, e.g. storage/app/public/branding/watermark.png
        'logo_path'  => env('WATERMARK_LOGO', 'branding/watermark.png'),
        'text'       => env('WATERMARK_TEXT', null),  // fallback if no logo
        'position'   => env('WATERMARK_POSITION', 'bottom-right'),
        'opacity'    => (int) env('WATERMARK_OPACITY', 35),   // 0–100
        'width_pct'  => (int) env('WATERMARK_WIDTH_PCT', 18), // logo width as % of image width
        'margin'     => 16,
    ],
];
