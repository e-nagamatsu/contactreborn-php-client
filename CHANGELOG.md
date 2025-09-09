# 変更履歴

このプロジェクトのすべての注目すべき変更は、このファイルに文書化されます。

フォーマットは [Keep a Changelog](https://keepachangelog.com/ja/1.0.0/) に基づいており、
このプロジェクトは [セマンティック バージョニング](https://semver.org/lang/ja/) に準拠しています。

## [Unreleased]

## [1.0.0] - 2025-09-09

### 追加
- 初回リリース
- メールアドレスのブロックチェック機能
- 複数メールアドレスの一括チェック機能
- ユーザー独自のブロックリスト管理機能
- CheckResult Enum クラスによる結果判定
- BlockType Enum クラスによるブロックタイプ管理
- 包括的なエラーハンドリング（認証エラー、レート制限、API例外）
- WordPress プラグインとの統合サンプル
- 詳細な日本語ドキュメント

### 技術仕様
- PHP 7.4+ / 8.0+ サポート
- Guzzle HTTP Client 7.0 使用
- PSR-12 コーディング規約準拠

[Unreleased]: https://github.com/contactreborn/php-client/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/contactreborn/php-client/releases/tag/v1.0.0