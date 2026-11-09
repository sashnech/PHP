(() => {
    const form = document.getElementById('validationForm');
    const title = document.getElementById('title');
    const author = document.getElementById('author');
    const message = document.getElementById('messageField');
    const errors = document.getElementById('clientError');
    const key = 'forum_variant9_message_draft';
    const draft = localStorage.getItem(key);
    if (draft !== null && !message.value.trim()) message.value = draft;
    message.addEventListener('input', () => localStorage.setItem(key, message.value));
    form.addEventListener('submit', event => {
        let issue = '';
        const allowed = /^[\p{L}\p{N}\s.,!?():;'"«»—–-]+$/u;
        if (!title.value.trim() || title.value.trim().length > 120 || !allowed.test(title.value.trim())) {
            issue = 'Введіть коректну назву без спеціальних символів (до 120).';
        } else if (!author.value.trim() || author.value.trim().length > 80) {
            issue = 'Вкажіть автора (до 80 символів).';
        } else if (Array.from(message.value.trim()).length < 10 || Array.from(message.value.trim()).length > 2000) {
            issue = 'Повідомлення має містити від 10 до 2000 символів.';
        }
        if (issue) { event.preventDefault(); errors.textContent = issue; }
    });
})();
