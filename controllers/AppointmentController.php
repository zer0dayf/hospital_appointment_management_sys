<?php
require_once 'BaseController.php';

class AppointmentController extends BaseController
{

    public function getAll()
    {
        $stmt = $this->pdo->prepare("
            SELECT a.appoint_id as id, a.appoint_date as date, a.appoint_time as time, a.status,
                   a.patient_id, p_pat.fname_surname as patient_name,
                   a.doctor_id, p_doc.fname_surname as doctor_name, d.profession as doctor_spec
            FROM appointment a
            LEFT JOIN person p_pat ON a.patient_id = p_pat.person_id
            LEFT JOIN person p_doc ON a.doctor_id = p_doc.person_id
            LEFT JOIN doctor d ON a.doctor_id = d.doctor_id
            ORDER BY a.appoint_date, a.appoint_time
        ");
        $stmt->execute();

        // Dashboard also needs list of patients and doctors for the dropdowns
        $stmtPat = $this->pdo->prepare("SELECT pat.patient_id, p.fname_surname FROM patient pat JOIN person p ON pat.patient_id = p.person_id");
        $stmtPat->execute();

        $stmtDoc = $this->pdo->prepare("SELECT doc.doctor_id, p.fname_surname FROM doctor doc JOIN person p ON doc.doctor_id = p.person_id");
        $stmtDoc->execute();

        $this->jsonResponse([
            'appointments' => $stmt->fetchAll(),
            'patients' => $stmtPat->fetchAll(),
            'doctors' => $stmtDoc->fetchAll()
        ]);
    }

    public function getOne($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM appointment WHERE appoint_id = ?");
        $stmt->execute([$id]);
        $appointment = $stmt->fetch();
        if (!$appointment) {
            $this->jsonResponse(['error' => 'Appointment not found'], 404);
        }
        $this->jsonResponse(['appointment' => $appointment]);
    }

    public function create()
    {
        $data = $this->sanitize($this->getJsonInput());
        $this->validateRequired($data, ['patient_id', 'doctor_id', 'date', 'time']);

        $stmt = $this->pdo->prepare("INSERT INTO appointment (appoint_date, appoint_time, status, patient_id, doctor_id, secretary_id) VALUES (?, ?, ?, ?, ?, ?)");
        // Secretary ID 5 as a default from original code
        $stmt->execute([$data['date'], $data['time'], $data['status'] ?? 'Scheduled', $data['patient_id'], $data['doctor_id'], 5]);

        $this->jsonResponse(['success' => true, 'id' => $this->pdo->lastInsertId('appointment_appoint_id_seq')], 201);
    }

    public function update($id)
    {
        $data = $this->sanitize($this->getJsonInput());
        $this->validateRequired($data, ['patient_id', 'doctor_id', 'date', 'time']);

        $stmt = $this->pdo->prepare("UPDATE appointment SET appoint_date = ?, appoint_time = ?, status = ?, patient_id = ?, doctor_id = ? WHERE appoint_id = ?");
        $stmt->execute([$data['date'], $data['time'], $data['status'], $data['patient_id'], $data['doctor_id'], $id]);

        $this->jsonResponse(['success' => true]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM appointment WHERE appoint_id = ?");
        $stmt->execute([$id]);
        $this->jsonResponse(['success' => true]);
    }
}
