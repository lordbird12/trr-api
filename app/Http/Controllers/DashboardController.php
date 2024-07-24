<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\DeductPaid;
use App\Models\IncomePaid;

class DashboardController extends Controller
{

    public function groutpby_activitytype(Request $request)
    {
        $frammerId = $request->frammer_id;
        $selectedDate = $request->selectdate;

        $query = DeductPaid::with('factory_activity')
            ->get()
            ->groupBy('factory_activity.activitytype');

        if (isset($frammerId)) {
            $query->where('frammer_id', $frammerId);
        }

        $result = $query->map(function ($group) {
            return [
                'activitytype' => $group->first()->factory_activity->activitytype,
                'total_paid' => $group->sum('paid'),
            ];
        })->values();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $result);
    }

    public function groutpby_weekly(Request $request)
    {
        $frammerId = $request->frammer_id;
        date_default_timezone_set('Asia/Bangkok');

        $selectedDate = $request->has('selectdate')
            ? Carbon::parse($request->selectdate)
            : now();

        $startOfWeek = (clone $selectedDate)->startOfWeek();
        $endOfWeek = (clone $selectedDate)->endOfWeek();

        $query = DeductPaid::whereBetween('updated_at', [$startOfWeek, $endOfWeek]);

        if (isset($frammerId)) {
            $query->where('frammer_id', $frammerId);
        }

        $result = $query->selectRaw('DATE(updated_at) as date, SUM(paid) as total_paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(function ($item) {
                return $item->total_paid;
            });

        $allDates = [];
        for ($date = clone $startOfWeek; $date <= $endOfWeek; $date->addDay()) {
            $dateString = $date->toDateString();
            $allDates[$dateString] = $result[$dateString] ?? 0;
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $allDates);
    }

    public function incomededuct(Request $request)
    {
        $frammerId = $request->frammer_id;
        $selectedDate = $request->selectdate;
        $allData = [];

        $query1 = DeductPaid::selectRaw('SUM(paid) as total_paid')
            ->get();

        if (isset($frammerId)) {
            $query1->where('frammer_id', $frammerId);
        }

        $allData["Deduct"] = $query1->map(function ($item) {
            return $item->total_paid;
        });

        $query2 = IncomePaid::selectRaw('SUM(paid) as total_paid')
            ->get();

        if (isset($frammerId)) {
            $query2->where('frammer_id', $frammerId);
        }

        $allData["Income"] = $query2->map(function ($item) {
            return $item->total_paid;
        });

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $allData);
    }
}
