<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>mission_5</title>
    <style>
      form {
        margin-bottom: 20px;
      }
      .flex {
        display: flex;
        justify-content: flex-start;
        align-items: center;
      }
    </style>
  </head>
  <body>

    <?php

      $error_message = "";

      //データベースへの接続
      $dsn = 'データベース';
      $user = 'ユーザー名';
      $password = 'パスワード';
      $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

      //データベース内にテーブルを作成
      $sql = "CREATE TABLE IF NOT EXISTS keijiban"
      ."("
      . "id INT AUTO_INCREMENT PRIMARY KEY,"
      . "name char(32),"
      . "comment TEXT,"
      . "postedAt TEXT,"
      . "password TEXT"
      .");";
      $stmt = $pdo->query($sql);

      //投稿機能

      //フォーム内が空でない場合に以下を実行する
      if (!empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['psw'])) {

          //入力データの受け取りを変数に代入
          $name = $_POST['name'];
          $comment = $_POST['comment'];
          $password = $_POST['psw'];

          //日付データを取得して変数に代入
          $postedAt = date("Y年m月d日 H:i:s");

          //editNOがないときは新規投稿、ある場合は編集と判断
          if (empty($_POST['editNO'])) {

              //以下、新規投稿

              //作成したテーブルに、insertを行ってデータを入力
              $sql = $pdo -> prepare("INSERT INTO keijiban (name, comment, postedAt, password) VALUES (:name, :comment, :postedAt, :password)");
              $sql -> bindParam(':name', $name, PDO::PARAM_STR);
              $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
              $sql -> bindParam(':postedAt', $postedAt, PDO::PARAM_STR);
              $sql -> bindParam(':password', $password, PDO::PARAM_STR);
              $sql -> execute();
          } else {

              //以下、編集実行機能

              //入力データの受け取りを変数に代入
              $editNO = $_POST['editNO'];

              //入力したデータをupdateによって編集する
              $id = $editNO; //変更する投稿番号
              $sql = 'update keijiban set name=:name,comment=:comment,postedAt=:postedAt,password=:password where id=:id';
              $stmt = $pdo->prepare($sql);
              $stmt->bindParam(':name',$name, PDO::PARAM_STR);
              $stmt->bindParam(':comment',$comment, PDO::PARAM_STR);
              $stmt->bindParam(':postedAt',$postedAt, PDO::PARAM_STR);
              $stmt->bindParam(':password',$password, PDO::PARAM_STR);
              $stmt->bindParam(':id',$id, PDO::PARAM_INT);
              $stmt->execute();
          }
      }

      //削除機能

      //削除フォームの送信の有無で処理を分岐
      if (!empty($_POST['dnum']) && !empty($_POST['psw_d'])) {

          //入力データの受け取りを変数に代入
          $delete = $_POST['dnum'];
          $password_d = $_POST['psw_d'];

          //データベースからデータをもらって$resultsという配列に入れ、$rowという配列に1行ずつ入れる
          $sql = 'SELECT * FROM keijiban';
          $stmt = $pdo->query($sql);
          $results = $stmt->fetchAll();
          foreach ($results as $row) {

                  //削除番号と行番号およびパスワードが一致した時削除
                  if ($delete == $row['id'] && $password_d == $row['password']) {

                    //入力したデータをdeleteによって削除する
                    $id = $delete;
                    $password = $password_d;
                    $sql = 'delete from keijiban where id=:id AND password=:password';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt->execute();

                  } elseif ($delete == $row['id'] && $password_d !== $row['password']) {

                      $error_message = "パスワードが違います。";

                  }
          }
      }

      //編集選択機能

      //編集フォームの送信の有無で処理を分岐
      if (!empty($_POST['edit']) && !empty($_POST['psw_e'])) {

          //入力データの受け取りを変数に代入
          $edit = $_POST['edit'];
          $password_e = $_POST['psw_e'];

          //データベースからデータをもらって$resultsという配列に入れ、$rowという配列に1行ずつ入れる
          $sql = 'SELECT * FROM keijiban';
          $stmt = $pdo->query($sql);
          $results = $stmt->fetchAll();
          foreach ($results as $row) {

                  //投稿番号と編集対象番号およびパスワードが一致したらその投稿の「名前」と「コメント」を取得
                  if ($edit == $row['id'] && $password_e == $row['password']) {

                      //投稿のそれぞれの値を取得し変数に代入
                      $editnumber = $row['id'];
                      $editname = $row['name'];
                      $editcomment = $row['comment'];

                      //既存の投稿フォームに、上記で取得した「名前」と「コメント」の内容が既に入っている状態で表示させる
                      //formのvalue属性で対応

                  } elseif ($edit == $row['id'] && $password_e !== $row['password']) {

                      $error_message = "パスワードが違います。";

                  }
          }
      }
    ?>

    <div class="flex">
    <p>この掲示板のテーマ:</p>
    <h1>好きな食べ物</h1>
    </div>

    <?php
      if(isset($error_message)) {
          echo $error_message;
      }
    ?>

    <form action="mission_5.php" method="post">
      <input type="text" name="name" placeholder="名前" value="<?php if(isset($editname)) {echo $editname;} ?>"><br>
      <input type="text" name="comment" placeholder="コメント" value="<?php if(isset($editcomment)) {echo $editcomment;} ?>"><br>
      <input type="text" name="psw" placeholder="パスワード">
      <input type="submit" name="submit" value="送信"><br>
      <input type="hidden" name="editNO" value="<?php if(isset($editnumber)) {echo $editnumber;} ?>">
    </form>

    <form action="mission_5.php" method="post">
      <input type="text" name="dnum" placeholder="削除対象番号"><br>
      <input type="text" name="psw_d" placeholder="パスワード">
      <input type="submit" name="delete" value="削除">
    </form>

    <form action="mission_5.php" method="post">
      <input type="text" name="edit" placeholder="編集対象番号"><br>
      <input type="text" name="psw_e" placeholder="パスワード">
      <input type="submit" value="編集">
    </form>

    <?php
      //表示機能

      //入力したデータをselectによって表示する
      $sql = 'SELECT * FROM keijiban';
      $stmt = $pdo->query($sql);
      $results = $stmt->fetchAll();
      foreach ($results as $row) {
              //$rowの中にはテーブルのカラム名が入る
              echo $row['id'] . ' ';
              echo $row['name'] . ' ';
              echo $row['comment'] . ' ';
              echo $row['postedAt'] . '<br>';
      }
    ?>
  </body>
</html>
