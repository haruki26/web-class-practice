<?php
$dbh = new PDO("mysql:host=mysql;dbname=example_db", "root", "");

$reply_to = isset($_GET["reply_to"]) ? intval($_GET["reply_to"]) : null;

if (isset($_POST["body"])) {
  // POSTで送られてくるフォームパラメータ body がある場合

  $image_filename = null;
  if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {
    // アップロードされた画像がある場合

    $mime_type = mime_content_type($_FILES["image"]["tmp_name"]);
    if (!$mime_type || preg_match("/^image\//", $mime_type) !== 1) {
      // アップロードされたものが画像ではなかった場合処理を強制的に終了
      header("HTTP/1.1 302 Found");
      header("Location: ./bbs.php");
      return;
    }

    // 元のファイル名から拡張子を取得
    $pathinfo = pathinfo($_FILES["image"]["name"]);
    $extension = $pathinfo["extension"];
    // 新しいファイル名を決める。他の投稿の画像ファイルと重複しないように時間+乱数で決める。
    $image_filename =
      strval(time()) . bin2hex(random_bytes(25)) . "." . $extension;
    $filepath = "/var/www/upload/image/" . $image_filename;
    move_uploaded_file($_FILES["image"]["tmp_name"], $filepath);
  }

  // insertする
  $insert_sth = $dbh->prepare(
    "INSERT INTO bbs_entries (body, image_filename, reply_to) VALUES (:body, :image_filename, :reply_to)",
  );
  $insert_sth->execute([
    ":body" => $_POST["body"],
    ":image_filename" => $image_filename,
    ":reply_to" => $reply_to,
  ]);

  // 処理が終わったらリダイレクトする
  // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
  header("HTTP/1.1 302 Found");
  header("Location: ./bbs.php");
  return;
}

// いままで保存してきたものを取得
$select_sth = $dbh->prepare(
  "SELECT * FROM bbs_entries ORDER BY created_at DESC",
);
$select_sth->execute();
?>

<head>
  <title>画像投稿できる掲示板</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <link rel="stylesheet" href="./style.css">
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.0/dist/browser-image-compression.js"></script>
</head>
<body>

  <!-- フォームのPOST先はこのファイル自身にする -->
  <form
    method="POST"
    action="./bbs.php<?= $reply_to ? "?reply_to=" . $reply_to : "" ?>"
    enctype="multipart/form-data">
    <?php if ($reply_to): ?>
      <span>返信中: <?= $reply_to ?></span>
    <?php endif; ?>
    <textarea name="body" required></textarea>
    <div>
      <input type="file" accept="image/*" name="image" id="imageInput">
    </div>
    <button type="submit">送信</button>
  </form>

  <hr>

  <div id="entries">
    <?php foreach ($select_sth as $entry): ?>
      <dl id="entry-<?= $entry["id"] ?>">
        <dt>ID</dt>
        <dd><a href="?reply_to=<?= $entry["id"] ?>"><?= $entry["id"] ?></a></dd>
        <dt>日時</dt>
        <dd><?= $entry["created_at"] ?></dd>
        <dt>内容</dt>
        <dd>
          <?php if (isset($entry["reply_to"])): ?>
            <a
              href="#entry-<?= $entry["reply_to"] ?>"
            >
              &gt;&gt; <?= $entry["reply_to"] ?>
            </a>
          <?php endif; ?>
          <?= nl2br(htmlspecialchars($entry["body"]))
      // 必ず htmlspecialchars() すること
      ?>
          <?php if (
            !empty($entry["image_filename"])
          ):// 画像がある場合は img 要素を使って表示
             ?>
          <div>
            <img src="/image/<?= $entry["image_filename"] ?>">
          </div>
          <?php endif; ?>
        </dd>
      </dl>
    <?php endforeach; ?>
  </div>

  <script type="text/javascript">
  const fileInput = document.getElementById("imageInput");

  const validate = (files) => {
    if (files.length !== 1) {
      return "単一の画像ファイルを選択してください";
    } else if (!files[0].type.startsWith("image/")) {
      return "画像ファイルを選択してください";
    }
    return null;
  }

  fileInput.addEventListener('change', async (e) => {
    const input = e.currentTarget;
    input.disabled = true;
    const msg = validate(e.target.files)

    if (msg !== null) {
      alert(msg);
      e.target.value = '';
    }

    const originalImage = e.target.files[0];

    if (originalImage.size > 5 * 1000 * 1000) {
      try {
        const compressedImage = await imageCompression(originalImage, { maxSizeMb: 5 });

        const resizedImage = new File([compressedImage], e.target.files[0].name, { type: e.target.files[0].type });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(resizedImage);
        e.target.files = dataTransfer.files;

        alert("要領が大きいため画像を圧縮しました");
      } catch {
        alert("画像の圧縮に失敗しました");
        e.target.value = '';
      }
    }

    input.disabled = false;
  });
  </script>
</body>
