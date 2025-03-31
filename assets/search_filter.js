function toggleDropdown() {
    const dropdown = document.getElementById('dropdown');
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('studentsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const sortSelect = document.getElementById('sortSelect');
    const searchInput = document.getElementById('searchInput');

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdown');
        const sortIcon = document.querySelector('.dropdown img');

        if (event.target !== sortIcon && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    sortSelect.addEventListener('change', function() {
        const column = this.value;
        if (!column) return;

        const columnIndex = {
            id: 0,
            student_id: 1,
            name: 2
        } [column];

        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();
            return aValue.localeCompare(bValue, undefined, {
                numeric: true
            });
        });

        rows.forEach(row => tbody.appendChild(row));

        document.getElementById('dropdown').style.display = 'none';
    });

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});