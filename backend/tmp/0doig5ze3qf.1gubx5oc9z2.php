<h3><?php echo $action; ?></h3>
<form action="" method="post">
  <table>
    <tr>
      <td>Title</td>
      <td>
        <input type="text" value="<?php echo $project['title']; ?>" name="title"/>
      </td>
    </tr>
    <tr>
      <td>
        Author
      </td>
      <td>
        <input type="text" value="<?php echo $project['author']; ?>" name="author" />
      </td>
    </tr>
    <tr>
      <td>Teaser</td>
      <td>
        <input type="text" value="<?php echo $project['teaser']; ?>" name="teaser" />
      </td>
    <tr>
      <td>Description</td>
      <td>
        <textarea name="description"><?php echo $project['description']; ?></textarea>
      </td>
    </tr>
  </table>
  <input type="submit" value="Submit" />
</form>
<script type="text/javascript">
  (function($){

    function getInput(name){
      return $("input[name='" + name + "']");
    }

    function isValueEmpty(input){
      return (input.val() === "");
    }

    // Easy, basic error handling
    // Abstract in the future for reuse
    $(document).ready(function(){
      $("input[type='submit']").click(function(){
        var errors = [];
        var names = ["title", "author", "teaser"];
        var fields = [];

        for (var i = 0; i < names.length; i++) {
          fields.push(getInput(names[i]));
        };

        fields.push($("textarea[name='description']"));
        
        for (var i = 0; i < fields.length; i++) {
          fields[i].removeAttr("style");
          var val = fields[i].val();
          if($.trim(val) == ""){
            errors.push(fields[i]);
          }
        };
        
        if(errors.length == 0){
          return true;
        }

        for (var i = 0; i < errors.length; i++) {
          errors[i].css("border", "1px solid red");
          alert(errors[i].attr("name") + " is incorrectly filled");
        };

        return false;
      });
    });
  })(jQuery);
</script>
<pre>
  <?php echo print_r($project); ?>
</pre>