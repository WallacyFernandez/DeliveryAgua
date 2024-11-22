<?php
include '../components/connect.php';
session_start();

$empresa_id = $_SESSION['empresa_id'];

if(!isset($empresa_id)){
   header('location:empresa_login.php');
}

if(isset($_POST['add_product'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/'.$image_02;

   $image_03 = $_FILES['image_03']['name'];
   $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
   $image_size_03 = $_FILES['image_03']['size'];
   $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
   $image_folder_03 = '../uploaded_img/'.$image_03;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ? AND empresa_id = ?");
   $select_products->execute([$name, $empresa_id]);

   if($select_products->rowCount() > 0){
      $message[] = 'nome do produto já existe!';
   }else{
      $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, image_01, image_02, image_03, empresa_id) VALUES(?,?,?,?,?,?,?)");
      $insert_products->execute([$name, $details, $price, $image_01, $image_02, $image_03, $empresa_id]);

      if($insert_products){
         if($image_size_01 > 2000000 OR $image_size_02 > 2000000 OR $image_size_03 > 2000000){
            $message[] = 'tamanho da imagem é muito grande!';
         }else{
            move_uploaded_file($image_tmp_name_01, $image_folder_01);
            move_uploaded_file($image_tmp_name_02, $image_folder_02);
            move_uploaded_file($image_tmp_name_03, $image_folder_03);
            $message[] = 'novo produto adicionado!';
         }
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/'.$fetch_delete_image['image_01']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_02']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_03']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   header('location:products.php');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Produtos</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/empresa_header.php'; ?>

   <section class="add-products">
      <h1 class="heading">Adicionar Produto</h1>
      <form action="" method="post" enctype="multipart/form-data">
         <div class="flex">
            <div class="inputBox">
               <span>Nome do produto (obrigatório)</span>
               <input type="text" class="box" required maxlength="100" placeholder="digite o nome do produto" name="name">
            </div>
            <div class="inputBox">
               <span>Preço do produto (obrigatório)</span>
               <input type="number" min="0" class="box" required max="9999999999" placeholder="digite o preço do produto" onkeypress="if(this.value.length == 10) return false;" name="price">
            </div>
            <div class="inputBox">
               <span>Imagem 01 (obrigatório)</span>
               <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
               <span>Imagem 02 (obrigatório)</span>
               <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
               <span>Imagem 03 (obrigatório)</span>
               <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
               <span>Detalhes do produto (obrigatório)</span>
               <textarea name="details" placeholder="digite os detalhes do produto" class="box" required maxlength="500" cols="30" rows="10"></textarea>
            </div>
         </div>
         <input type="submit" value="adicionar produto" class="btn" name="add_product">
      </form>
   </section>

   <section class="show-products">
      <h1 class="heading">Produtos Adicionados</h1>
      <div class="box-container">
      <?php
         $select_products = $conn->prepare("SELECT * FROM `products` WHERE empresa_id = ?");
         $select_products->execute([$empresa_id]);
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
      ?>
         <div class="box">
            <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
            <div class="name"><?= $fetch_products['name']; ?></div>
            <div class="price">R$<span><?= $fetch_products['price']; ?></span>/-</div>
            <div class="details"><span><?= $fetch_products['details']; ?></span></div>
            <div class="flex-btn">
               <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">atualizar</a>
               <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('deletar este produto?');">deletar</a>
            </div>
         </div>
      <?php
            }
         }else{
            echo '<p class="empty">nenhum produto adicionado ainda!</p>';
         }
      ?>
      </div>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html> 