<?php

return [
    'except'                  => [
        'password',
        'password_confirmation',
    ],

    // If this value set to `true` inline listeners will be escaped, otherwise they will be removed.
    'escape_inline_listeners' => false,

    // By default, all elements allowed.
    'allowed_elements'        => null,

    // Elements would be escaped (will be filtered out by allowed_elements).
    'blocked_elements'        => ['script', 'frame', 'iframe', 'object', 'embed'],

    'media_elements' => ['img', 'audio', 'video', 'iframe'],

     // Image/Audio/Video/Iframe hosts that should be retained (by default, all hosts are allowed).
    'allowed_media_hosts' => null,
];
