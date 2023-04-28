<?php 
use App\Models\Invoice;

function setInvoiceAmount($invoiceID,$invoiceAmount){
    $updateInvoice = Invoice::where('id',$invoiceID)->update(['amount'=>$invoiceAmount]);
}
?>