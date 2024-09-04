<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Chat_msg;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function getChat(Request $request)
    {

        $qoutaId = $request->qouta_id;
        $type = $request->type;

        $status = $request->status;

        $Chat = Chat::with('frammer')
            ->with('chat_msgs');


        if (isset($qoutaId)) {
            $Chat->where('qouta_id', $qoutaId);
        }


        if (isset($type)) {
            $Chat->where('type', $type);
        }

        if (isset($status)) {
            $Chat->where('status', $status);
        }

        $Chat = $Chat->get()
            ->toarray();

        if (!empty($Chat)) {

            for ($i = 0; $i < count($Chat); $i++) {
                $Chat[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Chat);
    }

    public function ChatPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;


        $qoutaId = $request->qouta_id;

        $type = $request->type;

        $status = $request->status;

        $col = array('id', 'room_name', 'qouta_id', 'type', 'status', 'meeting', 'co_agent', 'created_at', 'updated_at');

        $orderby = array('', 'room_name', 'qouta_id', 'meeting', 'type', 'status', 'meeting', 'co_agent', 'created_at', 'updated_at');

        $d = Chat::select($col)
            ->with('frammer')
            ->with('chat_msgs');


        if (isset($qoutaId)) {
            $d->where('qouta_id', $qoutaId);
        }

        if (isset($assetId)) {
            $d->where('asset_id', $assetId);
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

        $qoutaId = $request->qouta_id;

        DB::beginTransaction();

        try {

            $CHAT = null;
            //check duplicate Asset Chat
            $checkChat = Chat::where('qouta_id', $qoutaId)
                ->where('status', 'chat')
                ->first();

            if ($checkChat) {
                $CHAT = $checkChat;
            } else {

                $Chat = new Chat();

                $Chat->room_name = date('Ymdhis') . rand(0000, 9999) . $qoutaId;
                $Chat->qouta_id = $qoutaId;
                $Chat->type = 'vip';
                $Chat->updated_at = Carbon::now()->toDateTimeString();

                $Chat->save();
                $CHAT =  $Chat;
            }

            DB::commit();

            //get msg
            $ChatId = $CHAT->id;

            $Chat_msg = Chat_msg::with('frammer')
                ->with('user')
                ->with('chat')
                ->where('chat_id', $ChatId);

            $Chat_msg = $Chat_msg->get()
                ->toarray();

            if (!empty($Chat_msg)) {

                for ($i = 0; $i < count($Chat_msg); $i++) {
                    $Chat_msg[$i]['No'] = $i + 1;

                    //positon comment
                    if ($qoutaId) {
                        if ($qoutaId  == $Chat_msg[$i]['qouta_id']) {
                            $Chat_msg[$i]['positon_comment'] = 'Right';
                        } else {
                            $Chat_msg[$i]['positon_comment'] = 'Left';
                        }
                    } else {
                        if ($Chat_msg[$i]['qouta_id']) {
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

            return $this->returnSuccess('ดำเนินการสำเร็จ', ['chat_id' => $ChatId, 'msg' => $Chat_msg]);
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
        $Chat = Chat::with('frammer')
            ->with('chat_msgs')

            ->find($id);

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Chat);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();

        try {

            $Chat = Chat::find($id);
            $Chat->delete();

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }

    // public function updateChatStatus(Request $request, $id)
    // {
    //     $status = $request->status;
    //     $loginBy = $request->login_by;

    //     if (!isset($id)) {
    //         return $this->returnErrorData('ไม่พบข้อมูล id', 404);
    //     } else if (!isset($status)) {
    //         return $this->returnErrorData('กรุณาระบุ status ให้เรียบร้อย', 404);
    //     } else if (!isset($loginBy)) {
    //         return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         $Chat = Chat::find($id);
    //         $Chat->status = $status;

    //         if ($status == 'finish') {

    //             $qoutaId = $loginBy->id;
    //             $pointEventId = 3; //ผู้ซื้อกดปิดดิล
    //             $this->addMemberPoint($qoutaId, $pointEventId);

    //             // //update status co agent
    //             // $Co_agent = Co_agent::where('Chat_id', $id)->first();
    //             // if ($Co_agent) {
    //             //     $Co_agent->status = 'finish';
    //             //     $Co_agent->updated_at = Carbon::now()->toDateTimeString();
    //             //     $Co_agent->save();
    //             // }
    //             // //

    //         }

    //         $Chat->updated_at = Carbon::now()->toDateTimeString();

    //         $Chat->save();

    //         DB::commit();

    //         return $this->returnSuccess('ดำเนินการสำเร็จ', $Chat);
    //     } catch (\Throwable $e) {

    //         DB::rollback();

    //         return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
    //     }
    // }

    // public function closeDealChat(Request $request, $id)
    // {
    //     $closeDeal = $request->close_deal;
    //     $loginBy = $request->login_by;

    //     if (!isset($id)) {
    //         return $this->returnErrorData('ไม่พบข้อมูล id', 404);
    //     } else if (!isset($closeDeal)) {
    //         return $this->returnErrorData('กรุณาระบุ close_deal ให้เรียบร้อย', 404);
    //     } else if (!isset($loginBy)) {
    //         return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         $Chat = Chat::find($id);
    //         $Chat->close_deal = $closeDeal;

    //         //ผู้ขายกดปิดดิล
    //         if ($closeDeal == 1) {

    //             //send msg
    //             $Chat_msg = new Chat_msg();
    //             $Chat_msg->Chat_id = $id;
    //             $Chat_msg->qouta_id = $loginBy->id;

    //             $Chat_msg->message = 'ผู้ขายต้องการปิดดีลของคุณกรุณากดยืนยัน';
    //             $Chat_msg->type = 'close_deal';
    //             //
    //             $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

    //             $Chat_msg->save();
    //             //

    //         }

    //         //คนซื้อกดรับดิล
    //         if ($closeDeal == 2) {

    //             //send msg
    //             $Chat_msg = new Chat_msg();
    //             $Chat_msg->Chat_id = $id;
    //             $Chat_msg->qouta_id = $loginBy->id;

    //             $Chat_msg->message = 'ยินดีด้วย! ปิดดิลสำเร็จ';
    //             $Chat_msg->type = 'close_deal';
    //             //
    //             $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

    //             $Chat_msg->save();
    //             //

    //             /////////////////////// add point ////////////////////////////////
    //             //get member in asset chat
    //             $allmember = Chat::select('qouta_id')
    //                 ->where('id', $id)
    //                 ->groupby('qouta_id')
    //                 ->get();

    //             if ($allmember->isNotEmpty()) {
    //                 for ($i = 0; $i < count($allmember); $i++) {
    //                     //add point
    //                     $qoutaId = $allmember[$i]->qouta_id;
    //                     $pointEventId = 3; //ผู้ซื้อกดปิดดิล
    //                     $this->addMemberPoint($qoutaId, $pointEventId);
    //                 }
    //             }
    //             /////////////////////////////////////////////////////////////////////

    //             // //update status co agent
    //             // $Co_agent = Co_agent::where('Chat_id', $id)->first();
    //             // if ($Co_agent) {
    //             //     $Co_agent->status = 'close_deal';
    //             //     $Co_agent->updated_at = Carbon::now()->toDateTimeString();
    //             //     $Co_agent->save();
    //             // }

    //             // //
    //         }

    //         //คนซื้อ cancel ดิล
    //         if ($closeDeal == 0) {

    //             //send msg
    //             $Chat_msg = new Chat_msg();
    //             $Chat_msg->Chat_id = $id;
    //             $Chat_msg->qouta_id = $loginBy->id;

    //             $Chat_msg->message = 'ผู้ซื้อ ปฎิเสธการปิดดิล';
    //             $Chat_msg->type = 'close_deal';
    //             //
    //             $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

    //             $Chat_msg->save();
    //             //

    //         }

    //         $Chat->updated_at = Carbon::now()->toDateTimeString();

    //         $Chat->save();

    //         DB::commit();

    //         return $this->returnSuccess('ดำเนินการสำเร็จ', $Chat);
    //     } catch (\Throwable $e) {

    //         DB::rollback();

    //         return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
    //     }
    // }

    // public function meetingChat(Request $request, $id)
    // {
    //     $date = $request->date;
    //     $meeting = $request->meeting;
    //     $loginBy = $request->login_by;

    //     if (!isset($id)) {
    //         return $this->returnErrorData('ไม่พบข้อมูล id', 404);
    //     } else if (!isset($meeting)) {
    //         return $this->returnErrorData('กรุณาระบุ meeting ให้เรียบร้อย', 404);
    //     } else if ($meeting == 1 && !isset($date)) {
    //         return $this->returnErrorData('กรุณาระบุวันที่นัดหมายให้เรียบร้อย', 404);
    //     } else if (!isset($loginBy)) {
    //         return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         $Chat = Chat::with('frammer')
    //             ->with('owner')
    //             ->with(['asset' => function ($query) {
    //                 $query->with('frammer');
    //                 $query->with('property_type');
    //                 $query->with('inquiry_type');
    //                 $query->with('property_announcer');
    //                 $query->with('property_sub_type');
    //                 $query->with('asset_images');

    //                 $query->with(['asset_property_explends' => function ($query) {
    //                     $query->with('property_sub_type_explend');
    //                 }]);

    //                 $query->with(['asset_property_rents' => function ($query) {
    //                     $query->with('property_sub_type_rent');
    //                 }]);

    //                 $query->with('property_ownership');
    //                 $query->with('property_color_land');

    //                 $query->with(['asset_location_nearbys' => function ($query) {
    //                     $query->with('property_location_nearby');
    //                 }]);

    //                 $query->with(['asset_facilitys' => function ($query) {
    //                     $query->with(['property_sub_facility' => function ($query) {
    //                         $query->with('property_facility');
    //                     }]);
    //                 }]);
    //                 $query->with('asset_tags');
    //             }])
    //             ->find($id);

    //         $Chat->meeting = $meeting;

    //         //ผู้ซื้อกดนัดหมาย
    //         if ($meeting == 1) {

    //             //send msg
    //             $Chat_msg = new Chat_msg();
    //             $Chat_msg->Chat_id = $id;
    //             $Chat_msg->qouta_id = $loginBy->id;

    //             $Chat_msg->message = 'ผู้ซื้อต้องการนัดหมายคุณดูทรัพย์ ในวันที่ ' . date('d/m/Y', strtotime($date)) . ' เวลา ' . date('H:i', strtotime($date)) . ' น.';
    //             $Chat_msg->type = 'meeting';
    //             $Chat_msg->date = $date;
    //             //
    //             $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

    //             $Chat_msg->save();
    //             //

    //         }

    //         //คนขายกดรับนัดหมาย
    //         if ($meeting == 2) {

    //             //get last Asset_meeting_date
    //             $lastChatMsgMeeting = Chat_msg::where('Chat_id', $id)
    //                 ->where('type', 'meeting')
    //                 ->orderby('id', 'DESC')
    //                 ->first();

    //             $strDate = $lastChatMsgMeeting->date;

    //             //send msg
    //             $Chat_msg = new Chat_msg();
    //             $Chat_msg->Chat_id = $id;
    //             $Chat_msg->qouta_id = $loginBy->id;

    //             $Chat_msg->message = 'ผู้ขายยืนยันการนัดหมาย ในวันที่ ' . date('d/m/Y', strtotime($strDate)) . ' เวลา ' . date('H:i', strtotime($strDate)) . ' น.';
    //             $Chat_msg->type = 'meeting';
    //             $Chat_msg->date = $strDate;
    //             //
    //             $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

    //             $Chat_msg->save();
    //             //

    //             /////////////////////// add asset meeting date ////////////////////////////////

    //             $checkMeeting = Asset_meeting_date::where('Chat_id', $id)->first();

    //             if ($checkMeeting) {

    //                 //update
    //                 $checkMeeting->Chat_id = $id;
    //                 $checkMeeting->date = $strDate;
    //                 $checkMeeting->updated_at = Carbon::now()->toDateTimeString();

    //                 $checkMeeting->save();
    //             } else {
    //                 //add
    //                 $Asset_meeting_date = new Asset_meeting_date();
    //                 $Asset_meeting_date->Chat_id = $id;
    //                 $Asset_meeting_date->date = $strDate;
    //                 $Asset_meeting_date->updated_at = Carbon::now()->toDateTimeString();

    //                 $Asset_meeting_date->save();
    //             }

    //             /////////////////////////////////////////////////////////////////////

    //             // //update status co agent
    //             // $Co_agent = Co_agent::where('Chat_id', $id)->first();
    //             // if ($Co_agent) {
    //             //     $Co_agent->status = 'meeting';
    //             //     $Co_agent->updated_at = Carbon::now()->toDateTimeString();
    //             //     $Co_agent->save();
    //             // }
    //             // //

    //             //////////// send google calendar   ////////////////////////////////////

    //             // $name = 'คุณมีรายการนัดหมายดูทรัพย์ ' . $Chat->asset->name;

    //             // $description = 'คุณมีรายการนัดหมายดูทรัพย์ ' . $Chat->asset->name . "\n" .
    //             //     'วันที่ ' . date('d/m/Y', strtotime($date)) . "\n" .
    //             //     'เวลา ' . date('H:i', strtotime($date));

    //             // $startDateTime = $date;
    //             // $endDateTime = $date;


    //             // // $email = $Chat->asset->qouta_id;
    //             // $email = 'boss32099@gmail.com';

    //             // $this->sendGoogleCalendar($name, $description, $startDateTime, $endDateTime, $email);

    //             ////////////////////////////////////////////////////////////
    //         }

    //         //คนขาย cancel ดิล
    //         if ($meeting == 0) {

    //             //send msg
    //             $Chat_msg = new Chat_msg();
    //             $Chat_msg->Chat_id = $id;
    //             $Chat_msg->qouta_id = $loginBy->id;

    //             $Chat_msg->message = 'ผู้ขาย ปฎิเสธการนัดหมาย';
    //             $Chat_msg->type = 'meeting';
    //             //
    //             $Chat_msg->updated_at = Carbon::now()->toDateTimeString();

    //             $Chat_msg->save();
    //             //

    //         }

    //         $Chat->updated_at = Carbon::now()->toDateTimeString();

    //         $Chat->save();

    //         DB::commit();

    //         return $this->returnSuccess('ดำเนินการสำเร็จ', $Chat);
    //     } catch (\Throwable $e) {

    //         DB::rollback();

    //         return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e->getMessage(), 404);
    //     }
    // }
}
