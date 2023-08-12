<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Postman</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="normalize.css">
</head>
<body>
    <form method="post" action="/">
        <label for="djname">DJ Name:</label>
        <input type="text" name="djname" value="DJ BrainZ" /><br />
        <label for="subtitle">Subtitle:</label>
        <input type="text" name="subtitle" /><br />
        <label for="dateline">Dateline:</label>
        <input type="text" name="dateline" value="Saturday x 1500 - 1700" /><br />
        <label for="station">Station:</label>
        <input type="text" name="station" value="Sub.fm" /><br />
        <label for="image">Base64 Image:</label>
        <textarea name="image"></textarea><br />
        <input type="submit" />
    </form>
</body>
</html>