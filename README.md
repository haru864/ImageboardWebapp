# Imageboard Webapp

ユーザーが画像やテキストコンテンツを投稿できるイメージボード Web アプリです。<br>
このプラットフォームは、ユーザーがメインスレッドを開始し、他のユーザーがそれに<br>
返信できるスレッドベースのディスカッションを促進するものです。<br>
投稿にユーザーデータが添付されていないため、すべての投稿は匿名です。<br>
ユーザーは、画像と共にコンテンツを投稿することで新しいスレッドを作成できます。<br>
メインスレッドが作成されると、他のユーザーはテキストや画像を使ってそれに返信できます。

# DEMO

![imageboard_sample](https://github.com/haru864/ImageboardWebapp/assets/45516420/c5779c67-75a8-4b5a-929d-a0cf88cf6f00)

# Features

```Next.js```と```Material UI```を使うことで見やすく、使いやすいユーザーインターフェースを実現しました。
バックエンドにはPHPを使用し、MVCモデルを導入することで保守性に優れたコードベースを実現しました。<br>
ロガーを使い、PHPスクリプトで発生した例外、リクエスト/レスポンスをログファイルに記録することで、
エラーに関するユーザーからの問い合わせに対して、調査・回答することができるようになっています。<br>
アップロードされた画像ファイルはハッシュ値で名前を管理するため、同時に同じ画像がアップロードされても
それぞれ別々に管理することができるようになっています。また、画像ファイルをWebサーバーで保存することにより、
画像レスポンスの効率を向上させています。<br>
スレッド一覧画面では最新のスレッドを最大５件まで表示し、スレッドごとに状況がわかるようになっています。

# Usage

### スレッドの作成

subject(タイトル)、content(本文)、画像ファイルを選択してスレッドを作成します。<br>
画像ファイルはJPEG,GIF,PNGを選択可能で、ファイルサイズは設定ファイル(backend/config/.public.env)に
定義された```MAX_FILE_SIZE_BYTES```以下の値であればアップロード可能です。<br>
スレッドの作成に成功すると、リプライ画面に遷移します。

### リプライ

スレッドに対してリプライできます。content(本文)または画像ファイルのどちらか一方か両方を
リプライとして使用できます。それぞれの制約はスレッド作成時と同じです。<br>
スレッド一覧画面とリプライ画面ではアップロードされた画像のサムネイルが表示されますが、
この画面ではスレッドおよびリプライの画像をクリックすることで、アップロードされた画像を
原寸で確認することができます。

# Note

一定時間リプライが無いスレッドは自動的に削除されます。<br>
スレッドの存続時間は設定ファイル(backend/config/.public.env)で変更可能です。<br>
また、アップロード可能なファイルサイズについて、同じファイルで最大値を設定できます。
