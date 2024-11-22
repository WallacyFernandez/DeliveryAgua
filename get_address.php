<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   header('location:user_login.php');
   exit;
}

if(isset($_GET['id'])){
   $address_id = $_GET['id'];
   
   $select_address = $conn->prepare("SELECT * FROM `addresses` WHERE id = ? AND user_id = ?");
   $select_address->execute([$address_id, $user_id]);
   
   if($select_address->rowCount() > 0){
      $address = $select_address->fetch(PDO::FETCH_ASSOC);
      header('Content-Type: application/json');
      echo json_encode($address);
   }else{
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Endereço não encontrado']);
   }
}
