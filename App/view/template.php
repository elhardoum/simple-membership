<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $title; ?></title>
  <link rel="icon" type="image/x-icon" href="<?php echo url('/favicon.ico', true); ?>">
  <link rel="canonical" href="<?php echo App\View::getController()->canonical(); ?>" />
  <?php echo $head; ?>
</head>
<body>
  <div id="contain">
    <div class="subco">
      <?php echo $content; ?>
    </div>
  </div>
  <?php echo $footer; ?>
</body>
</html>