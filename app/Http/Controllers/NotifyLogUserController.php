<?php

namespace App\Http\Controllers;

use App\Models\Notify_log;
use App\Models\Notify_log_user;
use App\Models\AutoNotify;
use App\Models\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class NotifyLogUserController extends Controller
{

    public function alertNotify(Request $request)
    {
        $notify_log_id = $request->notify_log_id;
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnError('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        $Notify_log_user = Notify_log_user::with('notify_log')
            ->orderby('id', 'desc');

        if (isset($notify_log_id)) {
            $Notify_log_user->where('notify_log_id', $notify_log_id);
        }

        $Notify_log_user->where('user_id', $loginBy->id);
        $Notify_log_user->where('read', false);

        $Notify_log_user = $Notify_log_user->get()->toarray();

        if (!empty($Notify_log_user)) {

            for ($i = 0; $i < count($Notify_log_user); $i++) {
                $Notify_log_user[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('ดำเนินการสำเร็จ', $Notify_log_user);
    }

    public function readNotify($id)
    {
        $Notify_log_user = Notify_log_user::with('notify_log')->find($id);
        $Notify_log_user->read = true;
        // $Notify_log_user->send = true;
        $Notify_log_user->save();

        return $this->returnSuccess('ดำเนินการสำเร็จ', $Notify_log_user);
    }


    public function get(Request $request)
    {

        $notify_log_id = $request->notify_log_id;
        $user_id = $request->user_id;
        $qouta_id = $request->qouta_id;

        $notify_log_user = Notify_log_user::with('notify_log')
            ->orderby('id', 'desc');

        if (isset($notify_log_id)) {
            $notify_log_user->where('notify_log_id', $notify_log_id);
        }

        if (isset($user_id)) {
            $notify_log_user->where('user_id', $user_id);
        }

        if (isset($qouta_id)) {
            $notify_log_user->where('qouta_id', $qouta_id);
        }

        $Notify_log_user = $notify_log_user->get()->toarray();

        if (!empty($Notify_log_user)) {

            for ($i = 0; $i < count($Notify_log_user); $i++) {
                $Notify_log_user[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('ดำเนินการสำเร็จ', $Notify_log_user);
    }

    public function Page(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $notify_log_id = $request->notify_log_id;
        $user_id = $request->user_id;
        $qouta_id = $request->qouta_id;


        $col = array('id', 'notify_log_id', 'user_id', 'qouta_id', 'read', 'created_at', 'updated_at');

        $orderby = array('id', 'notify_log_id', 'user_id', 'qouta_id', 'read', 'created_at', 'updated_at');

        $d = Notify_log_user::select($col)
            ->with('notify_log');

        if (isset($notify_log_id)) {
            $d->where('notify_log_id', $notify_log_id);
        }

        if (isset($user_id)) {
            $d->where('user_id', $user_id);
        }

        if (isset($qouta_id)) {
            $d->where('qouta_id', $qouta_id);
        }

        if ($orderby[$order[0]['column']]) {
            $d->orderby($orderby[$order[0]['column']], $order[0]['dir']);
        }
        if ($search['value'] != '' && $search['value'] != null) {

            //search datatable
            $d->where(function ($query) use ($search, $col) {
                foreach ($col as &$c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $d->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {

            //run no
            $No = (($page - 1) * $length);

            for ($i = 0; $i < count($d); $i++) {

                // $No = $No + 1;
                // $d[$i]->No = $No;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function notiAlert(Request $request)
    {
        if (!isset($request->title)) {
            return $this->returnErrorData('[title] Data Not Found', 404);
        } else  if (!isset($request->body)) {
            return $this->returnErrorData('[body] Data Not Found', 404);
        }
        DB::beginTransaction();

        try {

            foreach ($request->factories as $key0 => $value0) {
                foreach ($request->date as $key => $value) {
                    foreach ($value['time'] as $key1 => $value1) {
                        $Item = new AutoNotify();
                        $Item->factorie_id = $value0['factorie_id'];
                        $Item->date = $value['day'];
                        $Item->time = $value1['hour'];
                        $Item->title = $request->title;
                        $Item->message = $request->body;
                        $Item->save();
                    }
                }
            }

            

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }

        //send notification user
        $title = $request->title;
        $body = $request->body;
        $target_id = null;
        $type = 'all';
        $this->sendNotifyAll($title, $body, $target_id, $type);
    }

    public function getDate($title)
    {
        $Item = AutoNotify::select('date', 'title', DB::raw('count(*) as total')) // Adjust as needed
            ->where('title', $title)
            ->groupBy('date', 'title') // Include 'title' in the GROUP BY clause
            ->get();

        if ($Item) {
            foreach ($Item as $key => $value) {
                $Item[$key]->days = AutoNotify::where('title', $value['title'])
                    ->where('date', $value['date'])
                    ->get();
                    foreach ($Item[$key]->days as $key2 => $value2) {
                        $Item[$key]->days[$key2]->factorie = Factory::find($Item[$key]->days[$key2]->factorie_id);
                    }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function AutoNotify_del($id)
    {
        DB::beginTransaction();

        try {

            $Item = AutoNotify::find($id);
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

    public function AutoNotify_del_date(Request $request)
    {
        $title = $request->title;
        $date = $request->date;

        if (!isset($title)) {
            return $this->returnErrorData('[title] Data Not Found', 404);
        } else  if (!isset($date)) {
            return $this->returnErrorData('[date] Data Not Found', 404);
        }

        DB::beginTransaction(); 

        try {

            $Item = AutoNotify::where('date',$date)
            ->where('title',$title)
            ->delete();

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
}
