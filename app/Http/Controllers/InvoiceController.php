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

class InvoiceController extends Controller {
	//
	public function __construct() {

		$this->middleware('auth:api');
	}

	/**
	 * $request,  param to add i.e invoice, customers, projects, subfields
	 * @return Status, invoice
	 */

	public function store(Request $request) {

		$data = $request->all();

		$messages = [
			'invoice_number.required' => trans('ln_api.invoice_number.required'),
			'invoice_number.unique' => trans('ln_api.invoice_number.unique'),
			'user_id.required' => trans('ln_api.customer.required'),
		];
		
		$validator = Validator::make($data,[
			'invoice_number' => 'required|unique:invoice',
			'user_id' => 'required',
			'invoice_tax_percentage' => 'numeric|between:0,100',
			'project.fields.*.field_quantity' => 'numeric|between:0,1000',
			'project.fields.*.field_rate' => 'numeric|between:0,99999999999.99',
			'project.fields.*.field_amount' => 'numeric|between:0,99999999999999.99',
			'project.fields.*.subfields.*.field_quantity' => 'numeric|between:0,1000',
			'project.fields.*.subfields.*.field_rate' =>  'numeric|between:0,99999999999.99',
			'project.fields.*.subfields.*.field_amount' => 'numeric|between:0,99999999999999.99',
			'amount' => 'numeric|between:0,99999999999999.99',
		],$messages);
		
		
		if ($validator->fails()) {
			return response(['error' => $validator->errors(),'content' => null]);
		}


		if (empty($data)) {

			return response()->json([
				'status' => __("ln_api.success"),
				'message' => __("ln_api.invalid_requst"), // 'Invoice generated successfully',

			]);

		}
		$lastInvoiceId = Invoice::max('id');
		$lastdbRow = $lastInvoiceId ? ($lastInvoiceId + 1) : 1;
		$isInvoiceDraft = isset($data['invoice_is_draft']) ? $data['invoice_is_draft'] : "";

		if (!$isInvoiceDraft) {

			$invoiceNumber = str_pad($lastdbRow, 3, '0', STR_PAD_LEFT); // returns 04
			$data['invoice_number'] = $invoiceNumber;
		}
		
		$invoice = Invoice::create(
			$data
		);

		isset($data['customer_id']) ? $invoice->customer()->attach($data['customer_id']) : "";
		$project = isset($data['project']) ? $data['project'] : "";

		//Adding Projects of invoices
		if (!empty($project)) {

			$projectObj = Project::create(
				$project
			);

			$fields = isset($project['fields']) ?
			$project['fields'] : "";

			//Subfield settlement
			if (!empty($fields)) {
				foreach ($fields as $field) {

					$fieldObj = Fields::create(
						$field
					);
					$fieldParentId = $fieldObj->id;

					$projectObj->field()->attach($fieldParentId);

					if (!empty($field['subfields'])) {
						$subFields = $field['subfields'];
						foreach ($subFields as $subField) {

							$subField['parent_id'] = $fieldParentId;

							$subFieldObj = Fields::create(
								$subField
							);

							$projectObj->field()->attach($subFieldObj->id);

						}

					}

				}

			}
			$invoice->project()->attach($projectObj['id']);

		}
		$fetchInvoiceResult = $this->fetchInvoice($invoice->id);

		return response()->json([
			'status' => __("ln_api.success"),
			'message' => __("ln_api.invoice_creation"), // 'Invoice generated successfully',
			'invoice' => $fetchInvoiceResult,
		]);

	}

	/**
	 * This function finds result from invoice, projects and subfield table
	 * $request, search param
	 * @return Status, result
	 */
	
	public function search(Request $request) {

		$request->validate([
			'search' => 'required',
		]);

		$size = !empty($request->size) ? $request->size : 10 ;
		$invoiceArray = Invoice::SELECT('invoice.*','customers.*')
		->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
		->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id');

		if ($request->has('search')) {
			$search = $request->search;
			$invoiceArray = $invoiceArray->where('invoice.invoice_number', 'LIKE', '%' . $search . '%')
			->orWhere('invoice.amount', 'LIKE', '%' . $search . '%')
			->orWhere('customers.accountingEmailAddress', 'LIKE', '%' . $search . '%')
			->orWhere('customers.companyName', 'LIKE', '%' . $search . '%');
			if(!empty($search) && (bool) strtotime($search) === true && !is_numeric($search)){
				$searchDate = date('Y-m-d',strtotime($search));
				$invoiceArray = $invoiceArray->orWhereDate('invoice.invoice_created_at', '=', $searchDate);
			}
		}

		$invoiceArray = $invoiceArray->paginate($size);

		return response()->json([
			'status' => __("ln_api.success"),
			'message' => __("ln_api.invoice_search"),
			'result' => $invoiceArray,
		]);
	}

	/**
	 * This function delete field, subfield
	 * $request, fieldId
	 * @return Status, result
	 */

	public function deleteField($fieldId) {
		$data['field_id'] = $fieldId;
		$validator = Validator::make($data, [
			'field_id' => 'required|numeric|exists:fields,id',

		],
			[
				'field_id.required' => __("api.field_required"),
				'field_id.exists' => __("api.field_exists"),

			]

		);
		if ($validator->fails()) {
			return response()->json($validator->errors(), 400);
		}

		$fields = Fields::with('subfields')->where('fields.id', $fieldId)->first();
		$allSubFields = $fields->subfields()->get();
		//deleting and detaching(from projects) sub fields
		if (!empty($allSubFields)) {

			foreach ($allSubFields as $subField) {

				$subField->project()->detach();
				$subField->delete();
			}
		}
		//detaching main field from project, then delete
		$fields->project()->detach();
		$fields->delete();

	}

/*
 ** This function is for edit field, subfield(In progress)
 * $request, fieldId
 * @return Status, result
 */
	public function editInvoice(Request $request) {

		$request->validate([
			'id' => 'required',
		]);

		$data = $request->all();

		$invoiceId = $data['id'];
		$invoice = Invoice::where('id', $invoiceId)->first();

		if (empty($invoice)) {

			return response()->json([
				'status' => __("ln_api.success"),
				'message' => __("ln_api.no_invoice"),

			]);
		}

		$invoice->update($data);
		isset($data['customer_id']) ? $invoice->customer()->sync($data['customer_id']) : '';

		$project = isset($data['project']) ? $data['project'] : "";
		$checkIfProjectAlreadyExist = $invoice->project()->first();

		$pid = isset($data['project']['id'])?$data['project']['id']:"";
		$pr = $pid ? Project::where('id', $pid)->first() : "";
		$doProjectDelete = isset($data['project']['do_delete']) ? $data['project']['do_delete'] : "";

		//Adding Projects of invoices
		if (!empty($project)) {
			//if project doesn't exist
			$pr = $checkIfProjectAlreadyExist;

			if (empty($pr) && empty($checkIfProjectAlreadyExist)) {

				$projectObj = Project::create(
					$project
				);

				$invoice->project()->attach($projectObj['id']);
				$pr = Project::where('id', $projectObj['id'])->firstOrFail();

			} else {

				$projectObj = $pr->update(
					$project
				);

			}

			//Subfield settlement
			$fields = $data['project']['fields'];

			if (!empty($fields)) {

				foreach ($fields as $field) {
					$fid = isset($field['id']) ? $field['id'] : "";

					$fieldObj = $fid ? Fields::where('id', $fid)->first() : "";
					$doMainFieldDelete = isset($field['do_delete']) ? $field['do_delete'] : "";

					//incase mail field has to create
					if (empty($fid)) {

						$fieldObj = Fields::create(
							$field
						);
						$field['id'] = $fieldObj->id;
						$fid = $fieldObj->id;

						$pr->field()->attach($fieldObj->id);

					}

					if (!empty($field['subfields'])) {
						//$fieldParentId = isset($field['id'])?$field['id']:"";
						$subFieldId = isset($field['subfields']['id']) ? $field['subfields']['id'] : "";
						$subFields = $field['subfields'];

						foreach ($subFields as $subField) {

							$subFieldId = isset($subField['id']) ? $subField['id'] : "";
							$DoSubFieldDelete = isset($subField['do_delete']) ? $subField['do_delete'] : "";
							$subFieldObj = Fields::where('id', $subFieldId)->first();
							// Update fields
							if ($subFieldId && !$DoSubFieldDelete && !empty($subFieldObj)) {

								$subFieldObj = $subFieldObj->update(
									$subField
								);

							}
							//This will create new subfield
							else if (empty($subFieldId) && empty($DoSubFieldDelete)) {

								$subField['parent_id'] = $fid;

								$subFieldObj = Fields::create(
									$subField
								);

								$pr->field()->attach($subFieldObj->id);

								//delete field
							}
							//if main field to delete
							//condition 1. if main field delete set in request
							//condition2. Project delete has been set
							//condition3. if subfield has to delete
							else if ($DoSubFieldDelete || $doMainFieldDelete || $doProjectDelete) {

								$this->deleteField($subFieldId);
							}

						}

					}

					//if main field to delete
					//condition 1. if main field delete set in request
					//condition2. Project delete has been set
					//if project delete
					if (($fieldObj && $doMainFieldDelete) || $doProjectDelete) {

						$this->deleteField($fid);
						continue;
					}

				}

			}

			$invoice->project()->attach($pr['id']);
		}
		//if project delete set
		if ($doProjectDelete && !empty($pr)) {
			// $pr->detach();
			$pr->delete();

		}

		$fetchInvoiceResult = $this->fetchInvoice($invoiceId);

		return response()->json([
			'status' => __("ln_api.success"),
			'message' => __("ln_api.invoice_update"),
			'invoice' => $fetchInvoiceResult,
		]);

	}

	#INVOICE PRINT PDF
	public function invoice_print(Request $request) {

		$invoiceID = $request->invoiceID;

		$returnPath = $this->invoice_print_pdf($invoiceID);

		return response()->json([
			'status' => 'success',
			'message' => 'Invoice print successfully',
			'invoicePath' => $returnPath,
		],200);
	}

/*
 ** This function clone the api
 * $request, invoice_id
 * @return Status, result
 */

	public function cloneInvoice(Request $request) {

		$getData = $request->all();

		if (!empty($getData)) {

			return response()->json([
				/*'status' => __("ln_api.success"),
				'message' => __("ln_api.invalid_invoice"),*/

				'status' => __("success"),
				'message' => __("Invalid Invoice"),

			]);

		}
		$invoiceId = $getData['invoice_id'];
		$data = $this->fetchInvoice($invoiceId);
		unset($data['id']);
		unset($data['invoice_number']);

		$invoice = Invoice::create(
			$data
		);

		isset($data['customer_id']) ? $invoice->customer()->attach($data['customer_id']) : "";
		$project = isset($data['project']) ? $data['project'] : "";

		//Adding Projects of invoices
		if (!empty($project)) {

			unset($project['id']);

			$projectObj = Project::create(
				$project
			);

			$fields = isset($project['fields']) ?
			$project['fields'] : "";

			//Subfield settlement
			if (!empty($fields)) {
				foreach ($fields as $field) {

					unset($field['id']);

					$fieldObj = Fields::create(
						$field
					);
					$fieldParentId = $fieldObj->id;

					$projectObj->field()->attach($fieldParentId);

					if (!empty($field['subfields'])) {
						$subFields = $field['subfields'];
						foreach ($subFields as $subField) {

							unset($subField['id']);

							$subField['parent_id'] = $fieldParentId;

							$subFieldObj = Fields::create(
								$subField
							);

							$projectObj->field()->attach($subFieldObj->id);

						}

					}

				}

			}

			$invoice->project()->attach($projectObj['id']);

		}
		$clone = $this->fetchInvoice($invoice['id']);

		return response()->json([
			/*'status' => __("ln_api.success"),
			'message' => __("ln_api.invoice_clone"),*/
			'status' => __("success"),
			'message' => __("Invoice Clone"),
			'result' => $clone,
		]);

	}

/*
 ** This function fetch invoice
 * $params, invoice_id
 * @return invoice result
 */

	public function fetchInvoice($invoiceId) {

		$fetchInvoice = Invoice::where('id', $invoiceId)->first();
		$fetchInvoiceResult = array();

			/*$fetchInvoice = Invoice::select("invoice.*","customers_invoice.*",DB::raw("DATE_FORMAT(invoice_created_at,'%Y-%m-%d') AS invoice_date"),"customers.*")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('invoice.id', $invoiceId)->first();*/

		$fetchCustomer = Invoice::select(DB::raw("DATE_FORMAT(invoice_created_at,'%Y-%m-%d') AS invoice_date"),"customers.*")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('invoice.id', $invoiceId)->first();

			if(!empty($fetchInvoice)){

				$fetchInvoiceResult = $fetchInvoice->toArray();
				$fetchProject = $fetchInvoice->project()->first();
	
				$fs = array();
				if (!empty($fetchProject)) {
	
					$fields = $fetchProject->field()
						->where('fields.parent_id', 0)
						->get()
						->pluck('id')
						->toArray();
	
					$fs = Fields::with('subfields')
						->whereIn('id', $fields)
						->orderBy('field_title', 'asc')
						->get()->toArray();
	
					$fetchInvoiceResult['project'] = $fetchProject->toArray();
				}
	
				$fetchInvoiceResult['project']['fields'] = $fs;

				if(!empty($fetchCustomer)){
					$fetchInvoiceResult['customer'] = $fetchCustomer->toArray();
				}else{
					$fetchInvoiceResult['customer'] = array();
				}

			}


		return $fetchInvoiceResult;
	}

	#INVOICE PRINT PDF
	public function invoicePrint(Request $request) {

		$invoiceID = $request->invoiceID;

		$returnPath = $this->invoicePrintPdf($invoiceID);

		if ($returnPath != "NA") {

			return response()->json([
				'status' => 'success',
				'message' => 'Invoice print successfully',
				'invoicePath' => $returnPath,
			],200);

		} else {

			return response()->json([
				'status' => 'error',
				'message' => 'Invoice doesnot Exist!',
				'invoicePath' => "",
			],401);
		}

	}

	public function invoicePrintPdf($invoiceID = 0) {

		$customerInfo = array();
		$returnVal = "NA";

		$invoiceArray = $this->fetchInvoice($invoiceID);

		if(!empty($invoiceArray)) {

				$data = [
					'title' => 'winkler immobilien',
					'date' => date('d.m.Y'),
					'invoiceID' => $invoiceID,
					'invoiceInfo' => $invoiceArray,
					'customerInfo' => $invoiceArray['customer'],
				];

				$pdf = PDF::loadView('pdf.invoice-pdf', $data);
				
				$path = public_path('invoices');
				$pdfPath = $path . '/' . 'invoice-' . $invoiceID . '.pdf';
				$pdf->save($pdfPath);

				$returnVal = url('invoices/' . 'invoice-' . $invoiceID . '.pdf');

				return $returnVal;

			} else{
				return $returnVal;

			}

	}

		
	#FETCH SINGLEINVOICE BY ID
	public function getInvoice(Request $request) {

		$invoiceID = $request->invoiceID;
		$customerInfo = array(); $invoiceArray =array();
		
		$invoiceArray = $this->fetchInvoice($invoiceID);

		if(!empty($invoiceArray)) {

				return response()->json([
					'status' => __("ln_api.success"),
					'message' => __("ln_api.invoice_update"),
					'invoiceID' => $invoiceID,
					'invoice' => $invoiceArray,
				],200);

			} else{
				return response()->json([
					'status' => 'error',
					'message' => 'Invoice doesnot Exist!',
					'invoice' => $invoiceArray
				],400);

			}

	}

	public function invoicePdfSend(Request $request) {

		$customerInfo = array();
		$returnVal = "NA";
		$invoiceID = $request->invoiceID;
		
		$invoiceArray = $this->fetchInvoice($invoiceID);

		if(!empty($invoiceArray)) {

			$data = [
				'title' => 'winkler immobilien',
				'date' => date('m.d.Y'),
				'invoiceID' => $invoiceID,
				'invoiceInfo' => $invoiceArray,
				'customerInfo' => $invoiceArray['customer'],
			];

			$pdf = PDF::loadView('pdf.invoice-pdf', $data);

			$path = public_path('invoices');
			$fileName = 'invoice-' . $invoiceID . '.pdf';
			$pdfPath = $path . '/' . $fileName;
			$pdf->save($pdfPath);

			#SAVE SENT INVOICE INFO
			$invoiceEmailed['invoice_id'] = $invoiceID;
			$invoiceEmailed['sent_date'] = date('Y-m-d h:i:s');
			$invoiceEmailed['created_at'] = $invoiceID;

			$invoiceEmaiedInfo = InvoiceEmailed::create(
				$invoiceEmailed
			);

			$emailData = array('fromEmail' => $invoiceArray['customer']['accountingEmailAddress'] != "" ? $invoiceArray['customer']['accountingEmailAddress'] : 'neeraj.kaushal@maracana.in', 'fromName' => 'winkler immobilien', "subject" => 'Invoice', "emailBody" => 'Please attach Invoice', 'fileName' => $fileName);
			Mail::to('neeraj.kaushal@maracana.in')->send(new InvoiceSendMail($emailData));

			return response()->json([
				'status' => 'success',
				'message' => 'Invoice sent in email!',
			],200);

		}else{
			return response()->json([
				'status' => 'success',
				'message' => 'Invoice not found!',
			],401);
		}
		

	}

/*
 ** This function fetch Fetches Invoice List
 * $params, type(1 incoming invoices, 2 outgoing inovices, 3 drafts, 4 Recurring Invoice,5 NON RECURRING, 6 Paid,7 Overdue, 8 unpaid). user_id
 * @return invoice result
 */

	public function invoiceSentList(Request $request) {

		$rawData = $request->all();
		$getFilterType = $rawData['type'];
		$userId = $rawData['user_id'];
		$invoiceSent = array();
		$invoiceArray = array();
		$sortBY ='invoice.invoice_created_at';
		$sortOrder = "desc";


		if ($request->has('size')) {
			$size = $request->size;
		} else {
			$size = 10;
		}

		if ($request->has('per_page')) {
			$size = $request->per_page;
		} else {
			$size = 10;
		}

		$sortColumns = array("byDate"=>'invoice.invoice_created_at',"byInvoiceno"=>'invoice.invoice_number');
		if($request->has('sort_by') && $request->has('order')){
			$sortBY =$sortColumns[$request->sort_by];
			$sortOrder = $request->order;
		}

		if ($getFilterType == 1) {

			$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers_invoice.customers_id', $userId)
			->where('customers.user_id', "!=", $userId)
			->where('invoice.invoice_is_draft', "!=", 1)
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		}

		if ($getFilterType == 2) {

			$invoiceArray = Invoice::SELECT("invoice.*",DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.invoice_is_draft', "!=", 1)
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		} 
		if ($getFilterType == 3) {

			$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.invoice_is_draft', "=", 1)
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		} if ($getFilterType == 4) {

			#FOR RECURRING INVOICE FILTER CODE 4
			$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.invoice_is_recurring', "=", 1)
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		}
		if ($getFilterType == 5) {

			#FOR NON RECURRING INVOICE FILTER CODE 5
			$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.invoice_is_recurring', "=", 0)
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		}
		if ($getFilterType == 6) {

			#FOR PAID INVOICE FILTER CODE 6
			$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.paying_status', "=", '1')
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		}
		if ($getFilterType == 7) {

			#FOR INVOICE PAYMENT OVERDUE FILTER CODE 7
			$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.paying_status', "=", '2')
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		}
		if ($getFilterType == 8) {

			#FOR INVOICE UNPAID FILTER CODE 8
			$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
			"customers_invoice.customers_id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
			"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
			"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
			"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
			"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
			->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
			->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
			->where('customers.user_id', $userId)
			->where('invoice.paying_status', "=", '0')
			->orwhereNull('invoice.paying_status')
			->orderBy($sortBY, $sortOrder)
			->paginate($size);

		}
		return response()->json([
			'status' => 'success',
			'invoices' => $invoiceArray,
			'filter'=> $getFilterType
		]);
	}

/*
 ** This function changes the invoice statuses
 * $params, type(0 unpaid or empty, 1 paid , 2 overdue).
 * @return success
 */
	public function changeInvoiceStatus(Request $request) {

		$request->validate([
			'invoice_id' => 'required|integer|exists:invoice,id',

		],

			[
				'invoice_id' => __("ln_api.field_required"),
				'invoice_id.exists' => __("ln_api.invoice_not_existed"),

			]

		);

		$rawData = $request->all();
		$invoiceId = $rawData['invoice_id'];
		$invoice = Invoice::where('id', $invoiceId)->first();
		$invoice->paying_status = isset($rawData['paying_status']) ? $rawData['paying_status'] : "0";
		$invoice->update();

		return response()->json([
			'status' => 'success',
			'message' => __("ln_api.invoice_update"),
		]);

	}


/*
 ** This function
 * $params(user_id,fromDate,toDate).
 * @return success
 */

public function invoiceListByDate(Request $request) {
	if($request->has('user_id')){

		$rawData = $request->all();
		$userId = $rawData['user_id'];
		$whereDateBetween = [];
		$size = !empty($request->size) ? $request->size : (!empty($request->per_page) ? $request->per_page : 10);

		if ($request->has('fromDate') && $request->has('toDate')) {
			$whereDateBetween = [$request->fromDate, $request->toDate];
		}
	
		$invoiceArray = Invoice::SELECT('invoice.*',DB::raw("DATE_FORMAT(invoice.invoice_created_at,'%d-%b-%Y') AS invoice_date"),
		"customers.id as custID","customers.companyID","customers.user_id","customers.companyName","customers.firstName",
		"customers.lastName","customers.phoneNumber","customers.accountingEmailAddress","customers.billingStreetName",
		"customers.billingStreetNumber","customers.billingZipcode","customers.billingCity","customers.billingCountry",
		"customers.billingAdditionalInfo","customers.shippingStreetName","customers.shippingStreetNumber",
		"customers.shippingZipcode","customers.shippingCity","customers.shippingCountry","customers.shippingAdditionalInfo")
		->leftJoin('customers_invoice', 'customers_invoice.invoice_id', '=', 'invoice.id')
		->leftJoin('customers', 'customers.id', '=', 'customers_invoice.customers_id')
		->where('customers_invoice.customers_id', $userId);
		
		if(!empty($whereDateBetween)){
			$invoiceArray = $invoiceArray->whereBetween('invoice.invoice_created_at', $whereDateBetween);
		}

		$invoiceArray = $invoiceArray->paginate($size);
		return response()->json([
			'status' => 'success',
			'invoices' => $invoiceArray
		],200);

	} else{

		return response()->json([
			'status' => 'error',
			'message' => 'Unauthorized',
		]);

	}

}


}
