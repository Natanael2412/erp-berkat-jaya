<?php

/*
|--------------------------------------------------------------------------
| Informasi Toko Listrik Berkat Jaya (Landing Page Publik Jualan)
|--------------------------------------------------------------------------
*/

return [
    'name' => env('STORE_NAME', 'Berkat Jaya'),
    'tagline' => env('STORE_TAGLINE', 'Toko Listrik Terlengkap & Terpercaya'),
    'whatsapp' => env('STORE_WHATSAPP', '6281234567890'),
    'phone' => env('STORE_PHONE', '0812-3456-7890'),
    'address' => env('STORE_ADDRESS', 'Toko Berkat Jaya - Sedia Alat Listrik, Kabel, Lampu & Perlengkapan Instalasi'),
    'hours' => env('STORE_HOURS', 'Senin - Sabtu: 08.00 - 17.00 WIB'),

    // Tautan langsung ke Google Maps toko sesuai yang diberikan user
    'maps_url' => env('STORE_MAPS_URL', 'https://maps.google.com/maps?vet=10CAAQoqAOahcKEwjAu4GyqsGXAxUAAAAAHQAAAAAQCQ..i&udm&fvr=1&pvq=Cg0vZy8xMXM3NjJsNHNzIhYKEHRva28gYmVya2F0IGpheWEQAhgD&lqi=ChB0b2tvIGJlcmthdCBqYXlhWhIiEHRva28gYmVya2F0IGpheWGSARFlbGVjdHJvbmljc19zdG9yZQ&cs=1&um=1&ie=UTF-8&fb=1&gl=id&sa=X&ftid=0x2e708bb91ad3ac63:0x758c36b20c854421'),

    // URL embed iframe Google Maps
    'maps_embed' => env('STORE_MAPS_EMBED', 'https://maps.google.com/maps?q=toko+berkat+jaya&ftid=0x2e708bb91ad3ac63:0x758c36b20c854421&hl=id&z=16&output=embed'),
];
