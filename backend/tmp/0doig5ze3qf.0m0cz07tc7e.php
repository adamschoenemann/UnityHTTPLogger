<section id="main">
  <section id="header">
    <h1>Admin</h1>
  </section>
  <section id="content">
    <section id="sidebar">
      <nav>
        <ul>
          <?php foreach (($menu?:array()) as $item): ?>
            <li>
              <a href="<?php echo $item['href']; ?>"><?php echo $item['name']; ?></a>
            </li>          
          <?php endforeach; ?>
        </ul>
      </nav>
    </section>
  </section>
</section>