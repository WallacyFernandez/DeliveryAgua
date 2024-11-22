<?php
include '../components/connect.php';
session_start();

$empresa_id = $_SESSION['empresa_id'];

if(!isset($empresa_id)){
   header('location:empresa_login.php');
}

if(isset($_POST['update'])){
   $pid = $_POST['pid'];
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   $update_product = $conn->prepare("UPDATE `products` SET name = ?, price = ?, details = ? WHERE id = ?");
   $update_product->execute([$name, $price, $details, $pid]);

   $message[] = 'produto atualizado com sucesso!';

   $old_image_01 = $_POST['old_image_01'];
   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   if(!empty($image_01)){
      if($image_size_01 > 2000000){
         $message[] = 'tamanho da imagem é muito grande!';
      }else{
         $update_image_01 = $conn->prepare("UPDATE `products` SET image_01 = ? WHERE id = ?");
         $update_image_01->execute([$image_01, $pid]);
         move_uploaded_file($image_tmp_name_01, $image_folder_01);
         unlink('../uploaded_img/'.$old_image_01);
         $message[] = 'imagem 01 atualizada com sucesso!';
      }
   }

   $old_image_02 = $_POST['old_image_02'];
   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/'.$image_02;

   if(!empty($image_02)){
      if($image_size_02 > 2000000){
         $message[] = 'tamanho da imagem é muito grande!';
      }else{
         $update_image_02 = $conn->prepare("UPDATE `products` SET image_02 = ? WHERE id = ?");
         $update_image_02->execute([$image_02, $pid]);
         move_uploaded_file($image_tmp_name_02, $image_folder_02);
         unlink('../uploaded_img/'.$old_image_02);
         $message[] = 'imagem 02 atualizada com sucesso!';
      }
   }

   $old_image_03 = $_POST['old_image_03'];
   $image_03 = $_FILES['image_03']['name'];
   $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
   $image_size_03 = $_FILES['image_03']['size'];
   $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
   $image_folder_03 = '../uploaded_img/'.$image_03;

   if(!empty($image_03)){
      if($image_size_03 > 2000000){
         $message[] = 'tamanho da imagem é muito grande!';
      }else{
         $update_image_03 = $conn->prepare("UPDATE `products` SET image_03 = ? WHERE id = ?");
         $update_image_03->execute([$image_03, $pid]);
         move_uploaded_file($image_tmp_name_03, $image_folder_03);
         unlink('../uploaded_img/'.$old_image_03);
         $message[] = 'imagem 03 atualizada com sucesso!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <title>Atualizar Produto</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   <?php include '../components/empresa_header.php'; ?>

   <section class="update-product">
      <?php
         $update_id = $_GET['update'];
         $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
         $select_products->execute([$update_id]);
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
      ?>
      <form action="" method="post" enctype="multipart/form-data">
         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
         <input type="hidden" name="old_image_01" value="<?= $fetch_products['image_01']; ?>">
         <input type="hidden" name="old_image_02" value="<?= $fetch_products['image_02']; ?>">
         <input type="hidden" name="old_image_03" value="<?= $fetch_products['image_03']; ?>">
         <div class="image-container">
            <div class="main-image">
               <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
            </div>
            <div class="sub-image">
               <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
               <img src="../uploaded_img/<?= $fetch_products['image_02']; ?>" alt="">
               <img src="../uploaded_img/<?= $fetch_products['image_03']; ?>" alt="">
            </div>
         </div>
         <span>Atualizar nome</span>
         <input type="text" name="name" required class="box" maxlength="100" placeholder="digite o nome do produto" value="<?= $fetch_products['name']; ?>">
         <span>Atualizar preço</span>
         <input type="number" name="price" required class="box" min="0" max="9999999999" placeholder="digite o preço do produto" onkeypress="if(this.value.length == 10) return false;" value="<?= $fetch_products['price']; ?>">
         <span>Atualizar detalhes</span>
         <textarea name="details" class="box" required cols="30" rows="10"><?= $fetch_products['details']; ?></textarea>
         <span>Atualizar imagem 01</span>
         <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
         <span>Atualizar imagem 02</span>
         <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
         <span>Atualizar imagem 03</span>
         <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
         <div class="flex-btn">
            <input type="submit" name="update" class="btn" value="atualizar">
            <a href="products.php" class="option-btn">voltar</a>
         </div>
      </form>
      <?php
            }
         }else{
            echo '<p class="empty">nenhum produto encontrado!</p>';
         }
      ?>
   </section>

   <script src="../js/admin_script.js"></script>
</body>
</html> 