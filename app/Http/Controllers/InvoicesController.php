<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Models\invoices;
use App\Notifications\AddInvoices;
use App\Models\sections;
use App\Models\User;
use App\Models\incoices_details;
use App\Models\invoice_attachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exports\InvoiceExport;
use Maatwebsite\Excel\Facades\Excel;

class InvoicesController extends Controller
{
    function __construct()
{

$this->middleware('permission:عرض صلاحية', ['only' => ['index']]);
$this->middleware('permission:اضافة صلاحية', ['only' => ['create','store']]);
$this->middleware('permission:تعديل صلاحية', ['only' => ['edit','update']]);
$this->middleware('permission:حذف صلاحية', ['only' => ['destroy']]);

}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = sections::all();
        $invoices = invoices::all();
        return view('invoices.invoices', compact('invoices','sections'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections = sections::all();
        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'invoice_number' => 'required',
            'invoice_Date' => 'required',
            'Due_date' => 'required',
            'product' => 'required',
            'Amount_collection' => 'required',
            'Amount_Commission' => 'required',
            'note' => 'required',
        ],[
            'invoice_number.required' =>'يرجي ادخال رقم الفاتوره',
            'invoice_Date.required' =>'يرجي ادخال تاريخ الفاتوره',
            'Due_date.required' =>'يرجي ادخال رقم الاستحقاق',
            'product.required' =>'يرجي ادخال اسم المنتج',
            'Amount_collection.required' =>'   يرجى ادخال قيمه التحصيل',
            'Amount_Commission.required' =>'يرجي ادخال قيمه العموله',
            'note.required' =>'يرجي ادخال الملاحضه',
        ]);
        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);

        $invoice_id = invoices::latest()->first()->id;
        incoices_details::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name),
        ]);

        if ($request->hasFile('pic')) {

            $invoice_id = Invoices::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new invoice_attachments();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

          //////////////////////////////////////////////////////////////////////////////////
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);

            $user = User::first();
            Notification::send($user ,new AddInvoices($invoice_id));

            $user = User::get();
            $invoices = invoices::latest()->first();
            Notification::send($user, new \App\Notifications\NotyInvoices($invoices));

            session()->flash('Add', 'تم اضافة الفاتوره بنجاح ');
            return back();
    }

         
       

}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoices = invoices::where('id', $id)->first();
        $sections = sections::all();
        return view('invoices.edit_invoice', compact('sections', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $invoices = invoices::findOrFail($request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\invoices  $invoices
     * @return \Illuminate\Http\Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->invoice_id;
        $invoices = invoices::where('id', $id)->first();
        $Details = invoice_attachments::where('invoice_id', $id)->first();

         $id_page =$request->id_page;


        if (!$id_page==2) {

        if (!empty($Details->invoice_number)) {

            Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
        }

        $invoices->forceDelete();
        session()->flash('delete_invoice');
        return redirect('/invoices');

        }

        else {

            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('/Archive');
        }


    }

    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("Product_name", "id");
        return json_encode($products);
    }
    public function Status_Update($id, Request $request)
    {
        $invoices = invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            incoices_details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }

        else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            incoices_details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');
}
public function Invoice_Paid()
{
    $invoices = Invoices::where('Value_Status', 1)->get();
    return view('invoices.invoices_paid',compact('invoices'));
}

public function Invoice_unPaid()
{
    $invoices = Invoices::where('Value_Status',2)->get();
    return view('invoices.invoices_unpaid',compact('invoices'));
}

public function Invoice_Partial()
{
    $invoices = Invoices::where('Value_Status',3)->get();
    return view('invoices.invoices_Partial',compact('invoices'));
}

public function Print_invoice($id)
{
    $invoices = invoices::where('id', $id)->first();
    return view('invoices.Print_invoice',compact('invoices'));
}
//** */
//extract invoices escel
public function export() 
{
    return Excel::download(new InvoiceExport, 'قائمه الفواتير.xlsx');
}

public function MarkAsRead_all (Request $request)
{

    $userUnreadNotification= auth()->user()->unreadNotifications;

    if($userUnreadNotification) {
        $userUnreadNotification->markAsRead();
        return back();
    }


}


public function unreadNotifications_count()

{
    return auth()->user()->unreadNotifications->count();
}

public function unreadNotifications()

{
    foreach (auth()->user()->unreadNotifications as $notification){

return $notification->data['title'];

    }


}
}