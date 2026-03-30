document.addEventListener('DOMContentLoaded', async () => {
    // Validate via server session — cannot be faked via localStorage
    try {
        const res = await fetch(API_URL + 'auth.php?action=check');
        const data = await res.json();
        if (!data.logged_in || data.user.role !== 'admin') {
            window.location.href = 'login.html';
            return;
        }
    } catch (e) {
        window.location.href = 'login.html';
        return;
    }
    await renderAdminDashboard();
});

function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.admin-nav a').forEach(el => el.classList.remove('active'));

    document.getElementById(tabId).classList.add('active');
    event.target.classList.add('active');
}

async function renderAdminDashboard() {
    // Appointments
    const aptTbody = document.querySelector('#appointments-table tbody');
    if (aptTbody) {
        try {
            const resApt = await fetch(API_URL + 'appointments.php');
            const apts = await resApt.json();
            if (apts.length) {
                aptTbody.innerHTML = apts.map((a) => `
                    <tr>
                        <td>${a.appointment_date} <br> <small style="color:var(--text-muted)">${a.appointment_time}</small></td>
                        <td>
                            ${a.customer_name ? `<strong>${a.customer_name}</strong>` : `<span style="color:var(--primary)">${a.guest_name}</span> <br><small style="color:var(--text-muted)">${a.guest_email || 'No email'}</small>`}
                        </td>
                        <td>${a.service_name}</td>
                        <td>${a.barber_name}</td>
                        <td>
                            <select onchange="updateAptStatus(${a.appointment_id}, this.value)" style="background: transparent; color: white; border: 1px solid var(--border); padding: 4px;">
                                <option value="Pending" ${a.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                <option value="Approved" ${a.status === 'Approved' ? 'selected' : ''}>Approved</option>
                                <option value="Completed" ${a.status === 'Completed' ? 'selected' : ''}>Completed</option>
                                <option value="Cancelled" ${a.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-outline btn-sm" onclick="deleteApt(${a.appointment_id})">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                aptTbody.innerHTML = '<tr><td colspan="6" style="text-align:center">No appointments found.</td></tr>';
            }
        } catch (e) { console.error(e); }
    }

    // Services
    const serviceList = document.getElementById('admin-services-list');
    if (serviceList) {
        const services = await getServices();
        serviceList.innerHTML = services.map(s => `
            <div class="card" style="padding: 1rem; position: relative;">
                <button onclick="deleteService(${s.service_id})" style="position:absolute; top:10px; right:10px; background:transparent; border:none; color:var(--danger); cursor:pointer;">✖</button>
                <h3 style="margin-bottom: 0.5rem; padding-right:20px;">${s.service_name}</h3>
                <p style="color: var(--primary);">₱${s.price} - ${s.duration}</p>
            </div>
        `).join('');
    }

    // Barbers
    const barberList = document.getElementById('admin-barbers-list');
    if (barberList) {
        const barbers = await getBarbers();
        barberList.innerHTML = barbers.map(b => `
            <div class="card" style="padding: 1rem; position: relative;">
                <button onclick="deleteBarber(${b.barber_id})" style="position:absolute; top:10px; right:10px; background:transparent; border:none; color:var(--danger); cursor:pointer;">✖</button>
                <h3 style="margin-bottom: 0.5rem; padding-right:20px;">${b.name}</h3>
                <p style="color: var(--text-muted);">${b.specialty}</p>
            </div>
        `).join('');
    }

    // Customers
    const custTbody = document.querySelector('#customers-table tbody');
    if (custTbody) {
        try {
            const resCust = await fetch(API_URL + 'customers.php');
            const customers = await resCust.json();

            if (customers.length) {
                custTbody.innerHTML = customers.map(u => `
                    <tr>
                        <td>${u.user_id}</td>
                        <td>${u.name}</td>
                        <td>${u.email}</td>
                        <td><span class="badge" style="background: ${u.role === 'admin' ? 'rgba(212,175,55,0.2)' : 'rgba(255,255,255,0.1)'}; color: ${u.role === 'admin' ? 'var(--primary)' : 'white'}">${u.role.toUpperCase()}</span></td>
                        <td>
                            ${u.user_id != 1 ? `<button class="btn btn-outline btn-sm" style="border-color: var(--danger); color: var(--danger);" onclick="deleteCustomer(${u.user_id})">Delete</button>` : ''}
                        </td>
                    </tr>
                `).join('');
            } else {
                custTbody.innerHTML = `<tr><td colspan="5" style="text-align:center">No users found.</td></tr>`;
            }
        } catch (e) { console.error(e); }
    }
}

async function updateAptStatus(id, newStatus) {
    try {
        const res = await fetch(API_URL + 'appointments.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ appointment_id: id, status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Status updated to ' + newStatus);
            await renderAdminDashboard(); // Refresh table
        } else {
            showToast('Failed: ' + data.error, true);
        }
    } catch (e) { console.error(e); }
}

async function deleteApt(id) {
    if (confirm('Are you sure you want to delete this appointment?')) {
        try {
            const res = await fetch(API_URL + 'appointments.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ appointment_id: id })
            });
            const data = await res.json();
            if (data.success) {
                await renderAdminDashboard();
            } else {
                showMessage('Failed to delete: ' + data.error);
            }
        } catch (e) { console.error(e); }
    }
}

async function addService(e) {
    e.preventDefault();
    const payload = {
        action: 'create',
        service_name: document.getElementById('s-name').value,
        price: document.getElementById('s-price').value,
        duration: document.getElementById('s-duration').value,
        image: document.getElementById('s-image').value || ''
    };

    try {
        const res = await fetch(API_URL + 'services.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('add-service-form').style.display = 'none';
            e.target.reset();
            await renderAdminDashboard();
            showToast('Service added successfully!');
        } else showToast(data.error || 'Error adding service', true);
    } catch (err) { console.error(err); }
}

async function deleteService(id) {
    if (confirm('Delete this service?')) {
        const payload = { action: 'delete', service_id: id };
        try {
            const res = await fetch(API_URL + 'services.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            await renderAdminDashboard();
        } catch (e) { }
    }
}

// function addBarber is intentionally removed from here. 
// It was re-implemented inside admin-barbers.html to support Passwords and Checklists.

async function deleteBarber(id) {
    if (confirm('Delete this barber?')) {
        const payload = { action: 'delete', barber_id: id };
        try {
            const res = await fetch(API_URL + 'barbers.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            await renderAdminDashboard();
        } catch (e) { }
    }
}

async function deleteCustomer(id) {
    if (!confirm('Are you sure you want to permanently delete this user?')) return;
    try {
        const res = await fetch(API_URL + 'customers.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', user_id: id })
        });
        const data = await res.json();
        if (data.success) {
            showToast("User deleted successfully");
            await renderAdminDashboard();
        } else {
            showToast(data.error || "Failed to delete user", true);
        }
    } catch (e) {
        console.error(e);
        showToast("An error occurred during deletion", true);
    }
}
