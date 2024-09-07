<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'suscriptor') {
    header("Location: index.html");
    exit();
}

require 'vendor/autoload.php';

$user_id = $_SESSION['user_id'];  // Obtener el user_id de la sesión

echo "<div class='login-container mt-5'>";
echo "<h2 class='text-center'>Bienvenido suscriptor</h2>";
echo "<p class='text-center'>Puedes subir y gestionar tus archivos aquí.</p><br>";

// Conectar a la base de datos
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_SERVER'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Manejar subida de archivos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $filename = $_FILES['file']['name'];
        $filepath = 'uploads/' . basename($filename);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
            // Guardar el archivo en la base de datos con el user_id
            $sql = "INSERT INTO archivos (nombre_archivo, ruta, user_id) VALUES ('$filename', '$filepath', '$user_id')";
            $conn->query($sql);
            echo "<div class='alert alert-success text-center'>Archivo subido correctamente.</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error al subir el archivo.</div>";
        }
    }
}

// Mostrar archivos existentes (solo los archivos del suscriptor actual)
$archivos = $conn->query("SELECT * FROM archivos WHERE user_id = $user_id");

echo "<h3 class='text-center'>Archivos Subidos</h3>";
echo "<div class='table-responsive'>";
echo "<table class='table table-bordered table-striped'>";
echo "<thead class='thead-dark'><tr><th>Nombre de Archivo</th><th>Acciones</th></tr></thead>";
echo "<tbody>";

while ($row = $archivos->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['nombre_archivo'] . "</td>";
    echo "<td>";
    echo "<a href='" . $row['ruta'] . "' class='btn btn-primary btn-sm' download>Descargar</a> ";
    echo "<form method='POST' style='display:inline;' action='eliminar_archivo.php'>
            <input type='hidden' name='id' value='" . $row['id'] . "'>
            <button type='submit' class='btn btn-danger btn-sm'>Eliminar</button>
          </form>";
    echo "</td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";
?>

<!-- Formulario para subir archivos -->
<div class="row justify-content-center">
    <div class="col-md-6">
        <form method="POST" enctype="multipart/form-data" class="form-inline">
            <div class="form-group mb-3">
                <label for="file" class="mr-2">Subir Archivo:</label>
                <input type="file" class="form-control mr-2" name="file" id="file" required>
            </div>
            <button type="submit" class="btn btn-success mb-3">Subir Archivo</button>
        </form>
    </div>
</div>
</div> <!-- End Container -->

<!-- Meta Viewport para responsividad -->
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- Estilos para combinar con el login -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');

    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        font-family: 'Orbitron', sans-serif;
        background: linear-gradient(45deg, #0f0c29, #302b63, #24243e);
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff;
    }

    .login-container {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 40px;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        width: 800px;
        color: #fff;
    }

    h2, h3 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 2.5em;
        text-shadow: 0 0 10px #00ffff;
    }

    .table {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;  /* El texto en la tabla será blanco */
    }

    .table td, .table th {
        color: #fff;  /* Asegura que el texto de las celdas sea blanco */
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(255, 255, 255, 0.05); /* Un leve fondo para las filas impares */
    }

    .btn-primary {
        background: linear-gradient(45deg, #00ffff, #00ff00);
        color: #000;
        font-weight: bold;
    }

    .btn-danger {
        background-color: #ff4c4c;
        color: #fff;
    }

    .btn-success {
        background: linear-gradient(45deg, #00ff00, #00ffff);
        color: #000;
        font-weight: bold;
    }

    input[type="file"] {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border: none;
        border-radius: 5px;
    }
</style>

<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
