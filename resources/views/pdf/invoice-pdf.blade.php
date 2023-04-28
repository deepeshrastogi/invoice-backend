<?php
//$invoice = json_decode(json_encode($invoiceInfo), true);
$invoice =$invoiceInfo;
$subTotal = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Invoice</title>
<style>

body{ font-family:'sans-serif'; font-size:12px;}
            @page {
              margin: 30px 50px 30px 50px;
            }

            header {
                /*position: absolute;*/
                top: -190px;
                left: 0px;
                right: 0px;
                text-align: center;
            }

            footer {
                position: fixed;
                bottom: -60px;
                left: 0px;
                right: 0px;
                height: 50px;
                text-align: center;
                color:#ccc !important;
            }

            /*td{
              line-height:5px
            }*/
  
            thead tr {
              border-bottom:10px solid transparent;
            }

            #watermark {
                position: fixed;
                width:100%;
                top:850px;
                bottom:-60px;
                left:0px;
                height: 50px;
                z-index:  -1000;
            }

            p{ font-size:12px; padding-bottom:4px;}
</style>

</head>

<body>
<header>
<table width="100%">
  <tr>
    <td width="1%">&nbsp;</td>
    <td width="52%" style="color:#ccc; font-size:30px;height:70px;" valign="top"><span style="color:blue;"><strong>winkler</strong></span> immobilien</td>
    <td width="46%" style="color:#ccc; font-size:12px; text-align:right" valign="bottom">winkler Immobilien Management GmbH & Co. KG<br/>
Kurzer Weg 10<br/>
64285 Darmstadt   <br/>  
UST-ID:   DE 334 259 551<br/>
Steuer-Nr.:007 382 00477<br/>
Finanzamt: Darmstadt</td>
    <td width="1%">&nbsp;</td>
  </tr>
</table>

</header>
<div id="watermark">
  @if(!empty($invoiceInfo['is_invoice_signature_associated']))
  <table width="100%" style="color:#000; font-size:10px;">
    <tr>
        <td valign="top" width="40%">
          @if(!empty($invoiceInfo['invoice_signature']))
              <img style="width:400px; height:150px;" src="{{$invoiceInfo['invoice_signature']}}" />
              <br/>
              <br/>
          @endif
        </td>
        <td valign="top" width="30%"></td>
        <td valign="top" width="30%"></td>
    </tr>
  </table>
  @endif
  <table width="100%" style="color:#000; font-size:10px;">
    <tr>
        <td valign="top" width="40%">Winkler Immobilien Management GmbH & Co. KG<br/>
          Kurzer Weg 10<br/>
          64285 Darmstadt<br/>
          TEL: 06151 / 51044<br/>
          MAIL: info@o-winkler.de <br/></td>
                <td valign="top" width="30%">Geschaftsfuhrer: Jan Winkler<br/>
          HRB: 86276<br/>
          Gerichtsstand: Darmstadt<br/>
          St. Nr.: 007 382 00477<br/></td>
                <td valign="top" width="30%">
          Bank: Kreissparkasse Grob-Gerau<br/>
          IBAN: DE33 5085 2553 0016 1312 45<br/>
          BIC: HELADEF1GRG<br/></td>
      </tr>
  </table>
</div>

<table width="100%" cellspacing="0">
  <tr>
    <td>
      <table width="100%">
        <tr>
          <td width="100%" colspan="2">
            <strong>winkler immobilien management GmbH & CO.KG | kurzer weg 10 | 64285 darmstadt </strong>
          </td>

        </tr>
        <tr>
          <td width="60%">
            
            <p>{{ $customerInfo['firstName'] }} {{ $customerInfo['lastName'] }}<br />
            {{ $customerInfo['billingStreetName'] }} <br />
            {{ $customerInfo['billingCity'] }}, {{ $customerInfo['billingCountry'] }}</p>
          </td>
          <td width="40%">
            <p style="text-align:right;">unser Projektnummer: <strong>{{ $invoice['id'] }} </strong><br /> Rechnung Nummer: <strong>{{ $invoice['invoice_number'] }}/2022 </strong></p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table width="100%" cellspacing="0">
  <tr>
    <td style="border-bottom:1px solid;  font-weight:normal" width="75%"><h3>Rechnung</h3></td>
    <td style="border-bottom:1px solid;  font-weight:normal; text-align:right;" width="25%"><h3>Datum: {{ $date }}</h3></td>
  </tr>
  <tr>
    <td colspan="3">
      <table width="100%" celpadding="20">
        <thead>
          <tr>
            <th width="2%"><strong>Pos</strong></th>
            <th width="51%" align="left"><strong>Material / Leistungsbeschreibung</strong></th>
            <th width="12%" align="center"><strong>Menge</strong></th>
            <th width="17%" align="right"><strong>Einzelpreis</strong></th>
            <th width="20%" align="right"><strong>Ges.Preis</strong></th>
          </tr>
        </thead>
        <tbody style="border-top:1px solid transparent;">
            @foreach($invoice['project']['fields'] as $key=>$val)
                <tr style="margin-top: 10px;">
                  <td width="2%"><strong>{{$key+1}}</strong></td>
                  <td width="51%">
                    @if(!empty($val['field_title']))
                      {{ $val['field_title'] }}<br />
                    @endif
                    {{ $val['field_description'] }}
                  </td>
                  <td width="12%" align="center">{{ $val['field_quantity'] }}</td>
                  <td width="17%" align="right">{{ $val['field_rate'] }} €</td>
                  <td width="20%" align="right">{{ $val['field_amount'] }} €</td>
                </tr>
                @php $subTotal = $subTotal+ $val['field_amount'] @endphp 
                @if(isset($val['subfields']))
                  @foreach($val['subfields'] as $keysub=>$valsub)
                    <tr>
                      <td width="2%"><strong>{{ $key+1 }}.{{ $keysub+1 }}</strong></td>
                      <td width="51%">
                        @if(!empty($valsub['field_title']))
                          {{ $valsub['field_title'] }}<br />
                        @endif
                        {{ $valsub['field_description'] }}
                      </td>
                      <td width="12%" align="center">{{ $valsub['field_quantity'] }}</td>
                      <td width="17%" align="right">{{ $valsub['field_rate'] }} €</td>
                      <td width="20%" align="right">{{ $valsub['field_amount'] }} €</td>
                    </tr>
                    @php $subTotal = $subTotal+ $valsub['field_amount'] @endphp
                  @endforeach
                @endif
            @endforeach
        </tbody>
      </table>
    </td>
  </tr>
  
  <tr>
    <td colspan="3">
      <table width="100%" celpadding="20">
        <tbody style="border-top:1px solid #000;">
        <tr>
        <td width="20%">&nbsp;</td>
        <td width="40%">&nbsp;</td>
        <td width="30%"  align="right">Zwischensumme</td>
        <td width="10%" align="right">{{ $subTotal }} €</td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align="right">Steuer {{$invoiceInfo['invoice_tax_percentage']}}%</td>
        <td align="right">
          @php $invoiceTaxAmount = $subTotal * $invoiceInfo['invoice_tax_percentage']/100 @endphp
          {{$invoiceTaxAmount}} €
        </td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align="right"><strong>Insgesamt fällig</strong></td>
        <td align="right"><strong>{{ $subTotal + $invoiceTaxAmount }} € </strong></td>
        </tr>
        </tbody>
      </table>
    </td>
  </tr>
</table>
<table width="100%" celpadding="0">
  <tr>
    <td></td>  
    <td>
        <p>This invoice is valid from {{ $date }} until this invoice is replaced by a newly issued invoice. 
      invoice is replaced. The validity of the invoice expires by termination of the contractual relationship.</p>
        <p>Please transfer the full amount, quoting the invoice number:to the account indicated.</p>
    </td> 
    <td></td> 
  </tr>

</table>

</body>
</html>
<?php $amount = 0; $amount = $subTotal+($subTotal * 10/100); setInvoiceAmount($invoiceID,$amount);?>