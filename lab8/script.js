const results = document.getElementById('results');
const search = document.getElementById('search');
const form = document.getElementById('topicForm');
const message = document.getElementById('message');
let lastRequest = 0;

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function renderTopics(topics) {
    if (!topics.length) {
        results.textContent = 'Тем не знайдено.';
        return;
    }
    results.innerHTML = `<div class="table-wrap"><table><thead><tr>
        <th>ID</th><th>Назва</th><th>Автор</th><th>Дата</th><th>Закріплена</th>
        </tr></thead><tbody>${topics.map(t => `<tr><td>${escapeHtml(t.id)}</td>
        <td>${escapeHtml(t.title)}</td><td>${escapeHtml(t.author)}</td>
        <td>${escapeHtml(t.created_at)}</td>
        <td>${Number(t.is_pinned) === 1 ? 'Так' : 'Ні'}</td></tr>`).join('')}</tbody></table></div>`;
}

async function loadTopics(query = '') {
    const request = ++lastRequest;
    try {
        const response = await fetch('api_list.php?q=' + encodeURIComponent(query));
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Помилка завантаження.');
        if (request === lastRequest) renderTopics(data);
    } catch (error) {
        if (request === lastRequest) {
            results.textContent = error.message;
            results.className = 'error';
        }
    }
}

search.addEventListener('input', () => loadTopics(search.value));
form.addEventListener('submit', async (event) => {
    event.preventDefault();
    message.className = '';
    message.textContent = '';
    try {
        const response = await fetch('api_add.php', {
            method: 'POST',
            body: new FormData(form),
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Помилка додавання.');
        form.reset();
        search.value = '';
        message.textContent = 'Тему додано.';
        await loadTopics();
    } catch (error) {
        message.className = 'error';
        message.textContent = error.message;
    }
});
loadTopics();
