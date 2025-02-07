<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';

// Verificar si el usuario está logueado y es un cliente
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'cliente') {
    header('Location: /auth/login.php');
    exit;
}

// Obtener el carrito del usuario desde la base de datos
$carrito = [];
try {
    $sql_carrito = "SELECT p.id, p.nombre, p.precio, c.cantidad 
                    FROM carrito c 
                    JOIN products p ON c.producto_id = p.id 
                    WHERE c.usuario_id = :usuario_id";
    $stmt_carrito = $pdo->prepare($sql_carrito);
    $stmt_carrito->execute([':usuario_id' => $_SESSION['id']]);
    $carrito = $stmt_carrito->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar el carrito: " . $e->getMessage();
    exit;
}

// Calcular el total de la compra
$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago ProBikeXtreme</title>
    <!-- Cargar Bootstrap (CDN) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Cargar el SDK de PayPal -->
    <script src="https://www.paypal.com/sdk/js?client-id=AeopX7xjoARaevuXdWqAE-ei096D90SUJmyNO3E_qB-GZiXLCpvNuU55VkWHfGI7okBn9uGfI9bVTjmm"
        data-sdk-integration-source="button-factory"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .paypal-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .paypal-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .paypal-header h2 {
            font-weight: bold;
            color: #343a40;
        }
        .paypal-header p {
            font-size: 1.2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="paypal-container">
        <div class="paypal-header">
            <h2>Pago ProBikeXtreme</h2>
            <p>Total a pagar: €<?php echo number_format($total, 2); ?></p>
        </div>
        <div id="paypal-button-container"></div>
    </div>

    <script>
        paypal.Buttons({
            style: {
                color: 'blue',
                shape: 'pill',
                label: 'pay'
            },
            createOrder: function (data, actions) {
                // Crear la orden con el total calculado
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: <?php echo $total; ?> // Total dinámico
                        }
                    }]
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (detalles) {
                    console.log('Detalles de la orden:', detalles);
                    // Obtener el ID de la orden de PayPal
                    var paypal_order_id = detalles.id;
                    
                    // Enviar una solicitud POST a procesar_pedido.php
                    return fetch('/carrito/procesar_pedido.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            carrito: <?php echo json_encode($carrito); ?>,
                            paypal_order_id: paypal_order_id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Respuesta del servidor:', data);
                        if (data.success) {
                            alert("Pago completado, pedido procesado correctamente.");
                            window.location.href = "../cliente/index_cliente.php";
                        } else {
                            alert("Error al procesar el pedido: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error en la petición:", error);
                        alert("Ocurrió un error en la comunicación con el servidor.");
                    });
                });
            },
            onCancel: function (data) {
                alert("Pago cancelado");
                console.log("Datos de cancelación:", data);
            }
        }).render('#paypal-button-container');
    </script>
</body>
</html>
