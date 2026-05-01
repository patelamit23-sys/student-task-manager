// ======== Dashboard Task Functionality ========
document.addEventListener('DOMContentLoaded', () => {
  const taskSection = document.querySelector('.task-section');
  const addTaskBtn = document.querySelector('.add-task button');

  // Load tasks from localStorage
  let tasks = JSON.parse(localStorage.getItem('tasks')) || [];

  function renderTasks() {
    taskSection.innerHTML = '';
    tasks.forEach((task, index) => {
      const card = document.createElement('div');
      card.className = 'task-card';
      card.innerHTML = `
        <span class="priority ${task.priority.toLowerCase()}">${task.priority}</span>
        <h3>${task.title}</h3>
        <p>Due: ${task.due}</p>
        <p>Status: ${task.status}</p>
        <div class="progress-bar"><div class="progress" style="width: ${task.progress}%;"></div></div>
        <button class="edit-task" data-index="${index}">✏️ Edit</button>
        <button class="delete-task" data-index="${index}">🗑️ Delete</button>
      `;
      taskSection.appendChild(card);
    });
  }

  function saveTasks() {
    localStorage.setItem('tasks', JSON.stringify(tasks));
  }

  // Add Task
  addTaskBtn.addEventListener('click', () => {
    const title = prompt('Enter task title:');
    if (!title) return;
    const due = prompt('Enter due date (e.g., 10th Oct):');
    const priority = prompt('Priority (High, Medium, Low):', 'Medium');
    const status = prompt('Status (Not Started, In Progress, Completed):', 'Not Started');
    const progress = prompt('Progress % (0-100):', '0');

    tasks.push({ title, due, priority, status, progress });
    saveTasks();
    renderTasks();
  });

  // Edit/Delete Task
  taskSection.addEventListener('click', (e) => {
    const index = e.target.dataset.index;
    if (!index) return;

    if (e.target.classList.contains('edit-task')) {
      const task = tasks[index];
      task.title = prompt('Edit title:', task.title) || task.title;
      task.due = prompt('Edit due date:', task.due) || task.due;
      task.priority = prompt('Edit priority (High, Medium, Low):', task.priority) || task.priority;
      task.status = prompt('Edit status:', task.status) || task.status;
      task.progress = prompt('Edit progress %:', task.progress) || task.progress;
      saveTasks();
      renderTasks();
    }

    if (e.target.classList.contains('delete-task')) {
      if (confirm('Are you sure you want to delete this task?')) {
        tasks.splice(index, 1);
        saveTasks();
        renderTasks();
      }
    }
  });

  // Initial render
  renderTasks();

  // ======== Progress Bar Animation ========
  function animateProgress() {
    const progressBars = document.querySelectorAll('.progress');
    progressBars.forEach(bar => {
      const width = bar.style.width;
      bar.style.width = '0%';
      setTimeout(() => {
        bar.style.transition = 'width 1s ease-in-out';
        bar.style.width = width;
      }, 100);
    });
  }
  animateProgress();
});

// ======== Calendar Highlight Today ========
function createCalendar(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;

  const now = new Date();
  const year = now.getFullYear();
  const month = now.getMonth();
  const today = now.getDate();

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  let html = '<table><tr>';
  const weekDays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  weekDays.forEach(day => html += `<th>${day}</th>`);
  html += '</tr><tr>';

  // Empty cells before first day
  for(let i=0; i<firstDay; i++) html += '<td></td>';

  for(let date=1; date<=daysInMonth; date++){
    if((firstDay + date -1) %7 === 0 && date !==1) html += '</tr><tr>';
    if(date === today) {
      html += `<td style="background:#1abc9c; color:white; border-radius:50%;">${date}</td>`;
    } else {
      html += `<td>${date}</td>`;
    }
  }

  html += '</tr></table>';
  container.innerHTML = html;
}
