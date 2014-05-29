	<div id="wrapper">
		<!-- h1 tag stays for the logo, you can use the a tag for linking the index page -->
		<h1><a href=""><span><?php echo $title; ?></span></a></h1>

		<!-- You can name the links with lowercase, they will be transformed to uppercase by CSS, we prefered to name them with uppercase to have the same effect with disabled stylesheet -->
		<ul id="mainNav">
			<!-- Use the "active" class for the active menu item  -->
			<?php foreach (($menu?:array()) as $item): ?>
				<li><a href="<?php echo $item['href']; ?>"><?php echo $item['name']; ?></a></li>
			<?php endforeach; ?>
			<li class="logout"><a href="#">LOGOUT</a></li>
		</ul>
		<!-- // #end mainNav -->

		<div id="containerHolder">
			<div id="container">
				<div id="sidebar">
					<ul class="sideNav">
            <?php if (isset($submenu)): ?>
              <?php foreach (($submenu?:array()) as $item): ?>
                <li><a href="<?php echo $CTRL; ?>/<?php echo $item['href']; ?>"><?php echo $item['name']; ?></a></li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <!-- // .sideNav -->
        </div>    
        <!-- // #sidebar -->

        <!-- h2 stays for breadcrumbs -->
        <h2><a href=""><?php echo $page; ?></a> &raquo; <a href="#" class="active">Print resources</a></h2>

        <div id="main">
         <?php if (isset($content)): ?>
          <?php echo $this->render($content,$this->mime,get_defined_vars()); ?>
        <?php endif; ?>
      </div>
      <!-- // #main -->

      <div class="clear"></div>
    </div>
    <!-- // #container -->
  </div>	
  <!-- // #containerHolder -->

  <p id="footer">Feel free to use and customize it. <a href="http://www.perspectived.com">Credit is appreciated.</a></p>
</div>
<!-- // #wrapper -->

