<html>
<head>
</head>
<body>
  <table>
    <tr>
      <?php foreach (($table_headers?:array()) as $th): ?>
        <th><?php echo $th; ?></th>
      <?php endforeach; ?>
    </tr>
    <?php foreach (($models?:array()) as $model): ?>
      <tr>
        <?php foreach (($table_headers?:array()) as $key): ?>
          <td>
            <a href="sessions/<?php echo $model['id']; ?>">
              <?php echo $model[$key]; ?>
            </a>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>