<?php

namespace App\Http\Controllers;
use App\Mail\InvoiceSendMail;
use App\Models\Customer;
use App\Models\Fields;
use App\Models\Invoice;
use App\Models\InvoiceEmailed;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PDF;
use DB;

class DashboardController extends Controller {

    public function __construct() {

		$this->middleware('auth:api');
	}

    public function dashboardStats(Request $request) {

		$rawData = $request->all();
		$invoiceType = $rawData['type'];
		$userId = $rawData['userID'];
		$invoiceArray = array();


		if ($invoiceType == 1) {

			$invoicesPaid = Invoice::SELECT(DB::raw("COUNT(*) AS totalInvoicesRecived"),DB::raw("IFNULL(SUM(invoice.amount),0) AS totalAmount"))
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers_invoice.customers_id', $userId)
			->where('customers.user_id', "!=", $userId)
			->where('invoice.paying_status', "=", 1)
			->where('invoice.invoice_is_draft', "!=", 1)->get();

			$invoiceArray['PaidIncoming'] = $invoicesPaid;

			$invoicesUNpaid = Invoice::SELECT(DB::raw("COUNT(*) AS totalInvoicesRecived"),DB::raw("IFNULL(SUM(invoice.amount),0) AS totalAmount"))
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers_invoice.customers_id', $userId)
			->where('customers.user_id', "!=", $userId)
			->where('invoice.paying_status', "=",0)
			->where('invoice.invoice_is_draft', "!=", 1)->get();

			$invoiceArray['UnpaidIncoming'] = $invoicesUNpaid;

			$invoicesOverdue = Invoice::SELECT(DB::raw("COUNT(*) AS totalInvoicesRecived"),DB::raw("IFNULL(SUM(invoice.amount),0) AS totalAmount"))
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers_invoice.customers_id', $userId)
			->where('customers.user_id', "!=", $userId)
			->where('invoice.paying_status', "=",2)
			->where('invoice.invoice_is_draft', "!=", 1)->get();

			$invoiceArray['OverdueIncoming'] = $invoicesOverdue;

		}

		if ($invoiceType == 2) {

			$invoicesPaid = Invoice::SELECT(DB::raw("COUNT(*) AS totalInvoicesSent"),DB::raw("IFNULL(SUM(invoice.amount),0) AS totalAmount"))
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.paying_status', "=",1)
			->where('invoice.invoice_is_draft', "!=", 1)->get();

			$invoiceArray['paidSent'] = $invoicesPaid;

			$invoicesUnPaid = Invoice::SELECT(DB::raw("COUNT(*) AS totalInvoicesSent"),DB::raw("IFNULL(SUM(invoice.amount),0) AS totalAmount"))
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.paying_status', "=",0)
			->where('invoice.invoice_is_draft', "!=", 1)->get();

			$invoiceArray['unpaidSent'] = $invoicesUnPaid;

			$invoicesOverdue = Invoice::SELECT(DB::raw("COUNT(*) AS totalInvoicesSent"),DB::raw("IFNULL(SUM(invoice.amount),0) AS totalAmount"))
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.paying_status', "=",2)
			->where('invoice.invoice_is_draft', "!=", 1)->get();

			$invoiceArray['overdueSent'] = $invoicesOverdue;

		} 
		
		return response()->json([
			'status' => 'success',
			'statistics' => $invoiceArray,
			'filter'=> $invoiceType
		],200);
	}

    public function notifications(Request $request) {

		$rawData = $request->all();
		$userId = $rawData['userID'];
		$invoiceArray = array();

		$invoiceArray = Invoice::SELECT("invoice.invoice_created_at","company_profile.company_name","invoice.user_id")
		->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
		->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
        ->leftJoin('company_profile', 'company_profile.user_id', '=', 'customers.user_id')
		->where('customers_invoice.customers_id', $userId)
		->where('customers.user_id', "!=", $userId)
		->where('invoice.invoice_is_draft', "=", 1)->get();

		return response()->json([
			'status' => 'success',
			'notifications' => $invoiceArray,
		],200);
	}
}

?>