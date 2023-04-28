<?php



return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'field_exists'=> 'Dieses Feld ist nicht vorhanden.',
    'field_required' => 'Feld ist leer.',
    'delete_file'=>'Die Datei wurde erfolgreich gelöscht',
    'invoice_search'=>'Die Suche nach Rechnungen wurde abgeschlossen',
    'invoice_creation'=>'Rechnung erfolgreich erstellt',
    'success'=>'Erfolg',
    'message'=>'Die angegebenen Daten waren ungültig.',
    'invoice_update'=>"Rechnung erfolgreich aktualisiert",
    'no_invoice'=>"Keine Rechnung gefunden",
    'invoice_clone'=>"Rechnung wurde erfolgreich geklont",
    "invalid_invoice"=>"Unzulässige Rechnung",
    "invoice_not_existed"=>"Die Rechnung existiert nicht",
    'invoice_number' => [
        'required' => 'Rechnungsnummer ist erforderlich',
        'unique' => 'Diese Rechnungsnummer existiert bereits im System',
    ],
    'customer' => [
        'required' => 'Kunde ist erforderlich',
    ],
    'email' => [
        'required' => 'E-Mail ist erforderlich',
        'exists' => 'Diese E-Mail ist nicht in unserem System registriert',
    ]


];
