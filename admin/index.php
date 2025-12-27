<?php
// admin/index.php
session_start();
// Auth Gate
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FOGC Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Admin Specific Overrides */
        body { background-color: var(--color-bg-primary); }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            background-color: var(--color-bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: var(--space-lg);
        }
        .main-content {
            margin-left: 250px;
            padding: var(--space-xl);
        }
        .nav-item {
            display: block;
            padding: 12px 16px;
            color: var(--color-text-secondary);
            text-decoration: none;
            border-radius: var(--radius-md);
            margin-bottom: 8px;
            transition: all 0.2s;
            cursor: pointer;
        }
        .nav-item:hover, .nav-item.active {
            background-color: var(--color-bg-tertiary);
            color: var(--color-role-gold);
        }
        
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--space-md);
        }
        th, td {
            text-align: left;
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
        }
        th { color: var(--color-text-muted); font-weight: 500; font-size: 0.9rem; }
        tr:hover { background-color: var(--color-bg-tertiary); }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .badge-active { background-color: rgba(46, 204, 113, 0.2); color: #2ecc71; }
        .badge-inactive { background-color: rgba(231, 76, 60, 0.2); color: #e74c3c; }

        /* Sections */
        .section { display: none; }
        .section.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo" style="text-align: left; margin-bottom: 40px;">
            FOGC ADMIN
        </div>

        <div class="nav-item active" data-target="dashboard">Dashboard</div>
        <div class="nav-item" data-target="events">Events Management</div>
        <div class="nav-item" data-target="attendees">Attendees Database</div>
        <div class="nav-item" data-target="users">System Users</div>
        
        <div style="margin-top: auto; padding-top: 40px;">
            <a href="../index.php" class="nav-item">Logout</a>
        </div>
    </nav>

    <main class="main-content">
        
        <!-- DASHBOARD Overview -->
        <div id="dashboard" class="section active">
            <h1 style="color: var(--color-role-gold); margin-bottom: 24px;">Overview</h1>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
                <div class="card">
                    <h3 style="color: var(--color-text-muted);">Total Events</h3>
                    <div style="font-size: 2.5rem; font-weight: bold;">12</div>
                </div>
                <div class="card">
                    <h3 style="color: var(--color-text-muted);">Total Attendees</h3>
                    <div style="font-size: 2.5rem; font-weight: bold;">482</div>
                </div>
                <div class="card">
                    <h3 style="color: var(--color-text-muted);">Last Service</h3>
                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--color-role-gold);">89</div>
                    <small>Checked In</small>
                </div>
            </div>
        </div>

        <!-- EVENTS Management -->
        <div id="events" class="section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1>Events</h1>
                <button class="btn btn-primary" style="width: auto;">+ New Event</button>
            </div>
            
            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sunday Service</td>
                                <td>Dec 27, 2025</td>
                                <td><span class="badge badge-active">Active</span></td>
                                <td>
                                    <button class="btn btn-secondary view-event-btn" style="padding: 4px 12px; height: auto; font-size: 0.8rem; margin-right: 4px;">View</button>
                                    <button class="btn btn-secondary edit-event-btn" style="padding: 4px 12px; height: auto; font-size: 0.8rem;">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Midweek Service</td>
                                <td>Dec 24, 2025</td>
                                <td><span class="badge badge-inactive">Past</span></td>
                                <td>
                                    <button class="btn btn-secondary view-event-btn" style="padding: 4px 12px; height: auto; font-size: 0.8rem; margin-right: 4px;">View</button>
                                    <button class="btn btn-secondary edit-event-btn" style="padding: 4px 12px; height: auto; font-size: 0.8rem;">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- EVENT DETAILS VIEW (Hidden by default) -->
        <div id="event-details" class="section">
             <button class="btn btn-secondary" id="backToEvents" style="width: auto; margin-bottom: 16px;">← Back to Events</button>
             
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1 id="detailEventName">Event Name</h1>
                <span class="badge badge-active" id="detailEventStatus">Active</span>
            </div>

            <!-- Stats Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">Total Present</small>
                    <div style="font-size: 1.8rem; font-weight: bold;">142</div>
                </div>
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">Men</small>
                    <div style="font-size: 1.8rem; font-weight: bold;">60</div>
                </div>
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">Women</small>
                    <div style="font-size: 1.8rem; font-weight: bold;">80</div>
                </div>
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">First Timers</small>
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--color-role-gold);">2</div>
                </div>
            </div>

            <div class="card">
                <h3>Attendance Log</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="eventAttendeesList">
                            <!-- Populated by JS -->
                            <tr>
                                <td>08:45 AM</td>
                                <td>John Doe</td>
                                <td>Male</td>
                                <td>Member</td>
                            </tr>
                            <tr>
                                <td>09:00 AM</td>
                                <td>Jane Smith</td>
                                <td>Female</td>
                                <td><span style="color: var(--color-role-gold);">New</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- EVENT DETAILS VIEW (Hidden by default) -->
        <div id="event-details" class="section">
             <button class="btn btn-secondary" id="backToEvents" style="width: auto; margin-bottom: 16px;">← Back to Events</button>
             
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1 id="detailEventName">Event Name</h1>
                <span class="badge badge-active" id="detailEventStatus">Active</span>
            </div>

            <!-- Stats Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">Total Present</small>
                    <div style="font-size: 1.8rem; font-weight: bold;">142</div>
                </div>
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">Men</small>
                    <div style="font-size: 1.8rem; font-weight: bold;">60</div>
                </div>
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">Women</small>
                    <div style="font-size: 1.8rem; font-weight: bold;">80</div>
                </div>
                <div class="card" style="padding: 16px;">
                    <small style="color: var(--color-text-muted);">First Timers</small>
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--color-role-gold);">2</div>
                </div>
            </div>

            <div class="card">
                <h3>Attendance Log</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="eventAttendeesList">
                            <!-- Populated by JS -->
                            <tr>
                                <td>08:45 AM</td>
                                <td>John Doe</td>
                                <td>Male</td>
                                <td>Member</td>
                            </tr>
                            <tr>
                                <td>09:00 AM</td>
                                <td>Jane Smith</td>
                                <td>Female</td>
                                <td><span style="color: var(--color-role-gold);">New</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ATTENDEES Management -->
        <div id="attendees" class="section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1>Member Directory</h1>
                <div style="display: flex; gap: 12px;">
                    <button class="btn btn-secondary" id="exportBtn" style="width: auto;">Export CSV</button>
                    <button class="btn btn-primary" id="importBtn" style="width: auto;">Import Data</button>
                </div>
            </div>

            <div class="card">
                <input type="text" class="form-control" placeholder="Search members..." style="margin-bottom: 16px;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Phone</th>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Member?</th>
                                <th>Email</th>
                                <th>Invited By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>0812 345 6789</td>
                                <td>Emmanuel Clement</td>
                                <td>Male</td>
                                <td><span class="badge badge-active">Yes</span></td>
                                <td>emmanuel@example.com</td>
                                <td>Pastor</td>
                            </tr>
                            <tr>
                                <td>0700 000 0000</td>
                                <td>Chioma Johnson</td>
                                <td>Female</td>
                                <td><span class="badge badge-inactive">No</span></td>
                                <td>-</td>
                                <td>Sister Rose</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SYSTEM USERS Management -->
        <div id="users" class="section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1>System Users</h1>
                <button class="btn btn-primary" id="newUserBtn" style="width: auto;">+ Add User</button>
            </div>

            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>admin</td>
                                <td><span class="badge" style="background: var(--color-role-gold); color: black;">Super Admin</span></td>
                                <td><button class="btn btn-secondary" disabled style="padding: 4px 12px; height: auto; font-size: 0.8rem; opacity: 0.5;">Locked</button></td>
                            </tr>
                            <tr>
                                <td>usher1</td>
                                <td><span class="badge badge-active">Usher</span></td>
                                <td>
                                    <button class="btn btn-secondary reset-pwd-btn" data-user="usher1" style="padding: 4px 12px; height: auto; font-size: 0.8rem;">Reset Pwd</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- MODALS -->
    
    <!-- Event Modal -->
    <div class="modal-overlay" id="eventModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">New Event</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <form id="eventForm">
                <input type="hidden" id="eventId" name="id">
                <div class="form-group">
                    <label class="form-label">Event Name</label>
                    <input type="text" id="eventName" name="event_name" class="form-control" placeholder="e.g. Sunday Service" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" id="eventDate" name="event_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="eventStatus" name="is_active" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelModal" style="width: auto;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="width: auto;">Save Event</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- User Modal (Add) -->
    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add System User</h3>
                <button class="modal-close" id="closeUserModal">&times;</button>
            </div>
            <form id="userForm">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" id="newUsername" name="username" class="form-control" required placeholder="e.g. usher_main">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select id="newUserRole" name="role" class="form-control">
                        <option value="usher">Usher</option>
                        <option value="admin">Super Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Password</label>
                    <input type="password" id="newUserPassword" name="password" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelUserModal" style="width: auto;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="width: auto;">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div class="modal-overlay" id="pwdModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Reset Password</h3>
                <button class="modal-close" id="closePwdModal">&times;</button>
            </div>
            <form id="pwdForm">
                <input type="hidden" id="pwdTargetUser">
                <div class="form-group">
                    <p style="margin-bottom: 12px; color: var(--color-text-secondary);">Resetting password for: <strong id="resetUserDisplay" style="color: var(--color-role-gold)">...</strong></p>
                    <label class="form-label">New Password</label>
                    <input type="password" id="resetNewPassword" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelPwdModal" style="width: auto;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="width: auto;">Update Password</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Import Modal -->
    <div class="modal-overlay" id="importModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Import Attendees</h3>
                <button class="modal-close" id="closeImportModal">&times;</button>
            </div>
            <form id="importForm">
                <div class="form-group">
                    <p style="margin-bottom: 12px; font-size: 0.9rem; color: var(--color-text-secondary);">
                        Upload a CSV file with the following columns:<br>
                        <strong>Phone, First Name, Last Name, Sex, Member(Yes/No), Email</strong>
                    </p>
                    <label class="form-label">Select CSV File</label>
                    <input type="file" id="importFile" name="csv_file" class="form-control" accept=".csv" required style="padding: 10px;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelImportModal" style="width: auto;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="width: auto;">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="toast" class="toast"></div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
