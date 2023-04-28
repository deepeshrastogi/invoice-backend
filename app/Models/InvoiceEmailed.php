<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class InvoiceEmailed extends Model
{
    public $timestamps = false;
    protected $table = 'invoice_emailed';

    protected $guarded = ['id'];

    protected $fillable = ['invoice_id', 'sent_date','created_at'];

}

?>