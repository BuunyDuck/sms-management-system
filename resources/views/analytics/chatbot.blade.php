<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Analytics - Montana Sky SMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 28px;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-buttons {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .filter-btn.active {
            background: #007aff;
            color: white;
            border-color: #007aff;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card .label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 4px;
        }

        .stat-card .subtext {
            font-size: 13px;
            color: #94a3b8;
        }

        .chart-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .chart-card h3 {
            font-size: 18px;
            color: #1a202c;
            margin-bottom: 20px;
        }

        .bar-chart {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .bar-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .bar-label {
            min-width: 150px;
            font-size: 14px;
            color: #475569;
        }

        .bar-visual {
            flex: 1;
            height: 28px;
            background: #e0f2fe;
            border-radius: 6px;
            position: relative;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #0ea5e9, #06b6d4);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            min-width: 40px;
        }

        .session-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .session-item {
            padding: 16px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #007aff;
        }

        .session-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .session-phone {
            font-weight: 600;
            color: #1a202c;
        }

        .session-time {
            font-size: 13px;
            color: #64748b;
        }

        .session-details {
            font-size: 13px;
            color: #64748b;
            display: flex;
            gap: 16px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fed7aa;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #007aff;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 16px;
            }

            .bar-label {
                min-width: 100px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}" class="back-link">
        ← Back to Home
    </a>

    <div class="header">
        <h1>
            <span>🤖</span>
            Chatbot Analytics
        </h1>
        <div class="filter-buttons">
            <a href="?days=7" class="filter-btn {{ $days == 7 ? 'active' : '' }}">7 Days</a>
            <a href="?days=30" class="filter-btn {{ $days == 30 ? 'active' : '' }}">30 Days</a>
            <a href="?days=90" class="filter-btn {{ $days == 90 ? 'active' : '' }}">90 Days</a>
        </div>
    </div>

    <!-- Key Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Sessions</div>
            <div class="value">{{ number_format($totalSessions) }}</div>
            <div class="subtext">Last {{ $days }} days</div>
        </div>

        <div class="stat-card">
            <div class="label">Total Interactions</div>
            <div class="value">{{ number_format($totalInteractions) }}</div>
            <div class="subtext">{{ $totalSessions > 0 ? number_format($totalInteractions / $totalSessions, 1) : 0 }} per session</div>
        </div>

        <div class="stat-card">
            <div class="label">Avg Duration</div>
            <div class="value">{{ $avgDurationFormatted }}</div>
            <div class="subtext">Minutes:Seconds</div>
        </div>

        <div class="stat-card">
            <div class="label">Completion Rate</div>
            <div class="value">{{ $completionRate }}%</div>
            <div class="subtext">Users who finished vs exited</div>
        </div>
    </div>

    <!-- Menu Popularity -->
    @if($menuPopularity->count() > 0)
    <div class="chart-card">
        <h3>📊 Most Popular Menu Options</h3>
        <div class="bar-chart">
            @php
                $maxCount = $menuPopularity->max('count');
            @endphp
            @foreach($menuPopularity as $menu)
            <div class="bar-item">
                <div class="bar-label">{{ str_replace('.txt', '', $menu->response_template) }}</div>
                <div class="bar-visual">
                    <div class="bar-fill" style="width: {{ ($menu->count / $maxCount) * 100 }}%">
                        {{ $menu->count }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Exit Types -->
    @if($exitTypes->count() > 0)
    <div class="chart-card">
        <h3>🚪 How Sessions Ended</h3>
        <div class="bar-chart">
            @php
                $totalExits = $exitTypes->sum('count');
            @endphp
            @foreach($exitTypes as $exit)
            <div class="bar-item">
                <div class="bar-label">
                    {{ ucfirst($exit->exit_type) }}
                    @if($exit->exit_type == 'explicit')
                        (User typed EXIT)
                    @else
                        (30-min timeout)
                    @endif
                </div>
                <div class="bar-visual">
                    <div class="bar-fill" style="width: {{ ($exit->count / $totalExits) * 100 }}%">
                        {{ $exit->count }} ({{ round(($exit->count / $totalExits) * 100) }}%)
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Top Navigation Paths -->
    @if($topPaths->count() > 0)
    <div class="chart-card">
        <h3>🗺️ Top Navigation Paths</h3>
        <div class="bar-chart">
            @php
                $maxPathCount = $topPaths->max('count');
            @endphp
            @foreach($topPaths as $path)
            <div class="bar-item">
                <div class="bar-label">{{ $path->menu_path }}</div>
                <div class="bar-visual">
                    <div class="bar-fill" style="width: {{ ($path->count / $maxPathCount) * 100 }}%">
                        {{ $path->count }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Sessions -->
    @if($recentSessions->count() > 0)
    <div class="chart-card">
        <h3>🕐 Recent Sessions</h3>
        <div class="session-list">
            @foreach($recentSessions as $session)
            <div class="session-item">
                <div class="session-header">
                    <div class="session-phone">
                        📱 {{ substr($session->phone, 0, 3) }}-{{ substr($session->phone, 3, 3) }}-{{ substr($session->phone, 6) }}
                    </div>
                    <div class="session-time">
                        {{ \Carbon\Carbon::parse($session->session_start)->diffForHumans() }}
                    </div>
                </div>
                <div class="session-details">
                    <span>{{ $session->interactions }} interactions</span>
                    @if($session->duration)
                        <span>{{ gmdate("i:s", $session->duration) }} duration</span>
                    @endif
                    @if($session->exit_type)
                        @if($session->exit_type == 'explicit')
                            <span class="badge badge-success">Completed</span>
                        @elseif($session->exit_type == 'timeout')
                            <span class="badge badge-warning">Timeout</span>
                        @endif
                    @else
                        <span class="badge badge-info">Active</span>
                    @endif
                </div>
                @if($session->menus_visited->count() > 0)
                <div class="session-details" style="margin-top: 8px">
                    <span><strong>Visited:</strong> {{ $session->menus_visited->map(fn($m) => str_replace('.txt', '', $m))->implode(', ') }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($totalSessions == 0)
    <div class="chart-card" style="text-align: center; padding: 60px 20px;">
        <h3 style="margin-bottom: 12px;">📊 No Data Yet</h3>
        <p style="color: #64748b;">Chatbot analytics will appear here once customers start using the MENU feature.</p>
    </div>
    @endif

</body>
</html>

