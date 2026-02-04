const API_URL = 'api.php';

document.addEventListener('DOMContentLoaded', () => {
    switchView('dashboard');
    setupEventListeners();
});

function setupEventListeners() {
    document.querySelectorAll('.modal-overlay').forEach(o => {
        o.addEventListener('click', e => { if (e.target === o) closeModal(o.id) });
    });

    document.getElementById('appointment-form').addEventListener('submit', e => {
        e.preventDefault();
        saveAppointment();
    });

    document.getElementById('patient-form').addEventListener('submit', e => {
        e.preventDefault();
        saveEntity('patient', e.target, 'patient-modal-overlay', loadPatients);
    });

    document.getElementById('doctor-form').addEventListener('submit', e => {
        e.preventDefault();
        saveEntity('doctor', e.target, 'doctor-modal-overlay', loadDoctors);
    });
}

function switchView(view) {
    document.querySelectorAll('.content-view').forEach(e => e.style.display = 'none');
    const targetView = document.getElementById(`view-${view}`);
    if (targetView) targetView.style.display = 'block';

    document.querySelectorAll('.nav-item').forEach(e => e.classList.remove('active'));
    const navItem = document.getElementById(`nav-${view}`);
    if (navItem) navItem.classList.add('active');

    if (view === 'dashboard') loadDashboard();
    else if (view === 'patients') loadPatients();
    else if (view === 'doctors') loadDoctors();
}

async function loadDashboard() {
    try {
        const res = await fetch(`${API_URL}?action=get_dashboard_data`).then(r => r.json());

        document.getElementById('total-appts').textContent = res.appointments ? res.appointments.length : 0;
        document.getElementById('total-patients').textContent = res.patients ? res.patients.length : 0;
        document.getElementById('total-doctors').textContent = res.doctors ? res.doctors.length : 0;

        const tbody = document.getElementById('appt-table-body');
        if (res.appointments) {
            tbody.innerHTML = res.appointments.map(a => `
                <tr class="fade-in">
                    <td>${a.patientName || 'N/A'} <div style="font-size:0.75rem;color:#6b7280">ID: ${a.patient_id}</div></td>
                    <td>${a.doctorName || 'N/A'} <div style="font-size:0.75rem;color:#6b7280">${a.doctorSpec || ''}</div></td>
                    <td>${a.date}</td><td>${a.time}</td>
                    <td><span class="status-badge status-${a.status.toLowerCase()}">${a.status}</span></td>
                    <td>
                        <button class="action-btn edit" onclick="editAppointment(${a.id})" style="background-color: #3b82f6; color: white;">Edit</button>
                        <button class="action-btn delete" onclick="deleteAppt(${a.id})">Del</button>
                    </td>
                </tr>
            `).join('');
        }

        const pSel = document.getElementById('patient-select');
        const dSel = document.getElementById('doctor-select');

        // Populate dropdowns only if empty (or always refresh?)
        // Better to always refresh to catch new patients, but let's be safe.
        if (res.patients) {
            let pHtml = '<option value="">Select Patient</option>';
            res.patients.forEach(p => {
                pHtml += `<option value="${p.patient_id}">${p.fname_surname || p.fname || 'Unknown'}</option>`;
            });
            pSel.innerHTML = pHtml;
        }

        if (res.doctors) {
            let dHtml = '<option value="">Select Doctor</option>';
            res.doctors.forEach(d => {
                dHtml += `<option value="${d.doctor_id}">${d.fname_surname || d.fname || 'Unknown'}</option>`;
            });
            dSel.innerHTML = dHtml;
        }

    } catch (e) { console.error("Error loading dashboard", e); }
}

async function loadPatients() {
    try {
        const res = await fetch(`${API_URL}?action=get_patients`).then(r => r.json());
        if (res.patients) {
            document.getElementById('patient-table-body').innerHTML = res.patients.map(p => `
                <tr class="fade-in">
                    <td>${p.fname_surname || p.fname}</td>
                    <td>${p.phone || p.phone_number}<br><small>${p.email}</small></td>
                    <td>${p.med_history || '-'}</td>
                    <td>${p.emrg_contact || '-'}</td>
                    <td>
                        <button class="action-btn edit" onclick="editPatient(${p.patient_id})" style="background-color: #3b82f6; color: white;">Edit</button>
                        <button class="action-btn delete" onclick="deleteEntity('delete_patient', ${p.patient_id}, loadPatients)">Del</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error(e); }
}

async function loadDoctors() {
    try {
        const res = await fetch(`${API_URL}?action=get_doctors`).then(r => r.json());
        if (res.doctors) {
            document.getElementById('doctor-table-body').innerHTML = res.doctors.map(d => `
                <tr class="fade-in">
                    <td>${d.fname_surname || d.fname}</td>
                    <td>${d.profession}</td>
                    <td>${d.room_no}</td>
                    <td>${d.shift_type}</td>
                    <td>$${d.salary}</td>
                    <td>
                        <button class="action-btn edit" onclick="editDoctor(${d.doctor_id})" style="background-color: #3b82f6; color: white;">Edit</button>
                        <button class="action-btn delete" onclick="deleteEntity('delete_doctor', ${d.doctor_id}, loadDoctors)">Del</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (e) { console.error(e); }
}

function resetForm(form) {
    form.reset();
    if (form.id) form.querySelector('[name="id"]').value = '';
}

async function saveEntity(type, form, modalId, cb) {
    const data = Object.fromEntries(new FormData(form).entries());
    const isUpdate = !!data.id;
    const action = isUpdate ? `update_${type}` : `create_${type}`;

    // For update, we might need to map fields if they differ, but here they should be consistent HTML names
    // However, the Person table combines fname and surname which might be tricky if not handled.
    // The original create code assumes inputs 'fname', 'birth_date', etc. which matches API expectations.

    const res = await fetch(`${API_URL}?action=${action}`, { method: 'POST', body: JSON.stringify(data) }).then(r => r.json());
    if (res.success) {
        closeModal(modalId);
        resetForm(form);
        cb();
    } else {
        alert(res.error || "Error");
    }
}

async function saveAppointment() {
    const form = document.getElementById('appointment-form');
    const data = Object.fromEntries(new FormData(form).entries());
    const isUpdate = !!data.id;
    const action = isUpdate ? 'update_appointment' : 'create_appointment';

    const res = await fetch(`${API_URL}?action=${action}`, { method: 'POST', body: JSON.stringify(data) }).then(r => r.json());
    if (res.success) {
        closeModal('modal-overlay');
        resetForm(form);
        loadDashboard();
    } else {
        alert(res.error || "Error saving appointment");
    }
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

// EDIT FUNCTIONS

async function editAppointment(id) {
    if (!id) return;
    const res = await fetch(`${API_URL}?action=get_appointment&id=${id}`).then(r => r.json());
    if (res.appointment) {
        // Also ensure dashboard is loaded to have dropdowns populated
        // But assuming we are on dashboard it should be ok. 
        // If we want to be safe we would wait for loadDashboard or re-populate dropdowns.
        // The modal is in the DOM, so dropdowns should be there.

        const form = document.getElementById('appointment-form');
        const appt = res.appointment;

        form.querySelector('[name="id"]').value = appt.appoint_id;
        form.querySelector('[name="patient_id"]').value = appt.patient_id;
        form.querySelector('[name="doctor_id"]').value = appt.doctor_id;
        form.querySelector('[name="date"]').value = appt.appoint_date;
        form.querySelector('[name="time"]').value = appt.appoint_time;
        form.querySelector('[name="status"]').value = appt.status;

        document.getElementById('appt-modal-title').innerText = "Edit Appointment";
        document.getElementById('modal-overlay').classList.add('open');
    }
}

async function editPatient(id) {
    if (!id) return;
    const res = await fetch(`${API_URL}?action=get_patient&id=${id}`).then(r => r.json());
    if (res.patient) {
        const form = document.getElementById('patient-form');
        const p = res.patient;

        form.querySelector('[name="id"]').value = p.person_id; // or patient_id, they are same
        form.querySelector('[name="fname"]').value = p.fname_surname;
        form.querySelector('[name="birth_date"]').value = p.birth_date;
        form.querySelector('[name="phone"]').value = p.phone_number;
        form.querySelector('[name="email"]').value = p.email;
        form.querySelector('[name="address"]').value = p.address;
        form.querySelector('[name="med_history"]').value = p.med_history;
        form.querySelector('[name="allergies"]').value = p.allergies;
        form.querySelector('[name="emrg_contact"]').value = p.emrg_contact;

        document.getElementById('patient-modal-title').innerText = "Edit Patient";
        document.getElementById('patient-modal-overlay').classList.add('open');
    }
}

async function editDoctor(id) {
    if (!id) return;
    const res = await fetch(`${API_URL}?action=get_doctor&id=${id}`).then(r => r.json());
    if (res.doctor) {
        const form = document.getElementById('doctor-form');
        const d = res.doctor;

        form.querySelector('[name="id"]').value = d.person_id;
        form.querySelector('[name="fname"]').value = d.fname_surname;
        form.querySelector('[name="birth_date"]').value = d.birth_date;
        form.querySelector('[name="phone"]').value = d.phone_number;
        form.querySelector('[name="email"]').value = d.email;
        form.querySelector('[name="address"]').value = d.address;
        form.querySelector('[name="profession"]').value = d.profession;
        form.querySelector('[name="room_no"]').value = d.room_no;
        form.querySelector('[name="hiring_date"]').value = d.hiring_date;
        form.querySelector('[name="salary"]').value = d.salary;
        form.querySelector('[name="shift_type"]').value = d.shift_type;

        document.getElementById('doctor-modal-title').innerText = "Edit Doctor";
        document.getElementById('doctor-modal-overlay').classList.add('open');
    }
}

async function deleteAppt(id) {
    if (!confirm('Delete?')) return;
    await fetch(`${API_URL}?action=delete_appointment`, { method: 'POST', body: JSON.stringify({ id }) });
    loadDashboard();
}

async function deleteEntity(action, id, cb) {
    if (!confirm('Delete?')) return;
    await fetch(`${API_URL}?action=${action}`, { method: 'POST', body: JSON.stringify({ id }) });
    cb();
}

window.openCreateModal = async () => {
    // Reset form and title
    const form = document.getElementById('appointment-form');
    resetForm(form);
    document.getElementById('appt-modal-title').innerText = "New Appointment";

    await loadDashboard(); // refresh dropdowns
    document.getElementById('modal-overlay').classList.add('open');
};

window.openPatientModal = () => {
    const form = document.getElementById('patient-form');
    resetForm(form);
    document.getElementById('patient-modal-title').innerText = "New Patient";
    document.getElementById('patient-modal-overlay').classList.add('open');
};

window.openDoctorModal = () => {
    const form = document.getElementById('doctor-form');
    resetForm(form);
    document.getElementById('doctor-modal-title').innerText = "New Doctor";
    document.getElementById('doctor-modal-overlay').classList.add('open');
};

window.switchView = switchView;
window.deleteEntity = deleteEntity;
window.deleteAppt = deleteAppt;
window.closeModal = closeModal;
window.editAppointment = editAppointment;
window.editPatient = editPatient;
window.editDoctor = editDoctor;
