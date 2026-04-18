<?php

declare(strict_types=1);

/**
 * Armazenamento e defaults de emissão fiscal (NF-e, NFC-e, NFS-e).
 * Caminhos relativos a base_path() salvo quando absoluto.
 */
return [
    'storage' => [
        'root' => 'storage/fiscal',
        'xml_unsigned' => 'xml/unsigned',
        'xml_signed' => 'xml/signed',
        'xml_authorized' => 'xml/authorized',
        'pdf' => 'pdf',
        'events' => 'events',
        'nfse_payloads' => 'nfse',
    ],

    /** Modelos (sped convenção) */
    'models' => [
        'nfe' => 55,
        'nfce' => 65,
        /** Placeholder até driver NFS-e definir numeração local */
        'nfse' => 1,
    ],

    /** Status internos além dos administrativos (draft, issued, …) */
    'status' => [
        'pending_transmission' => 'pending_transmission',
        'pending_authorization' => 'pending_authorization',
        'authorized' => 'authorized',
        'rejected' => 'rejected',
        'cancelled_sefaz' => 'cancelled_sefaz',
    ],
];
