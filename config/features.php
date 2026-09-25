<?php

return [
    'midtrans' => (bool) env('MIDTRANS_ENABLED', false),
    'royalty' => (bool) env('ROYALTY_ENABLED', false),
    'payout' => (bool) env('PAYOUT_ENABLED', false),
    'author_kyc' => (bool) env('AUTHOR_KYC_ENABLED', false),
];
