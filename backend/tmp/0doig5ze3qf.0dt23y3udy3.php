<h3>Projects</h3>
<table cellpadding="0" cellspading="0">
  <tr>
    <th>Name</th>
    <th>Author</th>
    <th>Teaser</th>
    <th span="3"></th>
  </tr>
  <?php foreach (($projects?:array()) as $project): ?>
    <tr>
      <td><?php echo $project['name']; ?></td>
      <td><?php echo $project['author']; ?></td>
      <td><?php echo $project['teaser']; ?></td>
      <td class="action">
        <a href="<?php echo $CTRL; ?>/view/<?php echo $project['id']; ?>" class="view">View</a>
        <a href="<?php echo $CTRL; ?>/edit/<?php echo $project['id']; ?>" class="edit">Edit</a>
        <a href="<?php echo $CTRL; ?>/delete/<?php echo $project['id']; ?>" class="delete">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<!-- <form action="" class="jNice">
  <h3>Sample section</h3>
  <table cellpadding="0" cellspacing="0">
    <tr>
      <td>Vivamus rutrum nibh in felis tristique vulputate</td>
      <td class="action"><a href="#" class="view">View</a><a href="#" class="edit">Edit</a><a href="#" class="delete">Delete</a></td>
    </tr>                        
    <tr class="odd">
      <td>Duis adipiscing lorem iaculis nunc</td>
      <td class="action"><a href="#" class="view">View</a><a href="#" class="edit">Edit</a><a href="#" class="delete">Delete</a></td>
    </tr>                        
    <tr>
      <td>Donec sit amet nisi ac magna varius tempus</td>
      <td class="action"><a href="#" class="view">View</a><a href="#" class="edit">Edit</a><a href="#" class="delete">Delete</a></td>
    </tr>                        
    <tr class="odd">
      <td>Duis ultricies laoreet felis</td>
      <td class="action"><a href="#" class="view">View</a><a href="#" class="edit">Edit</a><a href="#" class="delete">Delete</a></td>
    </tr>                        
    <tr>
      <td>Vivamus rutrum nibh in felis tristique vulputate</td>
      <td class="action"><a href="#" class="view">View</a><a href="#" class="edit">Edit</a><a href="#" class="delete">Delete</a></td>
    </tr>                        
  </table>
  <h3>Another section</h3>
  <fieldset>
    <p><label>Sample label:</label><input type="text" class="text-long" /></p>
    <p><label>Sample label:</label><input type="text" class="text-medium" /><input type="text" class="text-small" /><input type="text" class="text-small" /></p>
    <p><label>Sample label:</label>
      <select>
        <option>Select one</option>
        <option>Select two</option>
        <option>Select tree</option>
        <option>Select one</option>
        <option>Select two</option>
        <option>Select tree</option>
      </select>
    </p>
    <p><label>Sample label:</label><textarea rows="1" cols="1"></textarea></p>
    <input type="submit" value="Submit Query" />
  </fieldset>
</form> -->