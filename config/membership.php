<?php

return [
    'xp_boost' => [
        'free' => env('MEMBERSHIP_XP_BOOST_FREE', 1),
        'premium' => env('MEMBERSHIP_XP_BOOST_PREMIUM', 1.25),
        'vip' => env('MEMBERSHIP_XP_BOOST_VIP', 1.5),
    ],

    'expiry_warning_days' => env('MEMBERSHIP_EXPIRY_WARNING_DAYS', '7,3,1'),

    'manual_payment' => [
        'enabled' => env('MEMBERSHIP_MANUAL_PAYMENT_ENABLED', true),
        'bank_name' => env('MEMBERSHIP_MANUAL_BANK_NAME', 'BCA'),
        'bank_account' => env('MEMBERSHIP_MANUAL_BANK_ACCOUNT', ''),
        'bank_holder' => env('MEMBERSHIP_MANUAL_BANK_HOLDER', 'AnimeStream'),
    ],
];
