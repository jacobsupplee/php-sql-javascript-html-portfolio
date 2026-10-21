<?php // sqltest.php
  require_once 'login.php';

  try
  {
    $pdo = new PDO($attr, $user, $pass, $opts);
  }
  catch (PDOException $e)
  {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
  }

  if (isset($_POST['delete']) && isset($_POST['isbn']))
  {
    // Uses a prepared statement so the ISBN is treated as a value, not SQL.
    $isbn = $_POST['isbn'];
    $query = "DELETE FROM classics WHERE isbn = :isbn";
    $statement = $pdo->prepare($query);
    $statement->execute([':isbn' => $isbn]);
  }

  if (isset($_POST['author'])   &&
      isset($_POST['title'])    &&
      isset($_POST['category']) &&
      isset($_POST['year'])     &&
      isset($_POST['isbn']))
  {
    $author   = get_post($pdo, 'author');
    $title    = get_post($pdo, 'title');
    $category = get_post($pdo, 'category');
    $year     = get_post($pdo, 'year');
    $isbn     = get_post($pdo, 'isbn');
    
    $query    = "INSERT INTO classics VALUES" .
      "($author, $title, $category, $year, $isbn)";
    $result = $pdo->query($query);
  }

  echo <<<_END
  <form action="sqltest.php" method="post"><pre>
    Author <input type="text" name="author">
     Title <input type="text" name="title">
  Category <input type="text" name="category">
      Year <input type="text" name="year">
      ISBN <input type="text" name="isbn">
           <input type="submit" value="ADD RECORD">
  </pre></form>
_END;

  $query  = "SELECT * FROM classics";
  $result = $pdo->query($query);

  while ($row = $result->fetch(PDO::FETCH_OBJ)) //Added PDO::FETCH_OBJ, returning aeach row as an object
  {
      //changed from brackets to arrows because the row is an object
$r0 = htmlspecialchars($row->author); 
$r1 = htmlspecialchars($row->title);
$r2 = htmlspecialchars($row->category);
$r3 = htmlspecialchars($row->year);
$r4 = htmlspecialchars($row->isbn);
    
    echo <<<_END
  <pre>
    Author $r0
     Title $r1
  Category $r2
      Year $r3
      ISBN $r4
  </pre>
  <form action='sqltest.php' method='post'>
  <input type='hidden' name='delete' value='yes'>
  <input type='hidden' name='isbn' value='$r4'>
  <input type='submit' value='DELETE RECORD'></form>
_END;
  }

  function get_post($pdo, $var)
  {
    return $pdo->quote($_POST[$var]);
  }
?>