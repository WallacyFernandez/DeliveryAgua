<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Dashboard</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="dashboard">
   <h1 class="heading">Painel</h1>
   <div class="box-container">
      <div class="box">
         <h3>Bem-vindo!</h3>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="update_profile.php" class="btn">Atualizar Perfil</a>
      </div>

      <div class="box">
         <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_pendings->execute(['Pendente']);
            if($select_pendings->rowCount() > 0){
               while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
                  $total_pendings += $fetch_pendings['total_price'];
               }
            }
         ?>
         <h3><span>R$</span><?= $total_pendings; ?><span>/-</span></h3>
         <p>Total pendente</p>
         <a href="placed_orders.php?status=pendente" class="btn">Pedidos Pendentes</a>
      </div>

      <div class="box">
         <?php
            $total_delivery = 0;
            $select_delivery = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_delivery->execute(['Em rota de entrega']);
            if($select_delivery->rowCount() > 0){
               while($fetch_delivery = $select_delivery->fetch(PDO::FETCH_ASSOC)){
                  $total_delivery += $fetch_delivery['total_price'];
               }
            }
         ?>
         <h3><span>R$</span><?= $total_delivery; ?><span>/-</span></h3>
         <p>Em rota de entrega</p>
         <a href="placed_orders.php?status=em_rota" class="btn">Pedidos em Rota</a>
      </div>

      <div class="box">
         <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['Concluído']);
            if($select_completes->rowCount() > 0){
               while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
                  $total_completes += $fetch_completes['total_price'];
               }
            }
         ?>
         <h3><span>R$</span><?= $total_completes; ?><span>/-</span></h3>
         <p>Pedidos completos</p>
         <a href="placed_orders.php?status=concluido" class="btn">Pedidos Concluídos</a>
      </div>

      <div class="box">
         <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $number_of_orders = $select_orders->rowCount()
         ?>
         <h3><?= $number_of_orders; ?></h3>
         <p>Total de pedidos</p>
         <a href="placed_orders.php" class="btn">Todos os Pedidos</a>
      </div>

      <div class="box">
         <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            $number_of_products = $select_products->rowCount()
         ?>
         <h3><?= $number_of_products; ?></h3>
         <p>Total de produtos</p>
         <a href="products.php" class="btn">Ver produtos</a>
      </div>

      <div class="box">
         <?php
            $select_users = $conn->prepare("SELECT * FROM `users`");
            $select_users->execute();
            $number_of_users = $select_users->rowCount()
         ?>
         <h3><?= $number_of_users; ?></h3>
         <p>Usuários</p>
         <a href="users_accounts.php" class="btn">Ver Usuários</a>
      </div>

      <div class="box">
         <?php
            $select_admins = $conn->prepare("SELECT * FROM `admins`");
            $select_admins->execute();
            $number_of_admins = $select_admins->rowCount()
         ?>
         <h3><?= $number_of_admins; ?></h3>
         <p>Administradores</p>
         <a href="admin_accounts.php" class="btn">Ver Admins</a>
      </div>

      <div class="box">
         <?php
            $select_empresas = $conn->prepare("SELECT * FROM `empresas`");
            $select_empresas->execute();
            $number_of_empresas = $select_empresas->rowCount()
         ?>
         <h3><?= $number_of_empresas; ?></h3>
         <p>Empresas</p>
         <a href="empresa_accounts.php" class="btn">Ver Empresas</a>
      </div>

      <div class="box">
         <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $number_of_messages = $select_messages->rowCount()
         ?>
         <h3><?= $number_of_messages; ?></h3>
         <p>Mensagens</p>
         <a href="messages.php" class="btn">Ver mensagens</a>
      </div>
   </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
