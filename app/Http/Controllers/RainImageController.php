<?php

namespace App\Http\Controllers;

use App\Models\RainImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RainImageController extends Controller
{

    public function getList(Request $request)
    {
        $Item = RainImage::query();

        // return $Item->get();
    
        if(isset($request->frammer_id)){
            $Item->where('frammer_id', $request->frammer_id);
        }
        
        if (isset($request->plotsugar_id)) {
            $Item->whereIn('plotsugar_id', array_map('strval', $request->plotsugar_id));
        }elseif (isset($request->plotsugar_id)) {
            $Item->where('plotsugar_id', $request->plotsugar_id);
        }

        $Item = $Item->get();
        $Item = $Item->map(function ($item) {
            $item->image = url($item->image);
            return $item;
        });

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function uploadrainimage(Request $request)
    {

        DB::beginTransaction();

        try {

            if(isset($request->image_id)){
                $Item = RainImage::find($request->image_id);
                if ($request->image && $request->image != null && $request->image != 'null') {
                    $Item->image = $this->uploadImage($request->image, '/images/rain/');
                }
            }
            else{
                $Item = new RainImage();
                $Item->frammer_id = $request->frammer_id;
                $Item->plotsugar_id = $request->plotsugar_id;
                $Item->year = $request->year;
    
                if ($request->image && $request->image != null && $request->image != 'null') {
                    $Item->image = $this->uploadImage($request->image, '/images/rain/');
                }
            }

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}
