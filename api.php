<?php
require_once 'db_connect.php';

error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

try {
    switch ($action) {
        case 'get_dashboard_data':
            $stmt = $pdo->prepare("
                SELECT a.appoint_id as id, a.appoint_date as date, a.appoint_time as time, a.status,
                       a.patient_id, p_pat.fname_surname as patientName,
                       a.doctor_id, p_doc.fname_surname as doctorName, d.profession as doctorSpec
                FROM appointment a
                LEFT JOIN person p_pat ON a.patient_id = p_pat.person_id
                LEFT JOIN person p_doc ON a.doctor_id = p_doc.person_id
                LEFT JOIN doctor d ON a.doctor_id = d.doctor_id
                ORDER BY a.appoint_date, a.appoint_time
            ");
            $stmt->execute();
            $appointments = $stmt->fetchAll();

            $stmt = $pdo->prepare("SELECT pat.patient_id, p.fname_surname FROM patient pat JOIN person p ON pat.patient_id = p.person_id");
            $stmt->execute();
            $patients = $stmt->fetchAll();

            $stmt = $pdo->prepare("SELECT doc.doctor_id, p.fname_surname FROM doctor doc JOIN person p ON doc.doctor_id = p.person_id");
            $stmt->execute();
            $doctors = $stmt->fetchAll();

            echo json_encode(['appointments' => $appointments, 'patients' => $patients, 'doctors' => $doctors]);
            break;

        case 'get_patients':
            $stmt = $pdo->prepare("SELECT pat.patient_id, p.fname_surname, p.phone_number, p.email, pat.med_history, pat.emrg_contact FROM patient pat JOIN person p ON pat.patient_id = p.person_id ORDER BY p.fname_surname");
            $stmt->execute();
            echo json_encode(['patients' => $stmt->fetchAll()]);
            break;

        case 'get_doctors':
            $stmt = $pdo->prepare("SELECT doc.doctor_id, p.fname_surname, doc.profession, doc.room_no, emp.shift_type, emp.salary FROM doctor doc JOIN employee emp ON doc.doctor_id = emp.employee_id JOIN person p ON emp.employee_id = p.person_id ORDER BY p.fname_surname");
            $stmt->execute();
            echo json_encode(['doctors' => $stmt->fetchAll()]);
            break;

        // --- NEW GET SINGLE ENTITY CASES ---
        
        case 'get_appointment':
             $id = $_GET['id'];
             $stmt = $pdo->prepare("SELECT * FROM appointment WHERE appoint_id = ?");
             $stmt->execute([$id]);
             echo json_encode(['appointment' => $stmt->fetch()]);
             break;

        case 'get_patient':
            $id = $_GET['id'];
            $stmt = $pdo->prepare("
                SELECT p.*, pat.med_history, pat.allergies, pat.emrg_contact 
                FROM person p 
                JOIN patient pat ON p.person_id = pat.patient_id 
                WHERE pat.patient_id = ?
            ");
            $stmt->execute([$id]);
            echo json_encode(['patient' => $stmt->fetch()]);
            break;

        case 'get_doctor':
            $id = $_GET['id'];
            $stmt = $pdo->prepare("
                SELECT p.*, d.profession, d.room_no, emp.hiring_date, emp.salary, emp.shift_type 
                FROM person p 
                JOIN employee emp ON p.person_id = emp.employee_id 
                JOIN doctor d ON emp.employee_id = d.doctor_id 
                WHERE d.doctor_id = ?
            ");
            $stmt->execute([$id]);
            echo json_encode(['doctor' => $stmt->fetch()]);
            break;

        // --- CREATE CASES ---

        case 'create_appointment':
            $data = getJsonInput();
            $stmt = $pdo->prepare("INSERT INTO appointment (appoint_date, appoint_time, status, patient_id, doctor_id, secretary_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['date'], $data['time'], $data['status'], $data['patient_id'], $data['doctor_id'], 5]);
            echo json_encode(['success' => true]);
            break;

        case 'create_patient':
            $data = getJsonInput();
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO person (fname_surname, birth_date, address, phone_number, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$data['fname'], $data['birth_date'], $data['address'], $data['phone'], $data['email']]);
                $pid = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO patient (patient_id, med_history, allergies, emrg_contact) VALUES (?, ?, ?, ?)");
                $stmt->execute([$pid, $data['med_history'], $data['allergies'], $data['emrg_contact']]);
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) { $pdo->rollBack(); throw $e; }
            break;

        case 'create_doctor':
            $data = getJsonInput();
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO person (fname_surname, birth_date, address, phone_number, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$data['fname'], $data['birth_date'], $data['address'], $data['phone'], $data['email']]);
                $pid = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO employee (employee_id, hiring_date, salary, shift_type) VALUES (?, ?, ?, ?)")->execute([$pid, $data['hiring_date'], $data['salary'], $data['shift_type']]);
                $pdo->prepare("INSERT INTO doctor (doctor_id, profession, room_no) VALUES (?, ?, ?)")->execute([$pid, $data['profession'], $data['room_no']]);
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) { $pdo->rollBack(); throw $e; }
            break;
            
        // --- UPDATE CASES ---
        
        case 'update_appointment':
            $data = getJsonInput();
            // Note: Assuming secretary_id changes are not required or defaulted to 5, keeping original logic simplistic
            $stmt = $pdo->prepare("UPDATE appointment SET appoint_date = ?, appoint_time = ?, status = ?, patient_id = ?, doctor_id = ? WHERE appoint_id = ?");
            $stmt->execute([$data['date'], $data['time'], $data['status'], $data['patient_id'], $data['doctor_id'], $data['id']]);
            echo json_encode(['success' => true]);
            break;
            
        case 'update_patient':
            $data = getJsonInput();
            $pdo->beginTransaction();
            try {
                // Update Person
                $stmt = $pdo->prepare("UPDATE person SET fname_surname = ?, birth_date = ?, address = ?, phone_number = ?, email = ? WHERE person_id = ?");
                $stmt->execute([$data['fname'], $data['birth_date'], $data['address'], $data['phone'], $data['email'], $data['id']]);
                
                // Update Patient
                $stmt = $pdo->prepare("UPDATE patient SET med_history = ?, allergies = ?, emrg_contact = ? WHERE patient_id = ?");
                $stmt->execute([$data['med_history'], $data['allergies'], $data['emrg_contact'], $data['id']]);
                
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) { $pdo->rollBack(); throw $e; }
            break;
            
        case 'update_doctor':
            $data = getJsonInput();
            $pdo->beginTransaction();
            try {
                // Update Person
                $stmt = $pdo->prepare("UPDATE person SET fname_surname = ?, birth_date = ?, address = ?, phone_number = ?, email = ? WHERE person_id = ?");
                $stmt->execute([$data['fname'], $data['birth_date'], $data['address'], $data['phone'], $data['email'], $data['id']]);
                
                // Update Employee
                $stmt = $pdo->prepare("UPDATE employee SET hiring_date = ?, salary = ?, shift_type = ? WHERE employee_id = ?");
                $stmt->execute([$data['hiring_date'], $data['salary'], $data['shift_type'], $data['id']]);
                
                // Update Doctor
                $stmt = $pdo->prepare("UPDATE doctor SET profession = ?, room_no = ? WHERE doctor_id = ?");
                $stmt->execute([$data['profession'], $data['room_no'], $data['id']]);
                
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) { $pdo->rollBack(); throw $e; }
            break;

        // --- DELETE CASES ---

        case 'delete_appointment':
            $data = getJsonInput();
            $stmt = $pdo->prepare("DELETE FROM appointment WHERE appoint_id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_patient':
            $data = getJsonInput();
            $pdo->beginTransaction();
            try {
                // Cascading will handle this usually, but prompt code did it manually
                $pdo->prepare("DELETE FROM appointment WHERE patient_id = ?")->execute([$data['id']]);
                $pdo->prepare("DELETE FROM patient WHERE patient_id = ?")->execute([$data['id']]);
                $pdo->prepare("DELETE FROM person WHERE person_id = ?")->execute([$data['id']]);
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) { $pdo->rollBack(); throw $e; }
            break;

        case 'delete_doctor':
            $data = getJsonInput();
            $pdo->beginTransaction();
            try {
                $pdo->prepare("DELETE FROM appointment WHERE doctor_id = ?")->execute([$data['id']]);
                $pdo->prepare("DELETE FROM doctor WHERE doctor_id = ?")->execute([$data['id']]);
                $pdo->prepare("DELETE FROM employee WHERE employee_id = ?")->execute([$data['id']]);
                $pdo->prepare("DELETE FROM person WHERE person_id = ?")->execute([$data['id']]);
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) { $pdo->rollBack(); throw $e; }
            break;

        default: echo json_encode(['error' => 'Invalid action']); break;
    }
} catch (Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
?>
