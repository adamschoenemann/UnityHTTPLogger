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
          <td><?php echo $model[$key]; ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>