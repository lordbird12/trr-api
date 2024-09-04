<?php

namespace App\Http\Controllers;

use App\Models\Notify_log;
use App\Models\Notify_log_user;
use Illuminate\Http\Request;

class NotifyLogUserController extends Controller
{
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
}
