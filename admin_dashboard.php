<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_SERVER'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Manejar operaciones CRUD y búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $new_username = $_POST['new_username'];
        $new_password = $_POST['new_password'];
        $role = 'suscriptor';
        $sql = "INSERT INTO usuarios (username, password, role) VALUES ('$new_username', '$new_password', '$role')";
        $conn->query($sql);
    } elseif (isset($_POST['upload_excel'])) {
        $file = $_FILES['excel_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        foreach ($rows as $row) {
            $email = $row[0];
            $password = $row[1];
            $check_user = $conn->query("SELECT * FROM usuarios WHERE username='$email'");
            if ($check_user->num_rows == 0) {
                $sql = "INSERT INTO usuarios (username, password, role) VALUES ('$email', '$password', 'suscriptor')";
                $conn->query($sql);
            }
        }
    } elseif (isset($_POST['edit_user'])) {
        $id = $_POST['user_id'];
        $edit_username = $_POST['edit_username'];
        $edit_password = $_POST['edit_password'];
        if (!empty($edit_password)) {
            $sql = "UPDATE usuarios SET username='$edit_username', password='$edit_password' WHERE id=$id";
        } else {
            $sql = "UPDATE usuarios SET username='$edit_username' WHERE id=$id";
        }
        $conn->query($sql);
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        $sql = "DELETE FROM usuarios WHERE id=$id";
        $conn->query($sql);
    }
}

// Paginación
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $users_query = "SELECT * FROM usuarios WHERE role='suscriptor' AND username LIKE '%$search%' LIMIT $start, $limit";
    $total_query = "SELECT COUNT(*) FROM usuarios WHERE role='suscriptor' AND username LIKE '%$search%'";
} else {
    $users_query = "SELECT * FROM usuarios WHERE role='suscriptor' LIMIT $start, $limit";
    $total_query = "SELECT COUNT(*) FROM usuarios WHERE role='suscriptor'";
}

$users = $conn->query($users_query);
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestión de Usuarios</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
            align-items: flex-start;
            color: #fff;
            padding-top: 50px;
        }

        .admin-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 40px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            width: 100%;
            max-width: 1000px;
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
            color: #fff;
        }

        .table td, .table th {
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(45deg, #00ffff, #00ff00);
            color: #000;
            font-weight: bold;
        }

        .btn-warning {
            background-color: #ffc107;
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

        input[type="file"], input[type="email"], input[type="password"], input[type="text"] {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: none;
            border-radius: 5px;
            margin: 10px 0;
            padding: 10px;
        }

        .form-group, .table, .form-inline {
            margin-bottom: 30px;
        }

        /* Media queries para adaptabilidad */
        @media screen and (max-width: 768px) {
            h2, h3 {
                font-size: 1.8em;
            }

            .admin-container {
                padding: 20px;
            }

            .form-inline {
                display: block;
            }

            .form-inline .form-group {
                margin-bottom: 15px;
            }

            .btn-primary, .btn-warning, .btn-danger, .btn-success {
                width: 100%;
                margin-bottom: 10px;
            }

            table thead {
                display: none;
            }

            table tbody tr {
                display: block;
                margin-bottom: 10px;
                border-bottom: 2px solid #fff;
            }

            table tbody tr td {
                display: block;
                text-align: right;
                font-size: 0.9em;
                position: relative;
                padding-left: 50%;
                color: #fff;
            }

            table tbody tr td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                padding-left: 10px;
                font-weight: bold;
                text-align: left;
                color: #fff;
            }
        }

    </style>
</head>
<body>
    <div class="admin-container">
        <h2>Panel de Administración</h2>

        <h3>Buscar Suscriptor</h3>
        <form method="GET" class="form-inline mb-3">
            <input type="text" name="search" class="form-control mr-2" placeholder="Buscar por correo" value="<?= $search ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        <h3>Crear Usuarios</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="new_username">Correo Electrónico</label>
                <input type="email" class="form-control" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Contraseña</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <button type="submit" name="create_user" class="btn btn-success">Crear Usuario</button>
        </form>

        <h3 class="mt-5">Cargar Usuarios desde Excel</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="excel_file">Archivo de Excel (.xlsx)</label>
                <input type="file" class="form-control" id="excel_file" name="excel_file" required>
            </div>
            <button type="submit" name="upload_excel" class="btn btn-primary">Subir Archivo</button>
        </form>

        <h3 class="mt-5">Total de Suscriptores: <?= $total_rows ?></h3>

        <h3 class="mt-5">Gestión de Suscriptores</h3>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Correo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td data-label="ID"><?= $row['id'] ?></td>
                    <td data-label="Correo"><?= $row['username'] ?></td>
                    <td data-label="Acciones">
                        <form method="POST" class="form-inline">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <div class="form-group mr-2">
                                <input type="text" class="form-control" name="edit_username" value="<?= $row['username'] ?>" required>
                            </div>
                            <div class="form-group mr-2">
                                <input type="password" class="form-control" name="edit_password" placeholder="Nueva Contraseña (opcional)">
                            </div>
                            <button type="submit" name="edit_user" class="btn btn-warning mr-2">Editar</button>
                            <button type="submit" name="delete_user" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search ?>">Anterior</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a></li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search ?>">Siguiente</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
