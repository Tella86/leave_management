document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.approve-btn, .reject-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const leaveId = e.target.dataset.leaveId;
            const action = e.target.dataset.action;

            fetch(`approve_leave.php?leave_id=${leaveId}&action=${action}`)
                .then(response => response.text())
                .then(data => alert(data));
        });
    });
});
