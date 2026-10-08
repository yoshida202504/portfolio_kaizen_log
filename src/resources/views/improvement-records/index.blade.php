@extends('layouts.app')
@section('title', '改善記録 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div>
            <p class="eyebrow">IMPROVEMENT REVIEW</p>
            <h1>改善記録</h1>
            <p class="page-lead">実行した改善と、その結果を期間ごとに振り返ります。</p>
        </div>
    </header>

    <section class="card" aria-labelledby="improvement-filter-title">
        <h2 id="improvement-filter-title" class="panel-title">表示条件</h2>
        <form class="improvement-filter-form" method="GET" action="{{ route('improvement-records.index') }}">
            <div class="field">
                <label for="year">表示年</label>
                <select id="year" name="year">
                    @for ($optionYear = now(config('app.timezone'))->year; $optionYear >= now(config('app.timezone'))->year - 4; $optionYear--)
                        <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}年</option>
                    @endfor
                </select>
            </div>
            <div class="field">
                <label for="start_month">開始月</label>
                <select id="start_month" name="start_month">
                    @for ($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}" @selected($startMonth === $month)>{{ $month }}月</option>
                    @endfor
                </select>
            </div>
            <div class="field">
                <label for="end_month">終了月</label>
                <select id="end_month" name="end_month">
                    @for ($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}" @selected($endMonth === $month)>{{ $month }}月</option>
                    @endfor
                </select>
            </div>
            <div class="field">
                <label for="evaluation">評価</label>
                <select id="evaluation" name="evaluation">
                    <option value="all" @selected($evaluation === 'all')>すべて（A〜D）</option>
                    <option value="executed" @selected($evaluation === 'executed')>実施済み（A〜C）</option>
                    <option value="above_or_expected" @selected($evaluation === 'above_or_expected')>想定どおり以上（A・B）</option>
                    <option value="above" @selected($evaluation === 'above')>想定以上（A）</option>
                    <option value="below" @selected($evaluation === 'below')>想定より低い（C）</option>
                    <option value="not_executed" @selected($evaluation === 'not_executed')>未実施（D）</option>
                </select>
            </div>
            <button class="button" type="submit">絞り込む</button>
        </form>
        <p class="form-help">実施済み（A〜C）は実施日、未実施（D）は日報日を基準に期間へ含めています。</p>
    </section>

    <section class="improvement-overview" aria-label="改善記録の集計">
        <article class="summary-card"><span>A：想定より良い結果</span><strong>{{ $breakdown['A'] }}件</strong></article>
        <article class="summary-card"><span>B：想定どおりの結果</span><strong>{{ $breakdown['B'] }}件</strong></article>
        <article class="summary-card"><span>C：想定より低い結果</span><strong>{{ $breakdown['C'] }}件</strong></article>
        <article class="summary-card"><span>D：未実施</span><strong>{{ $breakdown['D'] }}件</strong></article>
    </section>

    <section class="card" aria-labelledby="weekly-activity-title">
        <h2 id="weekly-activity-title" class="panel-title">週ごとの改善行動数</h2>
        <p class="form-help">A〜Cとして記録された改善策だけを、実施日ごとに数えています。</p>
        @php($maximumWeeklyCount = max(1, $weeklyActivity->max('count')))
        @php($chartWidth = 720)
        @php($chartHeight = 270)
        @php($chartPadding = 40)
        @php($barAreaWidth = $chartWidth - ($chartPadding * 2))
        @php($barSlotWidth = $weeklyActivity->isEmpty() ? $barAreaWidth : $barAreaWidth / $weeklyActivity->count())
        <svg class="activity-bar-chart" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="週ごとの改善行動数を示す棒グラフ">
            <title>週ごとの改善行動数</title>
            <line x1="{{ $chartPadding }}" y1="{{ $chartPadding }}" x2="{{ $chartPadding }}" y2="{{ $chartHeight - $chartPadding }}" stroke="#6b7280" />
            <line x1="{{ $chartPadding }}" y1="{{ $chartHeight - $chartPadding }}" x2="{{ $chartWidth - $chartPadding }}" y2="{{ $chartHeight - $chartPadding }}" stroke="#6b7280" />
            @foreach ($weeklyActivity as $index => $week)
                @php($barWidth = max(3, min(38, $barSlotWidth * 0.64)))
                @php($barHeight = ($week['count'] / $maximumWeeklyCount) * 150)
                @php($barX = $chartPadding + ($index * $barSlotWidth) + (($barSlotWidth - $barWidth) / 2))
                @php($barY = $chartHeight - $chartPadding - $barHeight)
                <rect x="{{ $barX }}" y="{{ $barY }}" width="{{ $barWidth }}" height="{{ $barHeight }}" rx="3" fill="#2b927c"><title>{{ $week['label'] }}の週：{{ $week['count'] }}件</title></rect>
                @if ($week['count'] > 0)
                    <text x="{{ $barX + ($barWidth / 2) }}" y="{{ $barY - 7 }}" text-anchor="middle" font-size="12" fill="#174d83">{{ $week['count'] }}</text>
                @endif
                @if ($week['show_label'])
                    <text x="{{ $barX + ($barWidth / 2) }}" y="{{ $chartHeight - 14 }}" text-anchor="middle" font-size="11" fill="#617786">{{ $week['label'] }}</text>
                @endif
            @endforeach
        </svg>
    </section>

    <section class="card" aria-labelledby="legacy-improvement-trend-title">
        <h2 id="legacy-improvement-trend-title" class="panel-title">参考：従来の改善率の推移</h2>
        <p class="form-help">過去に改善率として保存した記録を確認できます。新しい改善結果は、上のA〜D評価と週ごとの改善行動数で振り返ります。</p>
        @if ($legacyChartPoints->isNotEmpty())
            <svg class="improvement-chart" viewBox="0 0 {{ $legacyChartWidth }} {{ $legacyChartHeight }}" role="img" aria-label="改善率の推移グラフ">
                <line x1="{{ $legacyChartPadding }}" y1="{{ $legacyChartPadding }}" x2="{{ $legacyChartPadding }}" y2="{{ $legacyChartHeight - $legacyChartPadding }}" stroke="#6b7280" />
                <line x1="{{ $legacyChartPadding }}" y1="{{ $legacyChartHeight - $legacyChartPadding }}" x2="{{ $legacyChartWidth - $legacyChartPadding }}" y2="{{ $legacyChartHeight - $legacyChartPadding }}" stroke="#6b7280" />
                <text x="4" y="{{ $legacyChartPadding + 4 }}" font-size="12">100%</text><text x="16" y="{{ $legacyChartHeight - $legacyChartPadding + 4 }}" font-size="12">0%</text>
                <polyline fill="none" stroke="#1967c9" stroke-width="3" points="{{ $legacyChartPoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ') }}" />
                @foreach ($legacyChartPoints as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#1967c9"><title>{{ $point['record_date'] }}：{{ $point['rate'] }}%</title></circle>
                    @if ($point['show_date_label'])
                        <text x="{{ $point['x'] }}" y="{{ $legacyChartLabelY }}" text-anchor="end" font-size="11" transform="rotate(-35 {{ $point['x'] }} {{ $legacyChartLabelY }})">{{ $point['record_date'] }}</text>
                    @endif
                    <text x="{{ $point['x'] }}" y="{{ $point['y'] - 8 }}" text-anchor="middle" font-size="11">{{ $point['rate'] }}%</text>
                @endforeach
            </svg>
        @else
            <p class="empty-state">改善率が記録された日報はまだありません。</p>
        @endif
    </section>

    <section class="card" aria-labelledby="improvement-record-list-title">
        <h2 id="improvement-record-list-title" class="panel-title">改善記録一覧</h2>
        <div class="improvement-record-list">
            @forelse ($records as $improvementRecord)
                <article class="improvement-record-item">
                    <div class="improvement-record-item-header">
                        <span class="evaluation evaluation-{{ strtolower($improvementRecord->evaluation) }}">評価 {{ $improvementRecord->evaluation }}</span>
                        <span class="record-date">
                            @if ($improvementRecord->executed_at)
                                実施日：{{ $improvementRecord->executed_at->format('Y/m/d') }}
                            @else
                                日報日：{{ $improvementRecord->dailyRecord->record_date }}
                            @endif
                        </span>
                    </div>
                    <h3>{{ $improvementRecord->dailyRecord->actions }}</h3>
                    <p><span>改善策：</span>{{ $improvementRecord->dailyRecord->improvement_strategy }}</p>
                    <p><span>想定する結果：</span>{{ $improvementRecord->dailyRecord->expected_result }}</p>
                    @if ($improvementRecord->evaluation === 'D')
                        <p><span>未実施理由：</span>{{ ['forgot' => '忘れた', 'no_time' => '時間がなかった', 'unnecessary' => '不要になった', 'other' => 'その他'][$improvementRecord->not_executed_reason] ?? '未入力' }}</p>
                        @if ($improvementRecord->not_executed_note)
                            <p><span>補足：</span>{{ $improvementRecord->not_executed_note }}</p>
                        @endif
                    @else
                        <p><span>実際の結果：</span>{{ $improvementRecord->actual_result }}</p>
                    @endif
                    <a href="{{ route('records.show', $improvementRecord->dailyRecord) }}">日報を見る</a>
                </article>
            @empty
                <p class="empty-state">指定した条件の改善記録はありません。</p>
            @endforelse
        </div>

        @if ($records->hasPages())
            <nav class="pagination" aria-label="改善記録一覧のページ移動">
                @if ($records->onFirstPage())
                    <span class="pagination-link is-disabled" aria-disabled="true">前へ</span>
                @else
                    <a class="pagination-link" href="{{ $records->previousPageUrl() }}">前へ</a>
                @endif
                <span class="pagination-status">{{ $records->currentPage() }} / {{ $records->lastPage() }} ページ</span>
                @if ($records->hasMorePages())
                    <a class="pagination-link" href="{{ $records->nextPageUrl() }}">次へ</a>
                @else
                    <span class="pagination-link is-disabled" aria-disabled="true">次へ</span>
                @endif
            </nav>
        @endif
    </section>
@endsection
