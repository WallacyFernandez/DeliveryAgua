<?php
include '../components/connect.php';
session_start();

$empresa_id = $_SESSION['empresa_id'];

if(!isset($empresa_id)){
   header('location:empresa_login.php');
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Dashboard Empresa</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/empresa_header.php'; ?>

   <section class="dashboard">
      <h1 class="heading">Painel da Empresa</h1>
      
      <div class="box-container">
         <div class="box">
            <?php
               $select_profile = $conn->prepare("SELECT * FROM `empresas` WHERE id = ?");
               $select_profile->execute([$empresa_id]);
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            ?>
            <h3>Bem-vindo!</h3>
            <p><?= $fetch_profile['company_name']; ?></p>
            <a href="update_profile.php" class="btn">Atualizar Perfil</a>
         </div>

         <div class="box">
            <?php
               $total_pendings = 0;
               $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE empresa_id = ? AND payment_status = ?");
               $select_pendings->execute([$empresa_id, 'Pendente']);
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
               $total_completes = 0;
               $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE empresa_id = ? AND payment_status = ?");
               $select_completes->execute([$empresa_id, 'Concluído']);
               if($select_completes->rowCount() > 0){
                  while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
                     $total_completes += $fetch_completes['total_price'];
                  }
               }
            ?>
            <h3><span>R$</span><?= $total_completes; ?><span>/-</span></h3>
            <p>Pedidos concluídos</p>
            <a href="placed_orders.php?status=concluido" class="btn">Pedidos Concluídos</a>
         </div>

         <div class="box">
            <?php
               $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE empresa_id = ?");
               $select_orders->execute([$empresa_id]);
               $number_of_orders = $select_orders->rowCount()
            ?>
            <h3><?= $number_of_orders; ?></h3>
            <p>Total de pedidos</p>
            <a href="placed_orders.php" class="btn">Todos os pedidos</a>
         </div>

         <div class="box">
            <?php
               $select_products = $conn->prepare("SELECT * FROM `products` WHERE empresa_id = ?");
               $select_products->execute([$empresa_id]);
               $number_of_products = $select_products->rowCount()
            ?>
            <h3><?= $number_of_products; ?></h3>
            <p>Produtos cadastrados</p>
            <a href="products.php" class="btn">Ver produtos</a>
         </div>
      </div>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html> 