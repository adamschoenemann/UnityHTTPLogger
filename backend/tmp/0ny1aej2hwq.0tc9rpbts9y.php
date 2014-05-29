<html>
<head>
</head>
<body>
  <h2>Basic Info</h2>
  <?php echo Utils::display_table($model); ?>
  <table id="basic_info">
    <tr>
      <?php foreach (($table_headers?:array()) as $th): ?>
        <th><?php echo $th; ?></th>
      <?php endforeach; ?>
    </tr>
    <tr>
      <?php foreach (($table_headers?:array()) as $key): ?>
        <td>
          <a href="sessions/<?php echo $model['id']; ?>">
            <?php echo $model[$key]; ?>
          </a>
        </td>
      <?php endforeach; ?>
    </tr>
  </table>
  <h2>Stats</h2>
  <table id="stats">
    <tr>
      <?php foreach ((array_keys($stats)?:array()) as $key): ?>
        <th><?php echo $key; ?></th>
      <?php endforeach; ?>
    </tr>
    <tr>
      <?php foreach (($stats?:array()) as $val): ?>
        <td><?php echo $val; ?></td>
      <?php endforeach; ?>
    </tr>
  </table>
  <div id="entries">
    <h2>Entries</h2>
    <?php foreach (($entries?:array()) as $entry): ?>
      <div id="entry_<?php echo $entry['id']; ?>">
        <h3>Entry <?php echo $entry['id']; ?></h3>
        <table id="basic_info">
          <tr>
            <th>session_id</th><th>event</th><th>timestamp</th>
          </tr>
          <tr>
            <td><?php echo $entry['session_id']; ?></td>
            <td><?php echo $entry['event']; ?></td>
            <td><?php echo $entry['timestamp']; ?></td>
          </tr>
        </table>
        <h4>Vectors</h4>
        <table id="vector3">
          <tr>
            <?php foreach ((array_keys($entry['vector3']['0'])?:array()) as $key): ?>
              <th><?php echo $key; ?></th>
            <?php endforeach; ?>
          </tr>
          <?php foreach (($entry['vector3']?:array()) as $vector3): ?>
            <tr>
              <?php foreach ((array_values($vector3)?:array()) as $val): ?>
                <td><?php echo $val; ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>