const results = document.getElementById('results');
const search = document.getElementById('search');
const form = document.getElementById('topicForm');
const message = document.getElementById('message');

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function renderTopics(topics) {
    if (topics.length === 0) {
        results.innerHTML = '<p>Тем не знайдено.</p>';
        return;
    }

    let html = `
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Назва</th>
                    <th>Автор</th>
                    <th>Дата створення</th>
                    <th>Закріплена</th>
                </tr>
            </thead>
            <tbody>
    `;

    topics.forEach(topic => {
        html += `
            <tr>
                <td>${escapeHtml(topic.id)}</td>
                <td>${escapeHtml(topic.title)}</td>
                <td>${escapeHtml(topic.author)}</td>
                <td>${escapeHtml(topic.created_at)}</td>
                <td>${Number(topic.is_pinned) === 1 ? 'Так' : 'Ні'}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    results.innerHTML = html;
}

async function loadTopics(query = '') {
    try {
        const response = await fetch(
            'api_list.php?q=' + encodeURIComponent(query)
        );

        if (!response.ok) {
            throw new Error('Помилка завантаження даних.');
        }

        const topics = await response.json();
        renderTopics(topics);
    } catch (error) {
        results.innerHTML = `<p class="error">${escapeHtml(error.message)}</p>`;
    }
}

search.addEventListener('input', () => {
    loadTopics(search.value);
});

form.addEventListener('submit', async event => {
    event.preventDefault();

    message.textContent = '';
    message.className = '';

    try {
        const formData = new FormData(form);

        const response = await fetch('api_add.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Помилка додавання теми.');
        }

        form.reset();
        message.textContent = 'Тему додано.';
        loadTopics(search.value);
    } catch (error) {
        message.textContent = error.message;
        message.className = 'error';
    }
});

loadTopics();
