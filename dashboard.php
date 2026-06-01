<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location:index.php");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
    background:#efedf8;
    font-family:'Segoe UI';
}

.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    background:#2d1569;
    padding:20px;
    color:white;
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:40px;
}

.logo i{
    font-size:35px;
    color:#ff9800;
}

.avatar{
    width:80px;
    height:80px;
    background:#ff9800;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:auto;
    font-size:35px;
    font-weight:bold;
}

.user{
    text-align:center;
    margin-bottom:40px;
}

.menu a{
    display:block;
    color:white;
    text-decoration:none;
    padding:15px;
    border-radius:12px;
    margin-bottom:10px;
    transition:.3s;
}

.menu a:hover{
    background:#ff9800;
}

.main{
    margin-left:260px;
    padding:30px;
}

.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:30px;
}

.card-box{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 5px 10px rgba(0,0,0,.05);
}

.chart-box{
    background:white;
    border-radius:20px;
    padding:20px;
}

.logout{
    background:#ff9800;
    color:white;
    padding:10px 20px;
    border-radius:10px;
    text-decoration:none;
}

</style>

</head>
<body>

<div class="sidebar">

    <div class="logo">
        <i class="fa-solid fa-utensils"></i>
        <h4>Mi Restaurante</h4>
    </div>

    <div class="user">

        <div class="avatar">
            A
        </div>

        <h5 class="mt-3">
            <?php echo $_SESSION['usuario']; ?>
        </h5>

    </div>

    <div class="menu">

        <a href="#">
            <i class="fa-solid fa-chart-line"></i>
            Dashboard
        </a>

        <a href="#">
            <i class="fa-solid fa-cash-register"></i>
            Punto de Venta
        </a>

        <a href="#">
            <i class="fa-solid fa-motorcycle"></i>
            Delivery
        </a>

        <a href="#">
            <i class="fa-solid fa-calendar"></i>
            Reservas
        </a>

        <a href="#">
            <i class="fa-solid fa-users"></i>
            Clientes
        </a>

    </div>

</div>

<div class="main">

    <div class="d-flex justify-content-between mb-4">

        <div>
            <h1>Bienvenido 👋</h1>
            <p>Panel Administrativo</p>
        </div>

        <a href="logout.php" class="logout">
            Cerrar Sesión
        </a>

    </div>

    <!-- CARDS -->
    <div class="cards">

        <div class="card-box">
            <h5>Ventas Hoy</h5>
            <h2>S/ 1,250</h2>
        </div>

        <div class="card-box">
            <h5>Mesas Activas</h5>
            <h2>7</h2>
        </div>

        <div class="card-box">
            <h5>Ventas Mes</h5>
            <h2>S/ 8,500</h2>
        </div>

        <div class="card-box">
            <h5>Pedidos</h5>
            <h2>32</h2>
        </div>

    </div>

    <!-- GRAFICO -->
    <div class="chart-box">

        <h4>Ventas Mensuales</h4>

        <canvas id="grafico"></canvas>

    </div>

</div>

<script>

const ctx = document.getElementById('grafico');

new Chart(ctx, {

    type:'line',

    data:{
        labels:['Ene','Feb','Mar','Abr','May','Jun'],

        datasets:[{
            label:'Ventas',

            data:[1200,2000,1800,3000,4500,5200],

            borderColor:'#ff9800',

            backgroundColor:'rgba(255,152,0,.2)',

            fill:true,

            tension:.4
        }]
    }

});

</script>

</body>
</html>