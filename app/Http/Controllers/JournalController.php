<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function getList()
    {
        $Item  = Journal::where('is_use','1')->get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $Status = $request->status;

        $col = array('id', 'no', 'code', 'image', 'title','is_use', 'detail', 'file', 'views', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'no', 'code', 'image', 'title','is_use', 'detail', 'file', 'views', 'status', 'create_by');

        $D = Journal::select($col);

        if (isset($Status)) {
            $D->where('status', $Status);
        }

        if ($orderby[$order[0]['column']]) {
            $D->orderby($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if ($search['value'] != '' && $search['value'] != null) {

            $D->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orWhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                //search with
                // $query = $this->withPermission($query, $search);
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {

            //run no
            $No = (($page - 1) * $length);

            for ($i = 0; $i < count($d); $i++) {

                $No = $No + 1;
                $d[$i]->No = $No;
                $d[$i]->image = $d[$i]->image ? url($d[$i]->image) : null;
                $d[$i]->file = $d[$i]->file ? url($d[$i]->file) : null;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $loginBy = $request->login_by;

        if (!isset($request->title)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new Journal();
            $prefix = "#JOU-";
            $id = IdGenerator::generate(['table' => 'journals', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);
            $Item->code = $id;
            $Item->no = $request->no;
            $Item->title = $request->title;
            $Item->detail = $request->detail;

            if ($request->file && $request->file != null && $request->file != 'null') {
                $Item->file = $this->uploadFile($request->file, '/files/asset/');
            }

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/journal/');
            }

            $Item->save();
            //

            if($request->notify_status == 1){
                //send notification user
                $title = 'แจ้งวารสาร';
                $body = $Item->title;
                $target_id = $Item->id;
                $type = 'journal';
                $this->sendNotifyAll($title, $body, $target_id, $type);
              }

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('ไม่สามารถบันทึกได้ กรุณาตรวจสอบความถูกต้องของไฟล์ข้อมูล', 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Journal  $journal
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        DB::beginTransaction();


        try {

            $Item = Journal::find($id);
            $Item->views = $Item->views + 1;
            $Item->save();

            if ($Item) {
                $Item->image = url($Item->image);
                $Item->file = url($Item->file);
                $Item->is_use = $Item->is_use == null ? 0:$Item->is_use;
            }

            DB::commit();

            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Journal  $journal
     * @return \Illuminate\Http\Response
     */
    public function edit(Journal $journal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Journal  $journal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Journal $journal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Journal  $journal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Journal::find($id);
            $Item->delete();

            //log
            $userId = "admin";
            $type = 'ลบผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function updateData(Request $request)
    {
        if (!isset($request->id)) {
            return $this->returnErrorData('[id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {
            $Item = Journal::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการ', 404);
            }

            $Item->no = $request->no;
            $Item->title = $request->title;
            $Item->detail = $request->detail;
            $Item->is_use = $request->is_use;

            if ($request->file && $request->file != null && $request->file != 'null') {
                $Item->file = $this->uploadFile($request->file, '/files/asset/');
            }

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/journal/');
            }

            $Item->save();
            //

            if($request->notify_status == 1){
              //send notification user
              $title = 'แจ้งวารสาร';
              $body = $Item->title;
              $target_id = $Item->id;
              $type = 'journal';
              $this->sendNotifyAll($title, $body, $target_id, $type);
            }

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}
