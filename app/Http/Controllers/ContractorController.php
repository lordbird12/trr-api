<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Factory;
use App\Models\FactoryContractor;
use App\Models\Feature;
use App\Models\FeatureContractor;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContractorController extends Controller
{
    public function getList()
    {
        $Item = Contractor::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getPage2(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $facetorie = $request->facetorie;
        $status = $request->status;

        $col = array('id', 'code', 'name', 'phone', 'detail', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'name', 'phone', 'detail', 'status', 'create_by');

        $D = Contractor::select($col)->with('facetories_contractors')->with('feature_contractors');

        if (isset($status)) {
            $D->where('status', $status);
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

            $Item = [];

            for ($i = 0; $i < count($d); $i++) {

                $No = $No + 1;
                $d[$i]->No = $No;
                $d[$i]->features = FeatureContractor::where('contractor_id', $d[$i]->id)->get();
                $d[$i]->factories = FactoryContractor::where('contractor_id', $d[$i]->id)->get();
                if ($facetorie) {
                    foreach ($d[$i]->factories as $key => $value) {
                        if ($value['factorie_id'] == $facetorie) {
                            array_push($Item, $d[$i]);
                        }
                    }
                } else {
                    array_push($Item, $d[$i]);
                }
            }
        }
        $Item = $D->paginate($length, ['*'], 'page', $page);

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
        $facetorie = $request->facetorie;
        $status = $request->status;
        $feature_id = $request->feature_id;

        $col = array('id', 'code', 'name', 'phone', 'detail', 'image', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'name', 'phone', 'detail', 'image', 'status', 'create_by');

        $D = Contractor::select($col);

        if (isset($status)) {
            $D->where('status', $status);
        }

        if (isset($feature_id)) {
            $features = FeatureContractor::where('feature_id', $feature_id)->get();

            $arr = [];
            foreach ($features as $key => $value) {
                array_push($arr, $value['id']);
            }
            $D->whereIn('id', $arr);
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
                $d[$i]->feature_contractors = FeatureContractor::where('contractor_id', $d[$i]->id)->get();
                foreach ($d[$i]->feature_contractors as $key => $value) {
                    $d[$i]->feature_contractors[$key]->feature = Feature::find($value['feature_id']);
                    if ($d[$i]->feature_contractors[$key]->feature->image) {
                        $d[$i]->feature_contractors[$key]->feature->image = url($d[$i]->feature_contractors[$key]->feature->image);
                    }
                }
                $d[$i]->facetories_contractors = FactoryContractor::where('contractor_id', $d[$i]->id)->get();
                foreach ($d[$i]->facetories_contractors as $key => $value) {
                    $d[$i]->facetories_contractors[$key]->facetorie = Factory::find($value['factorie_id']);
                    if ($d[$i]->facetories_contractors[$key]->facetorie->image) {
                        $d[$i]->facetories_contractors[$key]->facetorie->image = url($d[$i]->facetories_contractors[$key]->facetorie->image);
                    }
                }
                if ($d[$i]->image) {
                    $d[$i]->image = url($d[$i]->image);
                }
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
        $factories = $request->factories;
        $features = $request->features;

        if (!isset($request->name)) {
            return $this->returnErrorData('กรุณาระบุชื่อให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new Contractor();
            $prefix = "#CON-";
            $id = IdGenerator::generate(['table' => 'contractors', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);
            $Item->code = $id;
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->detail = $request->detail;
            $Item->status = $request->status;
            $Item->image = $request->image;

            $Item->save();

            foreach ($factories as $key => $value) {
                $ItemFac = new FactoryContractor();
                $ItemFac->contractor_id = $Item->id;
                $ItemFac->factorie_id = $value['factorie_id'];
                $ItemFac->save();
            }

            foreach ($features as $key => $value) {
                $ItemFea = new FeatureContractor();
                $ItemFea->contractor_id = $Item->id;
                $ItemFea->feature_id = $value['feature_id'];
                $ItemFea->save();
            }
            //

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
     * @param  \App\Models\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Contractor::find($id);
        if ($Item) {
            $Item->factories = FactoryContractor::where('contractor_id', $Item->id)->get();
            foreach ($Item->factories as $key => $value) {
                $Item->factories[$key]->factorie = Factory::find($value['factorie_id']);
            }
            $Item->features = FeatureContractor::where('contractor_id', $Item->id)->get();
            foreach ($Item->features as $key => $value) {
                $Item->features[$key]->feature = Feature::find($value['feature_id']);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function edit(Contractor $contractor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $factories = $request->factories;
        $features = $request->features;

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();


        try {
            $Item = Contractor::find($id);
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->detail = $request->detail;
            $Item->status = $request->status;
            $Item->image = $request->image;

            $Item->save();

            foreach ($factories as $key => $value) {
                $ItemFac = new FactoryContractor();
                $ItemFac->contractor_id = $Item->id;
                $ItemFac->factorie_id = $value['factorie_id'];
                $ItemFac->save();
            }

            foreach ($features as $key => $value) {
                $ItemFea = new FeatureContractor();
                $ItemFea->contractor_id = $Item->id;
                $ItemFea->feature_id = $value['feature_id'];
                $ItemFea->save();
            }
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->user_id;
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Contractor::find($id);
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
}
