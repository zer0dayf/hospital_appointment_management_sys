<?php
require_once 'BaseController.php';

class PatientController extends BaseController
{

    public function getAll()
    {
        $stmt = $this->pdo->prepare("
            SELECT pat.patient_id, p.fname_surname, p.phone_number, p.email, pat.med_history, pat.emrg_contact 
            FROM patient pat 
            JOIN person p ON pat.patient_id = p.person_id 
            ORDER BY p.fname_surname
        ");
        $stmt->execute();
        $this->jsonResponse(['patients' => $stmt->fetchAll()]);
    }

    public function getOne($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, pat.med_history, pat.allergies, pat.emrg_contact 
            FROM person p 
            JOIN patient pat ON p.person_id = pat.patient_id 
            WHERE pat.patient_id = ?
        ");
        $stmt->execute([$id]);
        $patient = $stmt->fetch();
        if (!$patient) {
            $this->jsonResponse(['error' => 'Patient not found'], 404);
        }
        $this->jsonResponse(['patient' => $patient]);
    }

    public function create()
    {
        $data = $this->sanitize($this->getJsonInput());
        $this->validateRequired($data, ['fname', 'birth_date']);
        $this->validateEmail($data['email'] ?? '');
        $this->validatePhone($data['phone'] ?? '');

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO person (fname_surname, birth_date, address, phone_number, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['fname'],
                $data['birth_date'],
                $data['address'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null
            ]);
            $pid = $this->pdo->lastInsertId('person_person_id_seq');

            $stmt = $this->pdo->prepare("INSERT INTO patient (patient_id, med_history, allergies, emrg_contact) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $pid,
                $data['med_history'] ?? null,
                $data['allergies'] ?? null,
                $data['emrg_contact'] ?? null
            ]);

            $this->pdo->commit();
            $this->jsonResponse(['success' => true, 'id' => $pid], 201);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function update($id)
    {
        $data = $this->sanitize($this->getJsonInput());
        $this->validateRequired($data, ['fname', 'birth_date']);
        $this->validateEmail($data['email'] ?? '');
        $this->validatePhone($data['phone'] ?? '');

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE person SET fname_surname = ?, birth_date = ?, address = ?, phone_number = ?, email = ? WHERE person_id = ?");
            $stmt->execute([
                $data['fname'],
                $data['birth_date'],
                $data['address'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $id
            ]);

            $stmt = $this->pdo->prepare("UPDATE patient SET med_history = ?, allergies = ?, emrg_contact = ? WHERE patient_id = ?");
            $stmt->execute([
                $data['med_history'] ?? null,
                $data['allergies'] ?? null,
                $data['emrg_contact'] ?? null,
                $id
            ]);

            $this->pdo->commit();
            $this->jsonResponse(['success' => true]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $this->pdo->beginTransaction();
        try {
            // Note: In a real system, we might want soft deletes.
            // Database constraints (ON DELETE CASCADE) should handle this, but let's be explicit if needed.
            // The original code handled it manually.
            $this->pdo->prepare("DELETE FROM appointment WHERE patient_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM patient WHERE patient_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM person WHERE person_id = ?")->execute([$id]);

            $this->pdo->commit();
            $this->jsonResponse(['success' => true]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
