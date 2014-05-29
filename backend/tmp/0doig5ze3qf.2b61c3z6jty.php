<h3>Galleries</h3>
<table cellpadding="0" cellspading="0">
  <tr>
    <th>Title</th>
    <th span="1"></th>
  </tr>
  <?php foreach (($galleries?:array()) as $gallery): ?>
    <tr>
      <td><?php echo $gallery['title']; ?></td>

      <td class="action">
        <a href="<?php echo $CTRL; ?>/view/<?php echo $project['id']; ?>" class="view">View</a>
        <a href="<?php echo $CTRL; ?>/edit/<?php echo $project['id']; ?>" class="edit">Edit</a>
        <a href="<?php echo $CTRL; ?>/delete/<?php echo $project['id']; ?>" class="delete">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>