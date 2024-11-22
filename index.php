<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Qsede.Com</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="home-bg">

<section class="home">

   <div class="swiper home-slider">
   
   <div class="swiper-wrapper">

      <div class="swiper-slide slide">
         <div class="image">
            <img src="images/botijões.png" alt="">
         </div>
         <div class="content">
            <span>Cupons de até 30%</span>
            <h3>Veja Produtos Com Desconto</h3>
            <a href="category.php?category=smartphone" class="btn">Compre Agora</a>
         </div>
      </div>

      <div class="swiper-slide slide">
         <div class="image">
            <img src="images/entrega.png" alt="">
         </div>
         <div class="content">
            <span>Cansado de ter que pedir água sempre?</span>
            <h3>Contrate Nossos Serviços de Entrega de Água Mensal</h3>
            <a href="category.php?category=watch" class="btn">Ver Serviços</a>
         </div>
      </div>

      <div class="swiper-slide slide">
         <div class="image">
            <img src="images/pngwing.com.png" alt="">
         </div>
         <div class="content">
            <span>Produtos de Qualidade e Segurança</span>
            <h3>Compre As Melhores Águas Da Sua Região</h3>
            <a href="shop.php" class="btn">Compre Agora</a>
         </div>
      </div>

   </div>

      <div class="swiper-pagination"></div>

   </div>

</section>

</div>

<section class="category">

   <h1 class="heading">Marcas Confiáveis</h1>

   <div class="swiper category-slider">

   <div class="swiper-wrapper">

   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>
   <a href="category.php?category=laptop" class="swiper-slide slide">
      <img src="images/icon-logo.png" alt="">
      <h3>San Valle</h3>
   </a>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>

<section class="home-products">

   <h1 class="heading">Produtos mais recentes</h1>

   <div class="swiper products-slider">

   <div class="swiper-wrapper">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="swiper-slide slide">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_product['name']; ?></div>
      <div class="flex">
         <div class="price"><span>R$</span><?= $fetch_product['price']; ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="adicionar ao carrinho" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">nenhum produto adicionado ainda!</p>';
   }
   ?>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>









<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

<script src="js/script.js"></script>

<script>

var swiper = new Swiper(".home-slider", {
   loop: true,
   spaceBetween: 20,
   speed: 2000,
   autoplay: {
      delay: 5000,
      disableOnInteraction: false,
      pauseOnMouseEnter: true,
   },
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
      dynamicBullets: true,
   },
});

var swiper = new Swiper(".category-slider", {
   loop: true,
   spaceBetween: 25,
   speed: 1000,
   autoplay: {
      delay: 6000,
      disableOnInteraction: false,
      pauseOnMouseEnter: true,
   },
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
      dynamicBullets: true,
   },
   breakpoints: {
      0: {
         slidesPerView: 2,
         grid: { rows: 1 },
      },
      650: {
         slidesPerView: 3,
         grid: { rows: 1 },
      },
      768: {
         slidesPerView: 4,
      },
      1024: {
         slidesPerView: 5,
      },
   },
});

var swiper = new Swiper(".products-slider", {
   loop: true,
   spaceBetween: 25,
   speed: 1000,
   grabCursor: true,
   autoplay: {
      delay: 4500,
      disableOnInteraction: false,
      pauseOnMouseEnter: true,
   },
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
      dynamicBullets: true,
   },
   breakpoints: {
      550: {
         slidesPerView: 2,
      },
      768: {
         slidesPerView: 2,
      },
      1024: {
         slidesPerView: 3,
      },
   },
});

</script>

</body>
</html>
