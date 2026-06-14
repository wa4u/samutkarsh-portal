<?php

return [
    'version' => [
        'tiny' => '7.9.3',
        'language' => [
            // https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/
            'version' => '24.7.29',
            'package' => 'langs7',
        ],
        'licence_key' => env('TINY_LICENSE_KEY', 'no-api-key'),
    ],
    // 'vendor' = serve TinyMCE from /public/vendor/tinymce (self-hosted, no CDN,
    // no cloud API key — GPL build). Keeps the editor working behind any firewall
    // and avoids any third-party request from the admin panel.
    'provider' => 'vendor', // cloud|vendor
    // 'direction' => 'rtl',
    /**
     * change darkMode: 'auto'|'force'|'class'|'media'|false|'custom'
     */
    'darkMode' => 'auto',

    /** cutsom */
    'skins' => [
        // oxide, oxide-dark, tinymce-5, tinymce-5-dark
        'ui' => 'oxide',

        // dark, default, document, tinymce-5, tinymce-5-dark, writer
        'content' => 'default'
    ],

    'profiles' => [
        // Basic editing + image / link / media — the toolset requested for
        // posts & pages. Images get drag-handle resize + the align buttons.
        'default' => [
            'plugins' => 'autoresize advlist autolink lists link image media table code wordcount quickbars',
            'toolbar' => 'undo redo | blocks | bold italic underline | bullist numlist | blockquote | alignleft aligncenter alignright | link image media | removeformat code',
            'upload_directory' => 'editor',
            // Floating toolbar that appears when an image is selected:
            // align left/center/right, rotate, and size/options. The text
            // insert/selection quickbars are off to keep the UI uncluttered.
            'custom_configs' => [
                'quickbars_image_toolbar' => 'alignleft aligncenter alignright | rotateleft rotateright | imageoptions',
                'quickbars_insert_toolbar' => false,
                'quickbars_selection_toolbar' => false,
                'image_caption' => true,
            ],
        ],

        'simple' => [
            'plugins' => 'autoresize directionality emoticons link wordcount',
            'toolbar' => 'removeformat | bold italic | rtl ltr | numlist bullist | link emoticons',
            'upload_directory' => null,
        ],

        'minimal' => [
            'plugins' => 'link wordcount',
            'toolbar' => 'bold italic link numlist bullist',
            'upload_directory' => null,
        ],

        'full' => [
            'plugins' => 'accordion autoresize codesample directionality advlist autolink link image lists charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons help',
            'toolbar' => 'undo redo removeformat | fontfamily fontsize fontsizeinput font_size_formats styles | bold italic underline | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist outdent indent accordion | forecolor backcolor | blockquote table toc hr | image link anchor media codesample emoticons | visualblocks print preview wordcount fullscreen help',
            'upload_directory' => null,
        ],
    ],

    /**
     * this option will load optional language file based on you app locale
     * example:
     * languages => [
     *      'fa' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.7.29/langs7/fa.min.js',
     *      'es' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.7.29/langs7/es.min.js',
     *      'ja' => asset('assets/ja.min.js')
     * ]
     */
    'languages' => [],

    'extra' => [
        'toolbar' => [
            // 'fontsize' => '10px 12px 13px 14px 16px 18px 20px',
            // 'fontfamily' => 'Tahoma=tahoma,arial,helvetica,sans-serif;',
            // 'content_style' => 'body { font-family: "Tahoma", sans-serif; }',
        ]
    ]
];
