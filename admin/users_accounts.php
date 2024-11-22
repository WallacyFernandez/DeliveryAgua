<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_user->execute([$delete_id]);
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
   $delete_orders->execute([$delete_id]);
   $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
   $delete_messages->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:users_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Contas de Usuários</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/admin_header.php'; ?>

   <section class="accounts">
      <h1 class="heading">Contas de Usuários</h1>
      <div class="box-container">
         <?php
            $select_accounts = $conn->prepare("SELECT * FROM `users`");
            $select_accounts->execute();
            if($select_accounts->rowCount() > 0){
               while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
         ?>
         <div class="box">
            <p> ID Usuário : <span><?= $fetch_accounts['id']; ?></span> </p>
            <p> Nome : <span><?= $fetch_accounts['name']; ?></span> </p>
            <p> Email : <span><?= $fetch_accounts['email']; ?></span> </p>
            <div class="flex-btn">
               <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('Deletar esta conta? Todos os dados relacionados também serão excluídos!')" class="delete-btn">deletar</a>
            </div>
         </div>
         <?php
            }
         }else{
            echo '<p class="empty">nenhum usuário cadastrado!</p>';
         }
         ?>
      </div>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html>
