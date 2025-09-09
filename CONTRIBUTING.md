# 貢献ガイドライン

Contact/Reborn PHP Client への貢献を検討いただきありがとうございます！

## 貢献方法

### バグ報告

バグを見つけた場合は、[GitHub Issues](https://github.com/contactreborn/php-client/issues) で報告してください。

報告時に含めていただきたい情報：
- PHPのバージョン
- ライブラリのバージョン
- エラーメッセージの全文
- 再現手順
- 期待される動作

### 機能リクエスト

新機能のアイデアがある場合は、まず Issue を作成してディスカッションしましょう。

### プルリクエスト

1. このリポジトリをフォーク
2. 機能ブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add some amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

### コーディング規約

- PSR-12 に準拠
- 意味のある変数名・関数名を使用
- 適切なコメントを追加（ただし過度なコメントは避ける）
- テストを追加

### テスト

新機能や修正には必ずテストを追加してください：

```bash
composer test
```

### ライセンス

貢献されたコードは MIT ライセンスの下で公開されることに同意いただいたものとみなします。