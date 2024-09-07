<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'suscriptor') {
    header("Location: index.html");
    exit();
}

require 'vendor/autoload.php';

$user_id = $_SESSION['user_id'];  // Obtener el user_id del suscriptor

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

// Eliminar archivo del suscriptor autenticado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Verificar si el archivo pertenece al usuario autenticado
    $archivo = $conn->query("SELECT * FROM archivos WHERE id = $id AND user_id = $user_id")->fetch_assoc();
    if ($archivo) {
        unlink($archivo['ruta']);  // Eliminar el archivo del servidor
        $conn->query("DELETE FROM archivos WHERE id = $id");  // Eliminar de la base de datos
        header("Location: subscriber_dashboard.php");
    } else {
        echo "Archivo no encontrado o no tienes permiso para eliminarlo.";
    }
}

$conn->close();
?>
