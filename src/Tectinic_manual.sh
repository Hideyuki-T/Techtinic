#!/bin/bash
# Techtinic Usage Manual
# このスクリプトは、Techtinic の知識管理と対話の使い方を確認するためのマニュアルです。

cat << 'EOF'
==================================================
Techtinic Usage Manual
==================================================

【概要】
Techtinic は、ユーザーが知識を「教える」際に、以下の4つの要素で知識を整理して保存します：
  1. カテゴリー
  2. タグ
  3. タイトル
  4. 本文

また、対話時にこれらの情報を基に知識を参照し、最適な回答を返す仕組みです。

--------------------------------------------------
【1. 知識の構成要素】
--------------------------------------------------
① カテゴリー
   ・目的: 知識を大まかなグループや分類（例：dockerコマンド、Laravel設定、デプロイ手法 など）に分けるため。
   ・使い方:
       - 知識を教える際、既存のカテゴリーを選択するか、新たにカテゴリー名を入力して作成する。
       - 例: 「dockerコマンド」→ docker に関する操作やコマンドの知識を集約する。

② タグ
   ・目的: 知識に対して、さらに細かいキーワードやラベルを付与し、柔軟な検索を可能にする。
   ・使い方:
       - 知識登録時、カンマ区切りで複数のタグを入力する（例: docker, コンテナ, 起動）。
       - 後から対話時に、タグ一覧から選択してそのタグに関連する知識を参照できる。

③ タイトル
   ・目的: 知識の内容を一目で把握できるような簡潔な見出しを付ける。
   ・使い方:
       - 例: 「起動済のコンテナ一覧の表示」
       - タイトルは、検索時に一覧として表示され、ユーザーがどの知識を参照するかを決定するキーとなる。

④ 本文
   ・目的: 詳細な知識の内容や説明を記述する部分。
   ・使い方:
       - 例: 「docker ps」と入力し、コンテナの状態を確認する方法を記述する。
       - 本文は、実際に参照された際に詳細な回答としてユーザーに提示される。

--------------------------------------------------
【2. 知識を Techtinic に教える方法】
--------------------------------------------------
■ 対話形式の知識登録 (KnowledgeTeachCommand)
1. コマンド実行:
   $ php artisan knowledge:teach
2. プロンプトの流れ:
   ・「まず、知識のカテゴリーを選択してください（新規作成する場合は '新規' を選んでください）」
       例: 「dockerコマンド」を選択または新規作成。
   ・「知識のタイトルを入力してください:」
       例: 「起動済のコンテナ一覧の表示」
   ・「その内容を入力してください:」
       例: 「docker ps」と入力
   ・「この知識に関連するタグ（カンマ区切り）を入力してください:」
       例: 「docker, コンテナ, 状態確認」
3. 保存:
   - 入力が完了すると、Knowledge テーブルに知識が保存され、ピボットテーブルを通じてカテゴリーおよびタグとの関連付けが行われます.

--------------------------------------------------
【3. Techtinic の知識を参照して対話する方法】
--------------------------------------------------
■ 対話シナリオ (ChatCommand)
1. 対話開始:
   $ php artisan chat:run
   「それじゃあ話そうか。やめたくなったら 'exit' と入力してください。」
2. ユーザーが「どんなことを知ってる？」と入力すると、
   ・システムは登録されたタグ一覧（例: docker, Laravel, Deploy など）を表示します.
3. タグ選択:
   ・ユーザーが興味のあるタグを選択.
4. タグに関連する知識のタイトル一覧を表示:
   ・選択されたタグに紐付いた知識のタイトル一覧（例: 「起動済のコンテナ一覧の表示」）を提示.
5. タイトル選択と詳細表示:
   ・ユーザーがタイトルを選択すると、その知識の本文（詳細な説明）が表示されます.
   ・例: 「docker ps」 の実行方法や詳細な説明が提示される.

--------------------------------------------------
【4. マニュアルの運用と拡張】
--------------------------------------------------
■ 教える側 (知識登録)
   - $ php artisan knowledge:teach を実行し、カテゴリー、タイトル、本文、タグを入力して知識を登録.

■ 参照する側 (対話)
   - $ php artisan chat:run を実行し、対話の流れに従って、登録された知識を検索・表示する.

■ 今後の拡張
   - 登録された知識の編集・削除は、データベース上で行うか、専用の管理インターフェースを後から実装.
   - タグとカテゴリーを活用した柔軟な検索機能の拡充や、全文検索の導入も検討可能.


--------------------------------------------------
【5. ２つのモード】
--------------------------------------------------
■特定のキーワード「どんなことを知ってる？」の場合
この入力があった場合、システムは知識の検索モードに入り、
登録されたタグ一覧（または、後にカテゴリー一覧に変更する場合はその一覧）を表示し、
ユーザーがタグを選択して、選択されたタグに関連する知識のタイトル一覧を提示、
最終的にその中から一つ選んで、詳細な内容（本文）を出力する
というフローになります。

■それ以外の入力の場合（通常の会話）
もし「どんなことを知ってる？」以外の入力があった場合は、システムは以下のように動作します。
通常の対話処理にフォールバック
ChatCommand の handle() メソッドでは、特定のキーワードに該当しない場合、
AIEngine の getResponse($input) メソッドが呼ばれます。
現在のシンプルな実装では、AIEngine は入力されたキーワードを元に、
Knowledge テーブル内の title や content に対するキーワード検索 を実施します。
もし入力とマッチする知識が見つかれば、その知識の 内容（content） を返します。
マッチするものがなければ、
デフォルトメッセージ（例："申し訳ありません、その知識はまだ教えられていません。"）を返すようになっています。

◆つまり
「どんなことを知ってる？」 という特定のプロンプトは、知識の検索・参照機能をトリガーする専用のルートです。
それ以外の入力は、AIEngine のシンプルなキーワード検索による応答として処理されます。
例えば、ユーザーが「こんにちは」や「最近どう？」と入力した場合、
現在の実装では知識に該当するものがなければ、デフォルトのエラーメッセージが返されるか、
またはキーワードにマッチした知識があればその内容が返されます。

==================================================
以上が Techtinic の使い方マニュアルです。
このマニュアルを参考に、知識の登録と対話を進めてください。

※将来的には※
対話機能を拡張して、より柔軟な会話や別の機能（知識の更新、削除、関連知識の提示など）を実装
自然言語処理の機能を強化して、より高度な対話ができるようにシステム全体を拡張すること
なども視野に入れ、Techtinicをアップグレードしていきたいです。
==================================================
EOF
