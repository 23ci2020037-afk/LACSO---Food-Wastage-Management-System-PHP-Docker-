// USERS AJAX MANAGEMENT

function loadUsers() {
  fetch('get_users.php')
    .then(res => res.json())
    .then(users => {
      const tbody = document.getElementById('users-table');
      tbody.innerHTML = '';
      users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="py-3 px-6">${user.name}</td>
          <td class="py-3 px-6">${user.email}</td>
          <td class="py-3 px-6">
            <div class="flex space-x-2">
              <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-800">
                <div class="w-4 h-4 flex items-center justify-center"><i class="ri-edit-line"></i></div>
              </button>
              <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-800">
                <div class="w-4 h-4 flex items-center justify-center"><i class="ri-delete-bin-line"></i></div>
              </button>
            </div>
          </td>
        `;
        tbody.appendChild(row);
      });
    });
}

function openUserModal(id = null) {
  const modal = document.getElementById('user-modal');
  const form = document.getElementById('userForm');
  const title = document.getElementById('user-modal-title');
  if (id) {
    // For edit, fetch user data
    fetch('get_user.php?id=' + id)
      .then(res => res.json())
      .then(user => {
        document.getElementById('user-id').value = user.id;
        document.getElementById('name').value = user.name;
        document.getElementById('email').value = user.email;
        title.textContent = 'Edit User';
        modal.classList.add('show');
      });
  } else {
    form.reset();
    document.getElementById('user-id').value = '';
    title.textContent = 'Add User';
    modal.classList.add('show');
  }
}

function closeUserModal() {
  document.getElementById('user-modal').classList.remove('show');
}

// Handle add/edit form submit
document.getElementById('userForm').addEventListener('submit', function(e){
  e.preventDefault();
  const id = document.getElementById('user-id').value;
  const name = document.getElementById('name').value;
  const email = document.getElementById('email').value;
  const url = id ? 'edit_user.php' : 'add_user.php';
  
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, name, email })
  })
  .then(res => res.json())
  .then(data => {
    alert(id ? 'User Updated!' : 'User Added!');
    closeUserModal();
    loadUsers();
  });
});

function editUser(id) {
  openUserModal(id);
}

function deleteUser(id) {
  if (confirm('Are you sure you want to delete this user?')) {
    fetch('delete_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
      alert('User Deleted!');
      loadUsers();
    });
  }
}

// Show "Users" section in navigation
document.addEventListener('DOMContentLoaded', function(){
  loadUsers();
  // Add 'Users' to navigation if not already
  if (!document.querySelector('[data-section="users"]')) {
    const nav = document.querySelector('nav ul');
    const li = document.createElement('li');
    li.innerHTML = `
      <a href="#" class="nav-item flex items-center space-x-3 p-3 rounded-lg" data-section="users">
        <div class="w-5 h-5 flex items-center justify-center"><i class="ri-user-settings-line"></i></div>
        <span>Users</span>
      </a>
    `;
    nav.appendChild(li);
  }
});