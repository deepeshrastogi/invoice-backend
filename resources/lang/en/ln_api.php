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

    'field_exists' => 'This field does not exist.',
    'field_required' => 'Field is empty.',
    'delete_file'=>'File has been deleted successfully',
    'invoice_search'=>'Invoice Search has been completed',
    'invoice_creation'=>'Invoice has been generated successfully',
     'success'=>'Success',
     'invoice_update'=>"Invoice updated successfully",
     'no_invoice'=>"No invoice Found",
     'invoice_clone'=>"Invoice has cloned successfully",
      "invalid_invoice"=>"Invalid Invoice",
      "invoice_not_existed"=>"Invoice doesn't exist",

   /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
    'invoice_number' => [
        'required' => 'Invoice number is required',
        'unique' => 'This invoice number already exists in system',
    ],
    'customer' => [
        'required' => 'Customer is required',
    ],
    'invoice_number' => [
        'required' => 'Invoice number is required',
        'unique' => 'This invoice number already exists in system',
    ],
    'customer' => [
        'required' => 'Customer is required',
    ],
    'email' => [
        'required' => 'Email is required',
        'exists' => 'This email is not registered with our system',
    ]

];
