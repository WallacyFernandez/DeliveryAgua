<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

// Obtém o status do filtro da URL
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'todos';

if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   
   // Se o status for alterado para Concluído
   if($payment_status == 'Concluído'){
      // Primeiro, obtém os dados do pedido
      $get_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
      $get_order->execute([$order_id]);
      $order_data = $get_order->fetch(PDO::FETCH_ASSOC);
      
      // Insere no histórico
      $insert_history = $conn->prepare("INSERT INTO `order_history` (order_id, user_id, empresa_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
      $insert_history->execute([
         $order_id,
         $order_data['user_id'],
         $order_data['empresa_id'],
         $order_data['name'],
         $order_data['number'],
         $order_data['email'],
         $order_data['method'],
         $order_data['address'],
         $order_data['total_products'],
         $order_data['total_price'],
         $order_data['placed_on'],
         'Concluído'
      ]);
   }

   // Atualiza o status do pedido
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'status do pagamento atualizado!';
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Pedidos <?= ucfirst($status_filter) ?></title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/admin_header.php'; ?>

   <section class="orders">
      <h1 class="heading">Pedidos <?= ucfirst($status_filter) ?></h1>

      <div class="box-container">
         <?php
            // Query base para selecionar pedidos e nome da empresa
            $query = "SELECT o.*, e.company_name 
                     FROM `orders` o 
                     LEFT JOIN `empresas` e ON o.empresa_id = e.id";
            $params = [];

            // Adiciona filtro por status se necessário
            if ($status_filter === 'pendente') {
               $query .= " WHERE o.payment_status = 'Pendente'";
            } elseif ($status_filter === 'em_rota') {
               $query .= " WHERE o.payment_status = 'Em rota de entrega'";
            } elseif ($status_filter === 'concluido') {
               $query .= " WHERE o.payment_status = 'Concluído'";
            }

            $select_orders = $conn->prepare($query);
            $select_orders->execute($params);
            
            if($select_orders->rowCount() > 0){
               while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
         ?>
         <div class="box">
            <p>Data : <span><?= $fetch_orders['placed_on']; ?></span></p>
            <p>Nome : <span><?= $fetch_orders['name']; ?></span></p>
            <p>Telefone : <span><?= $fetch_orders['number']; ?></span></p>
            <p>Endereço : <span><?= $fetch_orders['address']; ?></span></p>
            <p>Total de produtos : <span><?= $fetch_orders['total_products']; ?></span></p>
            <p>Preço total : <span>R$<?= $fetch_orders['total_price']; ?></span></p>
            <p>Método de pagamento : <span><?= $fetch_orders['method']; ?></span></p>
            <p>Empresa : <span><?= $fetch_orders['company_name'] ?? 'N/A'; ?></span></p>
            <form action="" method="post">
               <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
               <select name="payment_status" class="select">
                  <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
                  <option value="Pendente">Pendente</option>
                  <option value="Em rota de entrega">Em rota de entrega</option>
                  <option value="Concluído">Concluído</option>
               </select>
               <div class="flex-btn">
                  <input type="submit" value="atualizar" class="option-btn" name="update_payment">
                  <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('deletar este pedido?');">deletar</a>
               </div>
            </form>
         </div>
         <?php
               }
            }else{
               echo '<p class="empty">nenhum pedido encontrado!</p>';
            }
         ?>
      </div>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html>
