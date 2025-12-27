/**
 * attendance.js
 * The core "Smart Logic" for handling lookups and check-ins.
 */

const STATE = {
    IDLE: 'idle',
    LOOKING_UP: 'looking_up',
    FOUND_MEMBER: 'found_member',
    NEW_VISITOR: 'new_visitor',
    SUBMITTING: 'submitting'
};

document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const phoneInput = document.getElementById('phoneInput');
    const memberDetails = document.getElementById('memberDetails');
    const firstName = document.getElementById('firstName');
    const lastName = document.getElementById('lastName');
    const sexInput = document.getElementById('sexInput');
    const isMember = document.getElementById('isMember');
    const emailInput = document.getElementById('emailInput');
    const invitedBy = document.getElementById('invitedBy');
    const mainActionBtn = document.getElementById('mainActionBtn');
    const lookupStatus = document.getElementById('lookupStatus');
    const newBadge = document.getElementById('newBadge');
    const form = document.getElementById('attendanceForm');

    let currentState = STATE.IDLE;
    // Debounce timer
    let typeTimer;
    const DONE_TYPING_INTERVAL = 500; // ms

    // --- 1. Load Events (Dynamic) ---
    function loadEvents() {
        const selector = document.getElementById('eventSelector');
        // Fetch from API
        fetch('api/events.php')
            .then(res => res.json())
            .then(data => {
                selector.innerHTML = '';
                const activeEvents = data.filter(e => e.is_active == 1);

                if (activeEvents.length === 0) {
                    const opt = document.createElement('option');
                    opt.text = "No Active Events";
                    selector.appendChild(opt);
                    return;
                }

                activeEvents.forEach(event => {
                    const opt = document.createElement('option');
                    opt.value = event.id;
                    opt.text = `${event.event_name} - ${event.event_date}`;
                    selector.appendChild(opt);
                });
            })
            .catch(err => console.error("Failed to load events", err));
    }
    loadEvents();


    // --- 2. Phone Input Logic ---
    phoneInput.addEventListener('input', (e) => {
        // Enforce numeric only
        e.target.value = e.target.value.replace(/[^0-9]/g, '');

        clearTimeout(typeTimer);
        const val = e.target.value;

        if (val.length < 10) {
            resetFormState(false); // Reset but keep phone
            updateButtonState('Start Typing...', true);
            return;
        }

        // Trigger lookup automatically at 10 or 11 digits
        updateButtonState('Looking up...', true);
        lookupStatus.textContent = 'Searching database...';

        typeTimer = setTimeout(() => {
            performLookup(val);
        }, DONE_TYPING_INTERVAL);
    });

    // --- 3. The Real API Lookup ---
    function performLookup(phone) {
        currentState = STATE.LOOKING_UP;

        fetch(`api/lookup.php?phone=${phone}`)
            .then(res => res.json())
            .then(data => {
                if (data.found) {
                    handleReturningMember(data.data);
                } else {
                    handleNewVisitor();
                }
            })
            .catch(err => {
                console.error(err);
                lookupStatus.textContent = 'Error connecting to DB';
                lookupStatus.style.color = 'red';
            });
    }

    // --- 4. State Handlers ---

    function handleReturningMember(data) {
        currentState = STATE.FOUND_MEMBER;

        // Populate
        firstName.value = data.first_name;
        lastName.value = data.last_name || '';
        sexInput.value = data.sex;
        isMember.value = data.is_member;
        emailInput.value = data.email || '';
        invitedBy.value = data.invited_by || '';

        // Lock Fields
        setFieldsReadOnly(true);
        memberDetails.style.opacity = '1';
        memberDetails.style.pointerEvents = 'none'; // Lock clicks
        newBadge.style.display = 'none';

        lookupStatus.textContent = '✓ Member Found';
        lookupStatus.style.color = 'var(--color-status-success)';

        updateButtonState('Mark Present', false, 'btn-primary');
    }

    function handleNewVisitor() {
        currentState = STATE.NEW_VISITOR;

        // Clear & Unlock
        firstName.value = '';
        lastName.value = '';
        invitedBy.value = '';
        emailInput.value = '';
        isMember.value = 'No'; // Default for new visitor

        setFieldsReadOnly(false);
        memberDetails.style.opacity = '1';
        memberDetails.style.pointerEvents = 'auto'; // Enable editing
        newBadge.style.display = 'inline-block';
        firstName.focus(); // Jump to name

        lookupStatus.textContent = '* New Visitor';
        lookupStatus.style.color = 'var(--color-role-gold)';

        updateButtonState('Add & Mark Present', false, 'btn-primary');
    }

    // --- 5. Utilities ---
    function resetFormState(clearPhone = false) {
        currentState = STATE.IDLE;
        memberDetails.style.opacity = '0.5';
        memberDetails.style.pointerEvents = 'none';

        if (clearPhone) {
            phoneInput.value = '';
            phoneInput.focus();
        }

        firstName.value = '';
        lastName.value = '';
        emailInput.value = '';
        isMember.value = 'Yes';
        invitedBy.value = '';
        lookupStatus.textContent = '';
        newBadge.style.display = 'none';

        updateButtonState('Start Typing...', true);
    }

    function setFieldsReadOnly(isReadOnly) {
        const fields = [firstName, lastName, sexInput, isMember, emailInput, invitedBy];
        fields.forEach(f => {
            if (isReadOnly) f.setAttribute('readonly', 'true');
            else f.removeAttribute('readonly');
        });

        if (isReadOnly) {
            sexInput.style.pointerEvents = 'none';
            isMember.style.pointerEvents = 'none';
        } else {
            sexInput.style.pointerEvents = 'auto';
            isMember.style.pointerEvents = 'auto';
        }
    }

    function updateButtonState(text, disabled, variant = 'btn-primary') {
        const span = mainActionBtn.querySelector('span');
        if (span) span.textContent = text;
        else mainActionBtn.textContent = text; // Fallback

        mainActionBtn.disabled = disabled;
    }

    function showToast(msg, type) {
        const t = document.getElementById('toast');
        t.innerText = msg;
        t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    // --- 6. Submission ---
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const eventId = document.getElementById('eventSelector').value;
        if (!eventId) {
            showToast('No active event selected!', 'error');
            return;
        }

        // Animation
        updateButtonState('Saving...', true);

        const formData = new FormData(form);
        formData.append('event_id', eventId);

        // Handle readonly fields not submitting if disabled? 
        // Readonly fields ARE submitted. Disabled fields are NOT.
        // My utility uses setAttribute('readonly'), so they submit fine! 
        // BUT select elements with pointer-events:none are still interactive via keyboard?
        // Actually for Select, 'readonly' attribute doesn't work standardly. 
        // However, I'm just reading current values into FormData, so it's fine.

        // Wait, FormData captures current values regardless of readonly. 

        fetch('api/checkin.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, data.type || 'success'); // support warning type
                    resetFormState(true);
                } else {
                    showToast(data.message, 'error');
                    updateButtonState('Retry', false);
                }
            })
            .catch(err => {
                console.error(err);
                showToast('System Error', 'error');
                updateButtonState('Retry', false);
            });
    });

    // Logout
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            window.location.href = 'index.php';
        });
    }
});
