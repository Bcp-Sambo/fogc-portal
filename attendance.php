<?php
// attendance.php - The "Smart Form"
session_start();
// Mock session check
// if (!isset($_SESSION['role'])) header('Location: index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Usher Check-in - FOGC Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-md);
            background-color: var(--color-bg-secondary);
            border-bottom: 1px solid var(--border-color);
            margin-bottom: var(--space-lg);
        }
        
        .event-select-container {
            flex-grow: 1;
            margin-right: var(--space-md);
        }
        
        .badge-new {
            background-color: var(--color-role-gold);
            color: black;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            vertical-align: middle;
            margin-left: 8px;
            display: none; /* Toggled via JS */
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="event-select-container">
            <select id="eventSelector" class="form-control" style="padding: 10px; font-size: 0.9rem;">
                <!-- Populated via JS or PHP -->
                <option value="1">Sunday Service - Dec 27</option>
                <option value="2">Bible Study - Dec 30</option>
            </select>
        </div>
        <button id="logoutBtn" class="btn btn-secondary" style="width: auto; padding: 8px 16px; font-size: 0.8rem;">Logout</button>
    </div>

    <div class="container">
        <!-- Main Smart Form -->
        <div class="card" id="checkinCard">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="color: var(--color-role-gold);">Smart Check-in</h2>
                <p style="color: var(--color-text-secondary); font-size: 0.9rem;">Enter phone to lookup</p>
            </div>

            <form id="attendanceForm" autocomplete="off">
                
                <div class="form-group">
                    <label class="form-label">Phone Number (Lookup)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" value="+234" readonly style="width: 70px; text-align: center; padding: 16px 8px;" class="form-control">
                        <input type="tel" id="phoneInput" name="phone_number" class="form-control" placeholder="812 345 6789" maxlength="11" autofocus required style="letter-spacing: 2px; font-weight: bold; font-size: 1.3rem;">
                    </div>
                    <small id="lookupStatus" style="display: block; margin-top: 6px; min-height: 20px; font-size: 0.85rem; color: var(--color-role-gold);"></small>
                </div>

                <!-- Hidden fields until lookup needs them -->
                <div id="memberDetails" style="opacity: 0.5; pointer-events: none; transition: all 0.3s ease;">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="badge-new" id="newBadge">NEW</span></label>
                        <input type="text" id="firstName" name="first_name" class="form-control" placeholder="First Name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" id="lastName" name="last_name" class="form-control" placeholder="Last Name">
                    </div>

                    <div class="form-group" style="display: flex; gap: 16px;">
                        <div style="flex: 1;">
                            <label class="form-label">Sex</label>
                            <select id="sexInput" name="sex" class="form-control">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                             <label class="form-label">Member?</label>
                             <select id="isMember" name="is_member" class="form-control">
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                             </select>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; gap: 16px;">
                        <div style="flex: 1;">
                             <label class="form-label">Email (Optional)</label>
                             <input type="email" id="emailInput" name="email" class="form-control" placeholder="name@example.com">
                        </div>
                        <div style="flex: 1;">
                             <label class="form-label">Invited By</label>
                             <input type="text" id="invitedBy" name="invited_by" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 32px;">
                    <button type="submit" id="mainActionBtn" class="btn btn-primary" disabled>
                        <span>Start Typing...</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="assets/js/attendance.js"></script>
</body>
</html>
