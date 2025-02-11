#!/bin/bash
# すでに Techtinic ディレクトリ内にいる前提で、各ディレクトリを作成するスクリプト

# 現在のディレクトリが Techtinic であるか確認
if [[ $(basename "$PWD") != "Techtinic" ]]; then
    echo "エラー: Techtinic ディレクトリ内で実行してください"
    exit 1
fi

# app/ 以下のディレクトリ
mkdir -p app/Console/Commands
mkdir -p app/Models
mkdir -p app/Services

# config/ ディレクトリ
mkdir -p config

# database/ 以下のディレクトリ
mkdir -p database/migrations
mkdir -p database/seeders

# routes/ ディレクトリ
mkdir -p routes

# tests/ 以下のディレクトリ
mkdir -p tests/Feature
mkdir -p tests/Unit

# docker/ 以下のディレクトリ
mkdir -p docker/nginx
mkdir -p docker/php
mkdir -p docker/postgresql

# プロジェクトルートに必要なファイルも作成（中身は後で編集可能）
touch .env
touch composer.json
touch package.json
touch README.md

echo "Techtinic ディレクトリ内にディレクトリ構成が作成されました。"
