<?php

namespace App\Http\Controllers;

use App\Models\incoices_details;
use App\Models\invoice_attachments;
use App\Models\invoices;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Notifiable;
use File;

class IncoicesDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('/notyall');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\incoices_details  $incoices_details
     * @return \Illuminate\Http\Response
     */
    public function show(incoices_details $incoices_details)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\incoices_details  $incoices_details
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function edit($id,$idnet)
    {
        $invoices = invoices::where('id',$id)->first();
        $details  = incoices_Details::where('id_Invoice',$id)->get();
        $attachments  = invoice_attachments::where('invoice_id',$id)->get();


        //dd($id, auth()->user()->unreadNotifications);

       $r= auth()->user()->unreadNotifications->where('id','=', $idnet);
       $r->markAsRead();


        return view('invoices.details_invoice',compact('invoices','details','attachments'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\incoices_details  $incoices_details
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, incoices_details $incoices_details)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\incoices_details  $incoices_details
     * @return \Illuminate\Http\Response
     */

        public function destroy(Request $request)
    {
        $invoices = invoice_attachments::findOrFail($request->id_file);
        $invoices->delete();
        Storage::disk('public_uploads')->delete($request->invoice_number.'/'.$request->file_name);
        session()->flash('delete', 'تم حذف المرفق بنجاح');
        return back();
    }

    public function get_file($invoice_number,$file_name)

    {
        $contents= Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number.'/'.$file_name);
        return response()->download( $contents);
    }



    public function open_file($invoice_number,$file_name)

    {
        $files = Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number.'/'.$file_name);
        return response()->file($files);
    }
}
