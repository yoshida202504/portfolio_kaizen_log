<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\ImprovementRecord;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds Japanese demo data for portfolio reviewers and the guest login.
 * Running it again does not duplicate data: users are matched by email and
 * records are only created for demo users that have none yet.
 */
class DemoSeeder extends Seeder
{
    private const DAYS = 90;

    private const USERS = [
        ['email' => User::GUEST_EMAIL, 'name' => 'ゲストユーザー', 'age' => 28, 'gender' => '回答しない', 'interval' => 2],
        ['email' => 'misaki.sato@kaizen-log.test', 'name' => '佐藤 美咲', 'age' => 26, 'gender' => '女性', 'interval' => 3],
        ['email' => 'kenta.tanaka@kaizen-log.test', 'name' => '田中 健太', 'age' => 31, 'gender' => '男性', 'interval' => 4],
        ['email' => 'yoko.suzuki@kaizen-log.test', 'name' => '鈴木 陽子', 'age' => 35, 'gender' => '女性', 'interval' => 5],
    ];

    /**
     * @var list<array{actions: string, good: string, issue: string, strategy: string, expected: string, results: array<string, string>}>
     */
    private const THEMES = [
        [
            'actions' => '午前中にLaravelのFeatureテストを3本追加した',
            'good' => 'テストを先に書いたことで、仕様の抜け漏れに早く気づけた',
            'issue' => 'テストデータの準備に時間がかかり、午後の予定が押した',
            'strategy' => 'よく使うテストデータをFactoryのstateにまとめる',
            'expected' => 'テスト1本あたりの準備時間が半分になる',
            'results' => ['A' => 'stateを3つ作ったら準備がほぼ不要になり、想定以上に速く書けた', 'B' => 'テストの準備時間がおよそ半分になった', 'C' => 'stateの命名に迷い、思ったほど時間は短くならなかった'],
        ],
        [
            'actions' => 'チームの朝会で昨日の作業を共有した',
            'good' => '詰まっている点を早めに相談でき、解決のヒントをもらえた',
            'issue' => '話が長くなり、要点が伝わりにくかった',
            'strategy' => '朝会の前に「昨日・今日・困りごと」を3行でメモする',
            'expected' => '1分以内に要点を話せるようになる',
            'results' => ['A' => 'メモのおかげで40秒で話せ、質問も具体的になった', 'B' => '1分以内に話せるようになった', 'C' => 'メモは書けたが、話すときに脱線してしまった'],
        ],
        [
            'actions' => '英語の技術記事を1本読んだ',
            'good' => '知らなかったEloquentの書き方を2つ覚えた',
            'issue' => '分からない単語を調べるたびに集中が途切れた',
            'strategy' => '初読は単語を調べず、最後にまとめて調べる',
            'expected' => '記事1本を30分以内で読み終える',
            'results' => ['A' => '25分で読み終え、内容の理解度も上がった', 'B' => '30分で読み終えられた', 'C' => '最後に調べる単語が多すぎて、結局時間がかかった'],
        ],
        [
            'actions' => 'ポートフォリオの画面デザインを見直した',
            'good' => '余白を揃えただけで画面がかなり見やすくなった',
            'issue' => '細部にこだわりすぎて、機能の実装が進まなかった',
            'strategy' => 'デザイン調整は1日30分までとタイマーで区切る',
            'expected' => '実装とデザインの時間配分が安定する',
            'results' => ['A' => 'タイマーで区切ったら、実装も予定より早く進んだ', 'B' => '時間配分が安定し、実装の遅れがなくなった', 'C' => 'タイマーが鳴っても切り上げられない日があった'],
        ],
        [
            'actions' => '夜に30分ジョギングした',
            'good' => '気分が切り替わり、その後の学習に集中できた',
            'issue' => '寝る直前に走ったので、寝つきが悪かった',
            'strategy' => 'ジョギングを夕食前の時間に移す',
            'expected' => '23時までに眠れるようになる',
            'results' => ['A' => '寝つきが良くなり、朝の目覚めも良くなった', 'B' => '23時までに眠れる日が増えた', 'C' => '夕食前は予定が入りやすく、走れない日が多かった'],
        ],
        [
            'actions' => 'コードレビューで指摘された箇所を修正した',
            'good' => 'N+1問題の見つけ方を具体的に理解できた',
            'issue' => '同じ種類の指摘を前回も受けていた',
            'strategy' => 'PRを出す前に自分用のチェックリストで確認する',
            'expected' => '同じ指摘を受ける回数が減る',
            'results' => ['A' => 'チェックリストのおかげで、今回は指摘ゼロだった', 'B' => '同じ種類の指摘はなくなった', 'C' => 'チェックリストを見るのを忘れ、また同じ指摘を受けた'],
        ],
        [
            'actions' => '1日のタスクを朝に書き出してから作業した',
            'good' => '優先度の高い作業から手をつけられた',
            'issue' => '急な依頼が入ると、計画が崩れて焦ってしまった',
            'strategy' => '計画に30分のバッファを入れておく',
            'expected' => '急な依頼があっても予定のタスクを終えられる',
            'results' => ['A' => 'バッファがあったので、急な依頼にも落ち着いて対応できた', 'B' => '予定していたタスクをすべて終えられた', 'C' => 'バッファを別の作業で使い切ってしまった'],
        ],
        [
            'actions' => 'SQLのインデックスについて学習した',
            'good' => 'EXPLAINの読み方が分かり、遅いクエリの原因を説明できた',
            'issue' => '学んだことをすぐ忘れてしまいそうだと感じた',
            'strategy' => '学んだ内容を翌朝5分で人に説明するつもりでまとめ直す',
            'expected' => '1週間後も要点を説明できる',
            'results' => ['A' => '1週間後に同僚へ説明でき、質問にも答えられた', 'B' => '1週間後も要点を説明できた', 'C' => 'まとめ直しを2日でやめてしまい、細部を忘れていた'],
        ],
        [
            'actions' => '休日に部屋の片付けをした',
            'good' => '机の上が片付き、作業を始めるまでの時間が短くなった',
            'issue' => '平日の間にまた散らかってしまう',
            'strategy' => '寝る前に机の上だけ1分で片付ける',
            'expected' => '平日も机の上がきれいな状態を保てる',
            'results' => ['A' => '1分の片付けが習慣になり、部屋全体もきれいになった', 'B' => '平日も机の上を片付けた状態で保てた', 'C' => '疲れている日は片付けを飛ばしてしまった'],
        ],
        [
            'actions' => 'Gitのブランチ運用を見直した',
            'good' => 'feature → develop → master の流れが明確になった',
            'issue' => 'コミットの粒度が大きく、レビューしにくかった',
            'strategy' => '1つのコミットには1つの目的だけを入れる',
            'expected' => 'PRの差分を読む時間が短くなる',
            'results' => ['A' => 'コミット単位で読めるようになり、レビュー依頼もしやすくなった', 'B' => '差分が読みやすくなった', 'C' => '途中で目的が混ざり、結局大きなコミットになった'],
        ],
    ];

    private const NOT_EXECUTED_REASONS = ['forgot', 'no_time', 'unnecessary', 'other'];

    private const COMMENTS = [
        '同じことで悩んでいたので参考になりました！',
        '改善策が具体的で、自分も真似してみたいです。',
        '結果の振り返りまで書かれていて素晴らしいですね。',
        'タイマーで区切る方法、私も試してみます。',
        '小さな改善の積み重ねが大事だと感じました。',
    ];

    public function run(): void
    {
        mt_srand(20261011);
        $today = CarbonImmutable::now(config('app.timezone'))->startOfDay();

        $users = collect(self::USERS)->map(fn (array $attributes): User => $this->demoUser($attributes));

        foreach ($users as $index => $user) {
            if ($user->dailyRecords()->withTrashed()->exists()) {
                continue;
            }

            $this->createDailyRecords($user, self::USERS[$index]['interval'], $index, $today);
        }

        [$guest, $sato, $tanaka, $suzuki] = $users->all();
        $this->follow($guest, $sato);
        $this->follow($sato, $guest);
        $this->follow($guest, $tanaka);
        $this->follow($suzuki, $guest);
        $this->follow($tanaka, $sato);

        $this->createReactions($users->all());
    }

    /**
     * @param  array{email: string, name: string, age: int, gender: string}  $attributes
     */
    private function demoUser(array $attributes): User
    {
        return User::query()->firstOrCreate(
            ['email' => $attributes['email']],
            [
                'name' => $attributes['name'],
                'age' => $attributes['age'],
                'gender' => $attributes['gender'],
                // Demo users are not meant to sign in with a password; the guest uses the guest login button.
                'password' => Hash::make(Str::random(40)),
            ],
        );
    }

    private function createDailyRecords(User $user, int $interval, int $offset, CarbonImmutable $today): void
    {
        for ($daysAgo = self::DAYS - $offset; $daysAgo >= 0; $daysAgo -= $interval) {
            $recordDate = $today->subDays($daysAgo);
            $theme = self::THEMES[mt_rand(0, count(self::THEMES) - 1)];
            $createdAt = $recordDate->setTime(21, mt_rand(0, 59));

            $record = new DailyRecord([
                'record_date' => $recordDate->toDateString(),
                'actions' => $theme['actions'],
                'good_points' => $theme['good'],
                'improvement_points' => $theme['issue'],
                'improvement_strategy' => $theme['strategy'],
                'expected_result' => $theme['expected'],
                'is_public' => mt_rand(1, 100) <= 75,
            ]);
            $record->user()->associate($user);
            $record->created_at = $createdAt;
            $record->updated_at = $createdAt;
            $record->save();

            $this->createImprovementRecord($record, $theme, $recordDate, $today, $daysAgo);
        }
    }

    /**
     * Records within the input period are sometimes left empty so the reflection reminder is visible.
     *
     * @param  array{results: array<string, string>}  $theme
     */
    private function createImprovementRecord(DailyRecord $record, array $theme, CarbonImmutable $recordDate, CarbonImmutable $today, int $daysAgo): void
    {
        if ($daysAgo <= 6 && mt_rand(1, 100) <= 60) {
            return;
        }

        $evaluation = $this->randomEvaluation();
        $reflectedAt = $recordDate->addDays(min(mt_rand(1, 6), $daysAgo))->setTime(22, 0);

        $reason = self::NOT_EXECUTED_REASONS[mt_rand(0, count(self::NOT_EXECUTED_REASONS) - 1)];
        $attributes = $evaluation === 'D'
            ? [
                'execution_status' => ImprovementRecord::STATUS_NOT_EXECUTED,
                'not_executed_reason' => $reason,
                'not_executed_note' => $reason === 'other' ? '体調を崩して予定を変更したため' : null,
            ]
            : [
                'execution_status' => ImprovementRecord::STATUS_EXECUTED,
                'result_evaluation' => $evaluation,
                'executed_at' => $reflectedAt->toDateString(),
                'actual_result' => $theme['results'][$evaluation],
            ];

        $improvementRecord = $record->improvementRecord()->make($attributes);
        $improvementRecord->created_at = $reflectedAt;
        $improvementRecord->updated_at = $reflectedAt;
        $improvementRecord->save();
    }

    private function randomEvaluation(): string
    {
        $roll = mt_rand(1, 100);

        return match (true) {
            $roll <= 20 => 'A',
            $roll <= 60 => 'B',
            $roll <= 85 => 'C',
            default => 'D',
        };
    }

    private function follow(User $follower, User $followed): void
    {
        Follow::query()->firstOrCreate([
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);
    }

    /**
     * @param  list<User>  $users
     */
    private function createReactions(array $users): void
    {
        foreach ($users as $user) {
            $otherUserIds = collect($users)->reject(fn (User $other): bool => $other->is($user))->pluck('id');
            $records = DailyRecord::query()
                ->whereIn('user_id', $otherUserIds)
                ->where('is_public', true)
                ->latestRecordFirst()
                ->limit(12)
                ->get();

            foreach ($records as $index => $record) {
                if ($index % 2 === 0) {
                    $user->likes()->withTrashed()->firstOrCreate(['daily_record_id' => $record->id]);
                }

                if ($index % 4 === 1 && ! $record->comments()->where('user_id', $user->id)->exists()) {
                    $comment = new Comment(['body' => self::COMMENTS[mt_rand(0, count(self::COMMENTS) - 1)]]);
                    $comment->user()->associate($user);
                    $comment->dailyRecord()->associate($record);
                    $comment->save();
                }
            }
        }
    }
}
