<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Chat_msg;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatMsgController extends Controller
{
    public function getChatMsg(Request $request)
    {
        $ChatId = $request->chat_id;
        $type = $request->type;
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        $Chat_msg = Chat_msg::with('member')
            ->with('chat')
            ->where('chat_id', $ChatId);

        if ($type) {
            $Chat_msg->where('type', $type);
        }
        $Chat_msg = $Chat_msg->get()
            ->toarray();

        if (!empty($Chat_msg)) {

            for ($i = 0; $i < count($Chat_msg); $i++) {
                $Chat_msg[$i]['No'] = $i + 1;

                //positon comment
                if ($loginBy->type  == 'member') {
                    if ($loginBy->id == $Chat_msg[$i]['member_id']) {
                        $Chat_msg[$i]['positon_comment'] = 'Right';
                    } else {
                        $Chat_msg[$i]['positon_comment'] = 'Left';
                    }
                } else {
                    if ($Chat_msg[$i]['member_id']) {
                        $Chat_msg[$i]['positon_comment'] = 'Left';
                    } else {
                        $Chat_msg[$i]['positon_comment'] = 'Right';
                    }
                }


                //type img
                if ($Chat_msg[$i]['type'] == 'image') {
                    $Chat_msg[$i]['message'] = url($Chat_msg[$i]['message']);
                } else

                    //type file
                    if ($Chat_msg[$i]['type'] == 'file') {
                        $Chat_msg[$i]['message'] = url($Chat_msg[$i]['message']);
                    }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Chat_msg);
    }

    public function ChatMsgPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $ChatId = $request->chat_id;
        $type = $request->type;
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        $status = $request->status;

        $col = array('id', 'chat_id', 'user_id', 'member_id', 'message', 'type', 'status', 'created_at', 'updated_at');

        $orderby = array('', 'chat_id', 'user_id', 'member_id', 'message', 'type', 'status', 'created_at', 'updated_at');

        $d = Chat_msg::select($col)
            ->with('user')
            ->with('member')
            ->with('chat');

        //if
        if (isset($ChatId)) {
            $d->where('chat_id', $ChatId);
        }

        if (isset($type)) {
            $d->where('type', $type);
        }

        if (isset($status)) {
            $d->where('status', $status);
        }

        if ($orderby[$order[0]['column']]) {
            $d->orderby($orderby[$order[0]['column']], $order[0]['dir']);
        }
        if ($search['value'] != '' && $search['value'] != null) {

            $d->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->where(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                //search with
                $query = $this->withMember($query, $search);
            });
        }

        $d = $d->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {

            //run no
            $No = (($page - 1) * $length);

            for ($i = 0; $i < count($d); $i++) {

                $No = $No + 1;
                $d[$i]->No = $No;

                //positon comment
                if ($loginBy->type  == 'member') {
                    if ($loginBy->id == $d[$i]->member_id) {
                        $d[$i]->positon_comment = 'Right';
                    } else {
                        $d[$i]->positon_comment = 'Left';
                    }
                } else {
                    if ($d[$i]->member_id) {
                        $d[$i]->positon_comment = 'Left';
                    } else {
                        $d[$i]->positon_comment = 'Right';
                    }
                }

                //type img
                if ($d[$i]->type == 'image') {
                    $d[$i]->message = url($d[$i]->message);
                } else

                    //type file
                    if ($d[$i]->type == 'file') {
                        $d[$i]->message = url($d[$i]->message);
                    }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
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

        if (!isset($request->chat_id)) {
            return $this->returnErrorData('กรุณาระบุ chat_id ให้เรียบร้อย', 404);
        } else if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            $Chat_msg = new Chat_msg();
            $Chat_msg->chat_id = $request->chat_id;

            if ($loginBy->type  == 'member') {
                $Chat_msg->member_id = $loginBy->id;

                //update read chat 0
                $Chat = Chat::find($Chat_msg->chat_id);
                $Chat->meeting = 0;
                $Chat->save();
                //
            } else {
                $Chat_msg->user_id = $loginBy->id;

                //update read chat 1
                $Chat = Chat::find($Chat_msg->chat_id);
                $Chat->meeting = 1;
                $Chat->save();
                //
            }

            $Chat_msg->message = $request->message;

            //เชค url
            if ($request->type == 'text') {
                if ($this->isURL($Chat_msg->message) == false) {
                    $Chat_msg->type = $request->type;
                } else {
                    $Chat_msg->type = 'url';
                }
            } else {
                $Chat_msg->type = $request->type;
            }
            //
            $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

            $Chat_msg->save();

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Chat_msg);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Chat_msg = Chat_msg::with('user')
            ->with('member')
            ->with('chat')
            ->find($id);

        if ($Chat_msg) {

            //type img
            if ($Chat_msg->type == 'image') {
                $Chat_msg->message = url($Chat_msg->message);
            } else

                //type file
                if ($Chat_msg->type == 'file') {
                    $Chat_msg->message = url($Chat_msg->message);
                }
        }
        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Chat_msg);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($id)) {
            return $this->returnErrorData('ไม่พบข้อมูล id', 404);
        } else if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            $Chat_msg = Chat_msg::find($id);
            // $Chat_msg->course_id = $request->course_id;
            // $Chat_msg->member_id = $loginBy->id;

            $Chat_msg->message = $request->message;

            //เชค url
            if ($request->type == 'text') {
                if ($this->isURL($Chat_msg->message) == false) {
                    $Chat_msg->type = $request->type;
                } else {
                    $Chat_msg->type = 'url';
                }
            } else {
                $Chat_msg->type = $request->type;
            }
            //

            $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

            $Chat_msg->save();

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Chat_msg);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            $Chat_msg = Chat_msg::find($id);
            $Chat_msg->delete();

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }
}
