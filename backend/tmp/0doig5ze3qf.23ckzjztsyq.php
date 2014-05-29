<h3><?php echo $action; ?></h3>
<form action="" method="post">
  <table>
    <tr>
      <td>Title</td>
      <td>
        <input type="text" value="<?php echo $gallery['title']; ?>" name="title"/>
      </td>
    </tr>
  </table>
  <input type="submit" value="Submit" />
</form>