<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sobre</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="about">

   <div class="row">

      <div class="image">
         <img src="images/23.png" alt="">
      </div>

      <div class="content">
         <h3>Mensagem do Desenvolvedor:</h3>
         <p>Olá! Eu sou Wallacy. Um estudante de Engenharia de Software. Eu amo desenhar websites e explorar coisas novas. Aprender coisas novas é meu hobby.</p>

         <p>Gostaria de agradecer aoS <a href="#" target="_blank">Professores Da Faculdade</a> Por me guiar durante essa jornada e me tornar capaz de desenvolver projetos como este. </p>
         <a href="contact.php" class="btn">Entre em Contato</a>
      </div>

   </div>

</section>

<section class="reviews">
   
   <h1 class="heading">Avaliações dos Clientes</h1>

   <div class="swiper reviews-slider">

   <div class="swiper-wrapper">

      <div class="swiper-slide slide">
         <img src="images/pic-5.jpg" alt="">
         <p>Uso o serviço de entrega de água há algum tempo e nunca tive problemas com a qualidade dos produtos. O pedido online funciona muito bem. Minha única observação é que às vezes entregam quando estou ocupado, mesmo tendo definido um horário preferencial. Fora isso, tudo tem sido ótimo.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Mariana Silva</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-1.jpg" alt="">
         <p>É o primeiro serviço de entrega de água na minha região em que posso confiar completamente. Sempre verifico a entrega e, se houver algo errado, eles resolvem rapidamente. Às vezes nem preciso devolver o produto e eles já processam o reembolso. A empresa é muito rigorosa com a qualidade, por isso o serviço melhora a cada dia.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Rafael Santos</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-3.jpg" alt="">
         <p>O serviço de entrega de água é excelente. Há uma variedade de opções disponíveis. Os clientes podem devolver e receber reembolso total em até 7 dias facilmente. A empresa está impulsionando o negócio de entrega de água em domicílio na cidade. Oferece uma ótima oportunidade para pedir água online com facilidade.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Camila Oliveira</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-7.jpg" alt="">
         <p>Uso o serviço de entrega de água há quase 3 anos. Experiência excepcional com eles. A opção de agendar entregas e os pontos de retirada com frete grátis são serviços que ajudam muito a economizar.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Lucas Ferreira</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-2.jpg" alt="">
         <p>Utilizo os serviços deles há 2 anos e os considero extremamente confiáveis. A política de devolução oferece uma camada extra de confiança e tranquilidade. Caso o produto não atenda às suas expectativas ou se houver algum defeito, você pode devolvê-lo em até sete dias a partir da data de entrega.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Juliana Costa</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-6.jpg" alt="">
         <p>O serviço de entrega de água é incrível! Já fiz centenas de pedidos e nunca tive problemas. Eles entregam os produtos pontualmente, sem atrasos. A embalagem dos galões é resistente e as taxas de entrega são muito baixas. Um site simplesmente fantástico, continuarei comprando água deles.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Pedro Almeida</h3>
      </div>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>









<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

<script src="js/script.js"></script>

<script>

var swiper = new Swiper(".reviews-slider", {
   loop:true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable:true,
   },
   breakpoints: {
      0: {
        slidesPerView:1,
      },
      768: {
        slidesPerView: 2,
      },
      991: {
        slidesPerView: 3,
      },
   },
});

</script>

</body>
</html>
