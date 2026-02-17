<?php
require_once 'db_connect.php';
require_once 'controllers/PatientController.php';
require_once 'controllers/DoctorController.php';
require_once 'controllers/AppointmentController.php';

error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Basic Routing Logic
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        // Appointments
        case 'get_dashboard_data':
            (new AppointmentController($pdo))->getAll();
            break;
        case 'get_appointment':
            if (!$id)
                throw new Exception("ID required", 400);
            (new AppointmentController($pdo))->getOne($id);
            break;
        case 'create_appointment':
            (new AppointmentController($pdo))->create();
            break;
        case 'update_appointment':
            $input = json_decode(file_get_contents('php://input'), true);
            (new AppointmentController($pdo))->update($input['id'] ?? $id);
            break;
        case 'delete_appointment':
            $input = json_decode(file_get_contents('php://input'), true);
            (new AppointmentController($pdo))->delete($input['id'] ?? $id);
            break;

        // Patients
        case 'get_patients':
            (new PatientController($pdo))->getAll();
            break;
        case 'get_patient':
            if (!$id)
                throw new Exception("ID required", 400);
            (new PatientController($pdo))->getOne($id);
            break;
        case 'create_patient':
            (new PatientController($pdo))->create();
            break;
        case 'update_patient':
            $input = json_decode(file_get_contents('php://input'), true);
            (new PatientController($pdo))->update($input['id'] ?? $id);
            break;
        case 'delete_patient':
            $input = json_decode(file_get_contents('php://input'), true);
            (new PatientController($pdo))->delete($input['id'] ?? $id);
            break;

        // Doctors
        case 'get_doctors':
            (new DoctorController($pdo))->getAll();
            break;
        case 'get_doctor':
            if (!$id)
                throw new Exception("ID required", 400);
            (new DoctorController($pdo))->getOne($id);
            break;
        case 'create_doctor':
            (new DoctorController($pdo))->create();
            break;
        case 'update_doctor':
            $input = json_decode(file_get_contents('php://input'), true);
            (new DoctorController($pdo))->update($input['id'] ?? $id);
            break;
        case 'delete_doctor':
            $input = json_decode(file_get_contents('php://input'), true);
            (new DoctorController($pdo))->delete($input['id'] ?? $id);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
