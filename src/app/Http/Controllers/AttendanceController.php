<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Work;
use App\Models\Breaking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function attendance(Request $request)
    {
        // 日付管理
        $thisDate = $request->date ? new Carbon($request->date) : Carbon::today();
        $prevDate = $thisDate->copy()->subDay();
        $nextDate = $thisDate->copy()->addDay();

        $thisDateAttendances = Work::whereDate('work_in', $thisDate)->paginate(5);

        // calculate()メソッドから$calculationsHours=[$totalWorkHours, $totalBreakingHours]を取得
        $calculationsHours = $this->calculate($thisDateAttendances);
        // 実働時間の計算
        $trueWorkHours = [];
        foreach ($thisDateAttendances as $thisDateAttendance) {
            $calculationHours = $calculationsHours[$thisDateAttendance->id];
            $trueWorkHours[$thisDateAttendance->id] = $calculationHours['totalWorkHours'] - $calculationHours['totalBreakingHours'];
        }

        return view('attendance', compact(
            'prevDate',
            'thisDate',
            'nextDate',
            'thisDateAttendances',
            'calculationsHours',
            'trueWorkHours',
        ));
    }

    // 勤務時間・休憩時間の計算式
    private function calculate($attendances)
    {
        $calculationsHours = [];

        foreach ($attendances as $attendance)
        {
            $totalWorkHours = 0; //勤務合計時間初期化
            $totalBreakingHours = 0; // 休憩豪快時間初期化

            // 勤務時間の計算($totalWorkHours = work_out - work_in)
            $workIn = Carbon::parse($attendance->work_in);
            $workOut = Carbon::parse($attendance->work_out);
            $totalWorkHours += $workIn->diffInSeconds($workOut);


            // thisDateAttendancesの勤務情報に紐づくbreakingの情報取得
            $thisDateBreakings = Breaking::where('work_id', $attendance->id)->get();
            $totalBreakingHoursPerAttendance = 0; // 勤務ごとの休憩合計時間の初期化
                // 休憩時間の計算($totalBreakingHours = breaking_out - breaking_in)
            foreach ($thisDateBreakings as $thisDateBreaking) {
                $breakingIn = Carbon::parse($thisDateBreaking->breaking_in);
                $breakingOut = Carbon::parse($thisDateBreaking->breaking_out);
                $totalBreakingHoursPerAttendance += $breakingIn->diffInSeconds($breakingOut); // 休憩時間の加算
            }
            $totalBreakingHours += $totalBreakingHoursPerAttendance;

            $calculationsHours[$attendance->id] = [
                'totalWorkHours' => $totalWorkHours,
                'totalBreakingHours' => $totalBreakingHours,
            ];
        }
        // dd($calculationsHours);
        return $calculationsHours;

    }
}
