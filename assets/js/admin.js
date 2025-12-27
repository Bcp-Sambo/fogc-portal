/**
 * admin.js
 * Dashboard interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- Tabs Logic ---
    const navItems = document.querySelectorAll('.nav-item[data-target]');
    const sections = document.querySelectorAll('.section');

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            // Remove active from all
            navItems.forEach(nav => nav.classList.remove('active'));
            sections.forEach(sec => sec.classList.remove('active'));

            // Add active to current
            item.classList.add('active');
            const targetId = item.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
        });
    });

    // --- Modal Logic ---
    const eventModal = document.getElementById('eventModal');
    const newEventBtn = document.querySelector('button.btn-primary'); // Assumes first primary button in events is "New Event" - risky, better use ID
    // Better Selector for New Event:
    const createEventBtn = document.querySelector('#events .btn-primary');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelModalBtn = document.getElementById('cancelModal');
    const eventForm = document.getElementById('eventForm');

    // Modal Elements
    const modalTitle = document.getElementById('modalTitle');
    const eventIdInput = document.getElementById('eventId');
    const eventNameInput = document.getElementById('eventName');
    const eventDateInput = document.getElementById('eventDate');
    const eventStatusInput = document.getElementById('eventStatus');

    // Open "New Event"
    createEventBtn.addEventListener('click', () => {
        openModal();
    });

    [closeModalBtn, cancelModalBtn].forEach(btn => {
        btn.addEventListener('click', () => {
            eventModal.classList.remove('active');
        });
    });

    // Helper: Escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // --- Event API Logic ---
    function loadEvents() {
        fetch('../api/events.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#events table tbody');
                tbody.innerHTML = '';

                data.forEach(event => {
                    const statusBadge = event.is_active == 1
                        ? '<span class="badge badge-active">Active</span>'
                        : '<span class="badge badge-inactive">Inactive</span>';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(event.event_name)}</td>
                        <td>${escapeHtml(event.event_date)}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-secondary view-event-btn" data-id="${escapeHtml(event.id)}" style="padding: 4px 12px; height: auto; font-size: 0.8rem; margin-right: 4px;">View</button>
                            <button class="btn btn-secondary edit-event-btn" data-id="${escapeHtml(event.id)}" data-active="${escapeHtml(event.is_active)}" style="padding: 4px 12px; height: auto; font-size: 0.8rem;">Edit</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    // --- Attendees API Logic ---
    function loadAttendees() {
        fetch('../api/attendees.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#attendees table tbody');
                tbody.innerHTML = ''; // Clear mock

                data.forEach(a => {
                    const memberBadge = a.is_member === 'Yes'
                        ? '<span class="badge badge-active">Yes</span>'
                        : '<span class="badge badge-inactive">No</span>';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(a.phone_number)}</td>
                        <td>${escapeHtml(a.first_name)} ${escapeHtml(a.last_name || '')}</td>
                        <td>${escapeHtml(a.sex)}</td>
                        <td>${memberBadge}</td>
                        <td>${escapeHtml(a.email || '-')}</td>
                        <td>${escapeHtml(a.invited_by || '-')}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    // --- System Users API Logic ---
    function loadUsers() {
        fetch('../api/users.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#users table tbody');
                tbody.innerHTML = '';

                data.forEach(u => {
                    let actions = '';
                    if (u.username === 'admin') {
                        actions = '<button class="btn btn-secondary" disabled style="padding: 4px 12px; height: auto; font-size: 0.8rem; opacity: 0.5;">Locked</button>';
                    } else {
                        // username in data-user attribute needs to be safe
                        actions = `<button class="btn btn-secondary reset-pwd-btn" data-user="${escapeHtml(u.username)}" style="padding: 4px 12px; height: auto; font-size: 0.8rem;">Reset Pwd</button>`;
                    }

                    const badge = u.role === 'admin'
                        ? '<span class="badge" style="background: var(--color-role-gold); color: black;">Super Admin</span>'
                        : '<span class="badge badge-active">Usher</span>';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(u.username)}</td>
                        <td>${badge}</td>
                        <td>${actions}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    // --- Dashboard API Logic ---
    function loadDashboardStats() {
        const dashboardSection = document.getElementById('dashboard');
        // Selectors based on card order in HTML
        // Card 1: Total Events, Card 2: Total Attendees, Card 3: Last Service
        const cards = dashboardSection.querySelectorAll('.card div[style*="font-size: 2.5rem"]');

        if (cards.length < 3) return;

        fetch('../api/dashboard.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cards[0].textContent = data.stats.total_events; // Numbers are generally safe, but textContent is best practice
                    cards[1].textContent = data.stats.total_attendees;
                    cards[2].textContent = data.stats.last_service_checkins;
                }
            })
            .catch(err => console.error('Failed to load dashboard stats', err));
    }

    // Load on startup
    loadEvents();
    loadAttendees();
    loadUsers();
    loadDashboardStats();

    // Handle Edit Buttons (Event delegation)
    const eventsTable = document.querySelector('#events table tbody');
    eventsTable.addEventListener('click', (e) => {
        const btn = e.target;

        // Handle EDIT
        if (btn.classList.contains('edit-event-btn')) {
            const row = btn.closest('tr');
            const name = row.children[0].innerText;
            const date = row.children[1].innerText;
            const id = btn.getAttribute('data-id');
            const isActive = btn.getAttribute('data-active') == 1 ? 'Active' : 'Inactive';

            openModal('Edit Event', {
                id: id,
                name: name,
                date: date,
                status: isActive
            });
        }

        // Handle VIEW (Details)
        if (btn.classList.contains('view-event-btn')) {
            const row = btn.closest('tr');
            const name = row.children[0].innerText;
            // id would be needed for fetch details
            const id = btn.getAttribute('data-id');
            showEventDetails(name, id);
        }
    });

    const eventsSection = document.getElementById('events');
    const eventDetailsSection = document.getElementById('event-details');
    const backToEventsBtn = document.getElementById('backToEvents');
    const detailEventName = document.getElementById('detailEventName');

    if (backToEventsBtn) {
        backToEventsBtn.addEventListener('click', () => {
            eventDetailsSection.classList.remove('active');
            eventsSection.classList.add('active');
        });
    }

    function showEventDetails(eventName, eventId) {
        // Toggle Sections
        // We iterate sections to close all, then open details
        sections.forEach(sec => sec.classList.remove('active'));
        eventDetailsSection.classList.add('active');

        detailEventName.textContent = eventName; // textContent handles escaping automatically

        // Fetch Real Stats
        fetch(`../api/attendees.php?event_id=${eventId}`)
            .then(res => res.json())
            .then(data => {
                // Update Stats
                const stats = data.stats;
                // Assuming fixed order of cards: Total, Men, Women, FirstTimers
                const cards = eventDetailsSection.querySelectorAll('.card div[style*="font-size: 1.8rem"]');
                if (cards.length >= 4) {
                    cards[0].textContent = stats.total;
                    cards[1].textContent = stats.men;
                    cards[2].textContent = stats.women;
                    cards[3].textContent = stats.first_timers;
                }

                // Update Table
                const tbody = document.getElementById('eventAttendeesList');
                tbody.innerHTML = '';

                if (data.list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4">No attendees yet.</td></tr>';
                } else {
                    data.list.forEach(a => {
                        // Format Time
                        const time = a.check_in_time.split(' ')[1].substring(0, 5);
                        const status = a.is_member === 'Yes' ? 'Member' : '<span style="color: var(--color-role-gold);">New</span>';

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${escapeHtml(time)}</td>
                            <td>${escapeHtml(a.first_name)} ${escapeHtml(a.last_name || '')}</td>
                            <td>${escapeHtml(a.sex)}</td>
                            <td>${status}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            });
    }

    function openModal(title = 'New Event', data = null) {
        modalTitle.textContent = title;
        if (data) {
            eventIdInput.value = data.id;
            eventNameInput.value = data.name;
            eventDateInput.value = data.date;
            eventStatusInput.value = data.status;
        } else {
            eventForm.reset();
            eventIdInput.value = '';
        }
        eventModal.classList.add('active');
    }

    // Handle Form Submit
    eventForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(eventForm);

        fetch('../api/events.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    eventModal.classList.remove('active');
                    showToast(data.message, 'success');
                    loadEvents(); // Reload table
                } else {
                    showToast(data.message, 'error');
                }
            });
    });

    function showToast(msg, type) {
        const t = document.getElementById('toast');
        t.innerText = msg;
        t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    // --- System Users Logic ---
    const userModal = document.getElementById('userModal');
    const newUserBtn = document.getElementById('newUserBtn');
    const closeUserModal = document.getElementById('closeUserModal');
    const cancelUserModal = document.getElementById('cancelUserModal');
    const userForm = document.getElementById('userForm');

    if (newUserBtn) {
        newUserBtn.addEventListener('click', () => {
            userForm.reset();
            userModal.classList.add('active');
        });
    }

    [closeUserModal, cancelUserModal].forEach(btn => {
        if (btn) btn.addEventListener('click', () => userModal.classList.remove('active'));
    });

    if (userForm) {
        userForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(userForm);
            formData.append('action', 'create');

            fetch('../api/users.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        userModal.classList.remove('active');
                        loadUsers();
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        });
    }

    // Password Reset Logic
    const pwdModal = document.getElementById('pwdModal');
    const closePwdModal = document.getElementById('closePwdModal');
    const cancelPwdModal = document.getElementById('cancelPwdModal');
    const pwdForm = document.getElementById('pwdForm');
    const pwdTargetUser = document.getElementById('pwdTargetUser');
    const resetUserDisplay = document.getElementById('resetUserDisplay');

    // Event delegation for "Reset Pwd" buttons
    const usersTable = document.querySelector('#users table tbody');
    if (usersTable) {
        usersTable.addEventListener('click', (e) => {
            if (e.target.classList.contains('reset-pwd-btn')) {
                const username = e.target.getAttribute('data-user');
                pwdTargetUser.value = username;
                resetUserDisplay.textContent = username;
                document.getElementById('resetNewPassword').value = '';
                pwdModal.classList.add('active');
            }
        });
    }

    [closePwdModal, cancelPwdModal].forEach(btn => {
        if (btn) btn.addEventListener('click', () => pwdModal.classList.remove('active'));
    });

    if (pwdForm) {
        pwdForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData();
            formData.append('action', 'reset_password');
            formData.append('username', pwdTargetUser.value);
            formData.append('password', document.getElementById('resetNewPassword').value);

            fetch('../api/users.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        pwdModal.classList.remove('active');
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        });
    }

    // --- Import / Export Logic ---
    const importBtn = document.getElementById('importBtn');
    const exportBtn = document.getElementById('exportBtn');
    const importModal = document.getElementById('importModal');
    const closeImport = document.getElementById('closeImportModal');
    const cancelImport = document.getElementById('cancelImportModal');
    const importForm = document.getElementById('importForm');

    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            // Direct download
            window.location.href = '../api/export_attendees.php';
        });
    }

    if (importBtn) {
        importBtn.addEventListener('click', (e) => {
            e.preventDefault();
            importForm.reset();
            importModal.classList.add('active');
        });
    }

    [closeImport, cancelImport].forEach(btn => {
        if (btn) btn.addEventListener('click', () => importModal.classList.remove('active'));
    });

    if (importForm) {
        importForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(importForm);

            // Show loading state
            const submitBtn = importForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Uploading...';
            submitBtn.disabled = true;

            fetch('../api/import_attendees.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;

                    if (data.success) {
                        showToast(data.message, 'success');
                        importModal.classList.remove('active');
                        loadAttendees(); // Refresh list
                        loadDashboardStats(); // Refresh stats
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(err => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    showToast('Upload failed: ' + err.message, 'error');
                });
        });
    }
});
