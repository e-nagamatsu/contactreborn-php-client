# Contact/Reborn PHP API クライアント

Contact/Reborn API 用の PHP クライアントライブラリです。メールアドレスの検証とブロックチェックサービスを簡単に利用できます。

## 機能

- メールアドレスのブロックチェック
- ユーザー独自のブロックリスト管理

## 要件

- PHP 7.4 以上または 8.0 以上
- Composer
- Contact/Reborn API アカウント

## インストール

Composer を使用してインストール：

```bash
composer require contactreborn/php-client
```

## API トークンの取得方法

### 1. アカウント登録

1. [Contact/Reborn](https://contact-reborn.net) にアクセス
2. 「新規登録」をクリック
3. メールアドレスとパスワードを入力して登録

### 2. メール認証

1. 登録したメールアドレスに確認メールが送信されます
2. メール内の認証コードから認証を完了

### 3. API トークンの作成

1. ログイン後、ダッシュボードへ移動
2. メニューから「API トークン」をクリック
3. 一覧に表示される無料トークンを選択

### 4. トークンの管理

- ダッシュボードでトークンの一覧が確認できます
- 不要になったトークンは削除可能です
- 各トークンの最終利用日時が表示されます
- 無料のトークンが利用可能です。

## クイックスタート

```php
<?php
require_once 'vendor/autoload.php';

use ContactReborn\ContactRebornClient;
use ContactReborn\Enums\CheckResult;

// APIトークンでクライアントを初期化
$client = new ContactRebornClient('your-api-token-here');

// メールアドレスのチェック
$result = $client->checkEmail('test@example.com');

// Enumを使用した結果判定（推奨）
if (CheckResult::isBlocked($result['result'])) {
    echo "このメールアドレスはブロックされています: " . $result['reason'];
} elseif (CheckResult::isSafe($result['result'])) {
    echo "このメールアドレスは安全です";
} elseif (CheckResult::needsReview($result['result'])) {
    echo "このメールアドレスは確認が必要です";
}
```

## 利用可能なメソッド

### メールチェック

#### 単一メールアドレスのチェック

```php
$result = $client->checkEmail('user@example.com');

// レスポンス例:
// [
//     'result' => 'pass',  // CheckResult::PASS, BLOCK, SUSPICIOUS, UNKNOWN
//     'is_blocked' => false,
//     'reason' => null,
//     'matched_rule' => null,
//     'confidence' => 0.95,
//     'checked_at' => '2025-09-08 12:00:00'
// ]
```

#### 複数メールアドレスの一括チェック

```php
$emails = [
    'user1@example.com',
    'user2@example.com',
    'blocked@tempmail.com'
];

$results = $client->batchCheckEmails($emails);

foreach ($results['results'] as $email => $result) {
    if (CheckResult::isBlocked($result['result'])) {
        echo "{$email} はブロックされています\n";
    }
}
```

### ブロックリスト管理

#### ブロックリストの取得

```php
$blockedList = $client->getBlockedEmails($page = 1, $perPage = 20);

foreach ($blockedList['data'] as $blocked) {
    echo "ブロック: {$blocked['email']} - {$blocked['reason']}\n";
}
```

#### ブロックメールの追加

```php
use ContactReborn\Enums\BlockType;

$result = $client->addBlockedEmail(
    'blocked@example.com',
    '不正なメール送信者として報告'
);
```

#### ブロックメールの削除

```php
$success = $client->removeBlockedEmail($id);
```

### 利用統計

```php
$stats = $client->getUsageStats('daily');

echo "本日のAPI呼び出し数: {$stats['calls_today']}\n";
echo "残り呼び出し可能数: {$stats['remaining_calls']}\n";
echo "レート制限: {$stats['rate_limit']}\n";
```

## Enum クラス

### CheckResult - チェック結果

```php
use ContactReborn\Enums\CheckResult;

// 結果の種類
CheckResult::PASS       // 安全
CheckResult::BLOCK      // ブロック
CheckResult::SUSPICIOUS // 疑わしい
CheckResult::UNKNOWN    // 不明

// ヘルパーメソッド
CheckResult::isBlocked($result)    // ブロック判定
CheckResult::isSafe($result)       // 安全判定
CheckResult::needsReview($result)  // 要確認判定
CheckResult::getLabel($result)     // ラベル取得
CheckResult::getDescription($result) // 説明取得
```

### BlockType - ブロックタイプ

```php
use ContactReborn\Enums\BlockType;

BlockType::FULL    // 完全一致
BlockType::PREFIX  // 前方一致
BlockType::SUFFIX  // ドメイン一致
BlockType::DOMAIN  // ドメインのみ
BlockType::PATTERN // パターン一致
```

## エラーハンドリング

```php
use ContactReborn\Exceptions\ApiException;
use ContactReborn\Exceptions\AuthenticationException;
use ContactReborn\Exceptions\RateLimitException;

try {
    $result = $client->checkEmail('test@example.com');

} catch (AuthenticationException $e) {
    // 認証エラー（APIトークンが無効）
    echo "認証エラー: " . $e->getMessage();

} catch (RateLimitException $e) {
    // レート制限エラー
    echo "レート制限: " . $e->getMessage();
    $retryAfter = $e->getRetryAfter(); // 再試行までの秒数

} catch (ApiException $e) {
    // その他のAPIエラー
    echo "APIエラー: " . $e->getMessage();
}
```

## WordPress プラグインとの統合例

```php
// Contact Form 7 との統合
add_filter('wpcf7_validate', 'check_email_with_contactreborn', 10, 2);

function check_email_with_contactreborn($result, $tags) {
    $submission = WPCF7_Submission::get_instance();
    if (!$submission) return $result;

    $data = $submission->get_posted_data();
    $email = $data['your-email'] ?? '';

    if ($email) {
        $client = new ContactReborn\ContactRebornClient(CONTACTREBORN_API_TOKEN);

        try {
            $check = $client->checkEmail($email);

            if (ContactReborn\Enums\CheckResult::isBlocked($check['result'])) {
                $result->invalidate($tags[0], 'このメールアドレスは使用できません。');
            }
        } catch (Exception $e) {
            // エラー時は通過させる（サービス停止を防ぐ）
            error_log('Contact/Reborn API Error: ' . $e->getMessage());
        }
    }

    return $result;
}
```

## 高度な設定

### タイムアウト設定

```php
$client = new ContactRebornClient($apiToken, [
    'timeout' => 60,  // 60秒のタイムアウト
]);
```

### カスタムベース URL（開発環境用）

```php
$client = new ContactRebornClient($apiToken, [
    'base_url' => 'https://staging-api.contact-reborn.net',
]);
```

## サンプルコード

詳細な使用例は `examples/` ディレクトリを参照してください：

- `basic_usage.php` - 基本的な使用方法
- `wordpress_plugin.php` - WordPress プラグインの実装例

## トラブルシューティング

### よくある問題

1. **「認証エラー」が発生する**

   - API トークンが正しくコピーされているか確認
   - トークンの前後に空白が含まれていないか確認
   - トークンが削除されていないか管理画面で確認

2. **「レート制限エラー」が発生する**

   - 無料プランの場合、API コール数に制限があります
   - 有料プランへのアップグレードを検討してください

3. **「タイムアウトエラー」が発生する**
   - タイムアウト設定を延長してみてください
   - ネットワーク接続を確認してください

## サポート

- **ドキュメント**: [https://contact-reborn.net/api-doc](https://contact-reborn.net/api-doc)
- **サポート**: support@contact-reborn.net
- **問題報告**: [GitHub Issues](https://github.com/contactreborn/php-client/issues)

## ライセンス

このライブラリは MIT ライセンスの下で公開されています。詳細は[LICENSE](LICENSE)ファイルを参照してください。

## 貢献

プルリクエストを歓迎します。大きな変更の場合は、まず issue を開いて変更内容について議論してください。

## 変更履歴

### v1.0.0 (2025-09-09)

- 初回リリース
