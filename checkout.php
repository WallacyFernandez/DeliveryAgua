<?php

include 'components/connect.php';

session_start();

// Adicionar esta linha para definir o timezone para São Paulo/Brasil
date_default_timezone_set('America/Sao_Paulo');

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

if(isset($_POST['order'])){
   if(isset($_POST['saved_address']) && $_POST['saved_address'] != 'new' && $_POST['saved_address'] != ''){
      // Usar endereço salvo
      $address_id = $_POST['saved_address'];
      $select_address = $conn->prepare("SELECT * FROM `addresses` WHERE id = ? AND user_id = ?");
      $select_address->execute([$address_id, $user_id]);
      $fetch_address = $select_address->fetch(PDO::FETCH_ASSOC);
      
      $name = $fetch_address['name'];
      $number = $fetch_address['number'];
      $email = $fetch_address['email'];
      $address = $fetch_address['flat'] . ', ' . $fetch_address['street'] . ', ' . 
                $fetch_address['city'] . ', ' . $fetch_address['state'] . ', ' . 
                $fetch_address['country'] . ' - ' . $fetch_address['pin_code'];
   } else {
      // Processar novo endereço
      $name = $_POST['name'];
      $number = $_POST['number'];
      $email = $_POST['email'];
      $address = 'Numero da casa/apto: '. $_POST['flat'] .', '. $_POST['street'] .', '. 
                $_POST['city'] .', '. $_POST['state'] .', '. 
                $_POST['country'] .' - '. $_POST['pin_code'];

      // Salvar novo endereço se solicitado
      if(isset($_POST['save_address']) && $_POST['save_address'] == 'on'){
         $insert_address = $conn->prepare("INSERT INTO `addresses`(user_id, name, number, email, flat, street, city, state, country, pin_code) VALUES(?,?,?,?,?,?,?,?,?,?)");
         $insert_address->execute([$user_id, $name, $number, $email, $_POST['flat'], $_POST['street'], $_POST['city'], $_POST['state'], $_POST['country'], $_POST['pin_code']]);
      }
   }

   $method = $_POST['method'];
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];
   $placed_on = date('Y-m-d H:i:s');

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){

      $cart_items = $conn->prepare("SELECT p.empresa_id 
                                   FROM cart c 
                                   JOIN products p ON c.pid = p.id 
                                   WHERE c.user_id = ? 
                                   LIMIT 1");
      $cart_items->execute([$user_id]);
      $empresa_data = $cart_items->fetch(PDO::FETCH_ASSOC);
      $empresa_id = $empresa_data['empresa_id'];

      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, empresa_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $empresa_id, $name, $number, $email, $method, $address, $total_products, $total_price, $placed_on, 'Pendente']);

      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      $message[] = 'pedido feito com sucesso!';
      header('location:home.php');
      exit();
   }else{
      $message[] = 'seu carrinho está vazio';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>finalizar compra</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST">

   <h3>Seus Pedidos</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $cart_items = array();
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p> <?= $fetch_cart['name']; ?> <span>(<?= 'R$'.$fetch_cart['price'].'/- x '. $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         }else{
            echo '<p class="empty">seu carrinho está vazio!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
         <div class="grand-total">Total Geral : <span>R$<?= $grand_total; ?>/-</span></div>
      </div>

      <h3>faça seus pedidos</h3>

      <div class="flex">
         <div class="inputBox" style="width:100%; margin-bottom:1rem;">
            <span>Selecione um endereço salvo:</span>
            <select name="saved_address" class="box" id="saved-address">
               <option value="">Selecione um endereço</option>
               <?php
                  $select_addresses = $conn->prepare("SELECT * FROM `addresses` WHERE user_id = ?");
                  $select_addresses->execute([$user_id]);
                  if($select_addresses->rowCount() > 0){
                     while($fetch_address = $select_addresses->fetch(PDO::FETCH_ASSOC)){
                        echo '<option value="'.$fetch_address['id'].'">'.$fetch_address['street'].', '.$fetch_address['city'].'</option>';
                     }
                  }
               ?>
               <option value="new">Novo endereço</option>
            </select>
         </div>
      </div>

      <div id="address-form" class="flex">
         <div class="inputBox">
            <span>Seu nome :</span>
            <input type="text" name="name" id="name" placeholder="digite seu nome" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Seu Número :</span>
            <input type="number" name="number" placeholder="digite seu número" class="box" min="0" max="99999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Seu E-mail :</span>
            <input type="email" name="email" placeholder="digite seu e-mail" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Pagamento na Entrega :</span>
            <select name="method" class="box" required>
               <option value="Em espécie">Em Espécie</option>
               <option value="Pix">Pix</option>
               <option value="cartão de crédito">Cartão de Crédito</option>
               <option value="Cartão de Débito">Cartão de Débito</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Numero da casa/apto:</span>
            <input type="text" name="flat" placeholder="ex. número do apartamento" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Rua e Bairro:</span>
            <input type="text" name="street" placeholder="nome da rua" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Cidade :</span>
            <input type="text" name="city" placeholder="Pau dos Ferros" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Estado:</span>
            <input type="text" name="state" placeholder="Rio Grande do Norte" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>País :</span>
            <input type="text" name="country" placeholder="Brasil" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>CEP :</span>
            <input type="number" min="0" name="pin_code" placeholder="ex. 59830000" min="99999999" max="99999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
         <div class="inputBox">
            <input type="checkbox" name="save_address" id="save_address">
            <label for="save_address">Salvar este endereço</label>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" value="fazer pedido">

   </form>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addressSelect = document.getElementById('saved-address');
    const addressForm = document.getElementById('address-form');
    const saveAddressCheck = document.getElementById('save_address');

    addressSelect.addEventListener('change', function() {
        if(this.value === 'new') {
            addressForm.style.display = 'flex';
            clearFormFields();
        } else if(this.value !== '') {
            fetchAddress(this.value);
            addressForm.style.display = 'none';
        }
    });

    function fetchAddress(addressId) {
        fetch(`get_address.php?id=${addressId}`)
            .then(response => response.json())
            .then(data => {
                if(!data.error) {
                    fillFormFields(data);
                }
            });
    }

    function fillFormFields(data) {
        document.getElementById('name').value = data.name;
        document.querySelector('input[name="number"]').value = data.number;
        document.querySelector('input[name="email"]').value = data.email;
        document.querySelector('input[name="flat"]').value = data.flat;
        document.querySelector('input[name="street"]').value = data.street;
        document.querySelector('input[name="city"]').value = data.city;
        document.querySelector('input[name="state"]').value = data.state;
        document.querySelector('input[name="country"]').value = data.country;
        document.querySelector('input[name="pin_code"]').value = data.pin_code;
    }

    function clearFormFields() {
        document.getElementById('name').value = '';
        document.querySelector('input[name="number"]').value = '';
        document.querySelector('input[name="email"]').value = '';
        document.querySelector('input[name="flat"]').value = '';
        document.querySelector('input[name="street"]').value = '';
        document.querySelector('input[name="city"]').value = '';
        document.querySelector('input[name="state"]').value = '';
        document.querySelector('input[name="country"]').value = '';
        document.querySelector('input[name="pin_code"]').value = '';
    }
});
</script>

</body>
</html>
