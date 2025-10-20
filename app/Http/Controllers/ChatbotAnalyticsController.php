<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatbotAnalyticsController extends Controller
{
    /**
     * Show the chatbot analytics dashboard
     */
    public function index(Request $request)
    {
        // Get date range filter (default: last 30 days)
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Total sessions
        $totalSessions = DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', $startDate)
            ->distinct('session_id')
            ->count('session_id');

        // Total interactions
        $totalInteractions = DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', $startDate)
            ->count();

        // Average session duration (in seconds)
        $avgDuration = DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', $startDate)
            ->whereNotNull('session_end')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, session_start, session_end)) as avg_duration')
            ->value('avg_duration');

        // Exit types breakdown
        $exitTypes = DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', $startDate)
            ->whereIn('exit_type', ['explicit', 'timeout'])
            ->select('exit_type', DB::raw('COUNT(DISTINCT session_id) as count'))
            ->groupBy('exit_type')
            ->get();

        // Menu popularity (excluding main menu)
        $menuPopularity = DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', $startDate)
            ->whereNotNull('response_template')
            ->where('response_template', '!=', 'main_menu')
            ->select('response_template', DB::raw('COUNT(*) as count'))
            ->groupBy('response_template')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Recent sessions (last 20)
        $recentSessions = DB::table('chatbot_sessions_log')
            ->select(
                'session_id',
                'phone',
                'menu_path',
                'user_input',
                'response_template',
                'session_start',
                'session_end',
                'exit_type',
                DB::raw('TIMESTAMPDIFF(SECOND, session_start, session_end) as duration')
            )
            ->where('session_start', '>=', $startDate)
            ->orderBy('session_start', 'desc')
            ->limit(20)
            ->get()
            ->groupBy('session_id')
            ->map(function ($group) {
                $first = $group->first();
                return (object)[
                    'session_id' => $first->session_id,
                    'phone' => $first->phone,
                    'session_start' => $first->session_start,
                    'session_end' => $first->session_end,
                    'duration' => $first->duration,
                    'exit_type' => $first->exit_type,
                    'interactions' => $group->count(),
                    'menus_visited' => $group->pluck('response_template')->filter()->unique()->values()
                ];
            })
            ->values()
            ->take(10);

        // Sessions by day (last 7 days for chart)
        $sessionsByDay = DB::table('chatbot_sessions_log')
            ->select(
                DB::raw('DATE(session_start) as date'),
                DB::raw('COUNT(DISTINCT session_id) as count')
            )
            ->where('session_start', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Sessions by hour (today)
        $sessionsByHour = DB::table('chatbot_sessions_log')
            ->select(
                DB::raw('HOUR(session_start) as hour'),
                DB::raw('COUNT(DISTINCT session_id) as count')
            )
            ->whereDate('session_start', Carbon::today())
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Top navigation paths
        $topPaths = DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', $startDate)
            ->whereNotNull('menu_path')
            ->where('menu_path', '!=', '')
            ->where('menu_path', '!=', 'm')
            ->select('menu_path', DB::raw('COUNT(*) as count'))
            ->groupBy('menu_path')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Completion rate
        $completedSessions = DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', $startDate)
            ->where('completed_successfully', true)
            ->distinct('session_id')
            ->count('session_id');
        
        $completionRate = $totalSessions > 0 
            ? round(($completedSessions / $totalSessions) * 100, 1) 
            : 0;

        // Format average duration
        $avgDurationFormatted = $avgDuration ? gmdate("i:s", (int)$avgDuration) : '0:00';

        return view('analytics.chatbot', compact(
            'totalSessions',
            'totalInteractions',
            'avgDurationFormatted',
            'exitTypes',
            'menuPopularity',
            'recentSessions',
            'sessionsByDay',
            'sessionsByHour',
            'topPaths',
            'completionRate',
            'days'
        ));
    }

    /**
     * Get template display name from filename
     */
    protected function getTemplateName(string $template): string
    {
        $names = [
            'SKYCONNECT.txt' => 'SkyConnect',
            'DSL.txt' => 'DSL',
            'cable.txt' => 'Cable',
            'email.txt' => 'Email',
            'outage.txt' => 'Outages',
            'speedtest.txt' => 'Speedtest',
            'payment.txt' => 'Payments',
            'mstv.txt' => 'MontanaSkyTV',
            'voipphone.txt' => 'Voip Phone',
            'plume.txt' => 'Plume Wifi',
            'fiber.txt' => 'Fiber GPON',
            'p2p.txt' => 'Point to Points',
        ];

        return $names[$template] ?? str_replace('.txt', '', $template);
    }
}

