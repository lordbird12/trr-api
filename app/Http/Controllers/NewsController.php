<?php

namespace App\Http\Controllers;

use App\Models\News;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    public function getList()
    {
        $Item = News::where('is_use','1')->get()->toarray();

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

        $col = array('id', 'code', 'image',  'views', 'title', 'detail','is_use', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'image',  'views', 'title', 'detail','is_use', 'status', 'create_by');

        $D = News::select($col);

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
                $d[$i]->image = url($d[$i]->image);
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
            $Item = new News();
            $prefix = "#N-";
            $id = IdGenerator::generate(['table' => 'news', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);
            $Item->code = $id;
            $Item->title = $request->title;
            $Item->detail = $request->detail;
            $Item->is_use = $request->is_use;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/news/');
            }

            $Item->save();
            //

            if($request->notify_status == 1){
                //send notification user
                //send notification user
                $title = 'แจ้งข่าวสาร';
                $body = $Item->title;
                $target_id = $Item->id;
                $type = 'news';
                $this->sendNotifyAll($title, $body, $target_id, $type);
              }


            //เคสกรณีส่งระบุคน ใช้คำสั่งนี้ $qoutaId ที่ได้จาก api  เป็น array id เช่น [477,327]
            // $this->sendNotifyMultiUser($title, $body, $target_id, $type, $userId);

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

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        DB::beginTransaction();


        try {

            $Item = News::find($id);
            $Item->views = $Item->views + 1;
            $Item->save();

            if ($Item) {
                $Item->image = url($Item->image);
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
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function edit(News $news)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, News $news)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = News::find($id);
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
            $Item = News::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการ', 404);
            }

            $Item->title = $request->title;
            $Item->detail = $request->detail;
            $Item->is_use = $request->is_use;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/news/');
            }

            $Item->save();
            //

            if($request->notify_status == 1){
                //send notification user
                $title = 'แจ้งข่าวสาร';
                $body = $Item->title;
                $target_id = $Item->id;
                $type = 'news';
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
