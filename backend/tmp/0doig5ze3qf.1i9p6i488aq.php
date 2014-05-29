<!DOCTYPE html>
<html lang="en">
	<head>
		<base href="<?php echo $SCHEME.'://'.$HOST.$BASE.'/'.$UI; ?>" />
		<meta charset="<?php echo $ENCODING; ?>" />
		<title><?php echo $title; ?></title>
    <?php foreach (($css?:array()) as $href): ?>
      <link rel="stylesheet" href="<?php echo $href; ?>" type="text/css" />
    <?php endforeach; ?>
	</head>
	<body>
    <div id="scripts" style="display: none;">
      <?php foreach (($js?:array()) as $src): ?>
        <script type="text/javascript" src="<?php echo $src; ?>" ></script>
      <?php endforeach; ?>
    </div>
    <?php echo $this->render($inc,$this->mime,get_defined_vars()); ?>
	</body>
</html>
