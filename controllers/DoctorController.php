<?php
require_once 'BaseController.php';

class DoctorController extends BaseController
{

    public function getAll()
    {
        $stmt = $this->pdo->prepare("
            SELECT doc.doctor_id, p.fname_surname, doc.profession, doc.room_no, emp.shift_type, emp.salary 
            FROM doctor doc 
            JOIN employee emp ON doc.doctor_id = emp.employee_id 
            JOIN person p ON emp.employee_id = p.person_id 
            ORDER BY p.fname_surname
        ");
        $stmt->execute();
        $this->jsonResponse(['doctors' => $stmt->fetchAll()]);
    }

    public function getOne($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, d.profession, d.room_no, emp.hiring_date, emp.salary, emp.shift_type 
            FROM person p 
            JOIN employee emp ON p.person_id = emp.employee_id 
            JOIN doctor d ON emp.employee_id = d.doctor_id 
            WHERE d.doctor_id = ?
        ");
        $stmt->execute([$id]);
        $doctor = $stmt->fetch();
        if (!$doctor) {
            $this->jsonResponse(['error' => 'Doctor not found'], 404);
        }
        $this->jsonResponse(['doctor' => $doctor]);
    }

    public function create()
    {
        $data = $this->sanitize($this->getJsonInput());
        $this->validateRequired($data, ['fname', 'birth_date', 'profession', 'salary', 'shift_type']);
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

            $this->pdo->prepare("INSERT INTO employee (employee_id, hiring_date, salary, shift_type) VALUES (?, ?, ?, ?)")
                ->execute([$pid, $data['hiring_date'] ?? date('Y-m-d'), $data['salary'], $data['shift_type']]);

            $this->pdo->prepare("INSERT INTO doctor (doctor_id, profession, room_no) VALUES (?, ?, ?)")
                ->execute([$pid, $data['profession'], $data['room_no'] ?? null]);

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
        $this->validateRequired($data, ['fname', 'birth_date', 'profession', 'salary', 'shift_type']);
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

            $stmt = $this->pdo->prepare("UPDATE employee SET hiring_date = ?, salary = ?, shift_type = ? WHERE employee_id = ?");
            $stmt->execute([
                $data['hiring_date'] ?? date('Y-m-d'),
                $data['salary'],
                $data['shift_type'],
                $id
            ]);

            $stmt = $this->pdo->prepare("UPDATE doctor SET profession = ?, room_no = ? WHERE doctor_id = ?");
            $stmt->execute([
                $data['profession'],
                $data['room_no'] ?? null,
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
            $this->pdo->prepare("DELETE FROM appointment WHERE doctor_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM doctor WHERE doctor_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM employee WHERE employee_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM person WHERE person_id = ?")->execute([$id]);

            $this->pdo->commit();
            $this->jsonResponse(['success' => true]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
