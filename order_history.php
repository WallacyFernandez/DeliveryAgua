<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Histórico de Pedidos</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="orders">
   <h1 class="heading">Histórico de Pedidos</h1>
   <div class="box-container">
   <?php
      $select_history = $conn->prepare("SELECT * FROM `order_history` WHERE user_id = ? ORDER BY placed_on DESC");
      $select_history->execute([$user_id]);
      if($select_history->rowCount() > 0){
         while($fetch_history = $select_history->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p>Data : <span><?= $fetch_history['placed_on']; ?></span></p>
      <p>Nome : <span><?= $fetch_history['name']; ?></span></p>
      <p>E-mail : <span><?= $fetch_history['email']; ?></span></p>
      <p>Número : <span><?= $fetch_history['number']; ?></span></p>
      <p>Endereço : <span><?= $fetch_history['address']; ?></span></p>
      <p>Método de Pagamento : <span><?= $fetch_history['method']; ?></span></p>
      <p>Seus pedidos : <span><?= $fetch_history['total_products']; ?></span></p>
      <p>Preço total : <span>R$<?= $fetch_history['total_price']; ?>/-</span></p>
      <p>Status do pagamento : <span style="color:#27ae60;">Concluído</span></p>
      <p>Status do pedido : <span style="color:<?php 
         if($fetch_history['order_status'] == 'Preparando'){ 
            echo '#e67e22'; // laranja
         }elseif($fetch_history['order_status'] == 'Em rota de entrega'){ 
            echo '#3498db'; // azul
         }else{ 
            echo '#27ae60'; // verde
         }; 
      ?>"><?= $fetch_history['order_status']; ?></span></p>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">Nenhum pedido no histórico ainda!</p>';
      }
   ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>