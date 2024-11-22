<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<header class="header">
   <section class="flex">
      <a href="../empresa/dashboard.php" class="logo">Painel<span>Empresa</span></a>
      
      <nav class="navbar">
         <a href="../empresa/dashboard.php">Início</a>
         <a href="../empresa/products.php">Produtos</a>
         <a href="../empresa/placed_orders.php">Pedidos</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `empresas` WHERE id = ?");
            $select_profile->execute([$empresa_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['company_name']; ?></p>
         <a href="../empresa/update_profile.php" class="btn">Atualizar Perfil</a>
         <div class="flex-btn">
            <a href="../empresa/register_empresa.php" class="option-btn">Registrar</a>
            <a href="../empresa/empresa_login.php" class="option-btn">Entrar</a>
         </div>
         <a href="../components/empresa_logout.php" class="delete-btn" onclick="return confirm('Sair do site?');">Sair</a>
      </div>
   </section>
</header> 