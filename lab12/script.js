
const storageKey = "booknest_practice_12_variant_9_v1";
const addBookForm = document.getElementById("add-book-form");
const bookList = document.getElementById("book-list");

let books = loadBooks();

function loadBooks() {
  try {
    const savedBooks = JSON.parse(localStorage.getItem(storageKey));

    if (!Array.isArray(savedBooks)) {
      return [];
    }

    return savedBooks.filter(book =>
      book &&
      typeof book.title === "string" &&
      Number.isInteger(book.total) && book.total > 0 &&
      Number.isInteger(book.current) &&
      book.current >= 0 && book.current <= book.total
    );
  } catch (error) {
    return [];
  }
}

function saveBooks() {
  localStorage.setItem(storageKey, JSON.stringify(books));
}

function updateProgress(book, pageText, progressFill) {
  const percent = (book.current / book.total) * 100;

  pageText.textContent = `Сторінка ${book.current} із ${book.total}`;
  progressFill.style.width = `${percent}%`;
}

function renderBooks() {
  bookList.replaceChildren();

  if (books.length === 0) {
    bookList.textContent = "Книг поки немає.";
    return;
  }

  books.forEach(book => {
    const card = document.createElement("article");
    card.className = "book";

    card.innerHTML = `
      <h3></h3>
      <p class="pages"></p>
      <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-fill"></div>
      </div>
      <form class="progress-form">
        <label>
          Прочитано сторінок
          <input type="number" min="0" step="1" required>
        </label>
        <button type="submit">Оновити</button>
      </form>
    `;

    const pageText = card.querySelector(".pages");
    const progressFill = card.querySelector(".progress-fill");
    const progress = card.querySelector(".progress");
    const progressForm = card.querySelector(".progress-form");
    const currentInput = progressForm.querySelector("input");

    card.querySelector("h3").textContent = book.title;
    currentInput.max = book.total;
    currentInput.value = book.current;
    updateProgress(book, pageText, progressFill);
    progress.setAttribute("aria-label", `Прогрес книги «${book.title}»`);
    progress.setAttribute("aria-valuenow", Math.round(book.current / book.total * 100));

    progressForm.addEventListener("submit", event => {
      event.preventDefault();

      const pages = Number(currentInput.value);
      if (!Number.isInteger(pages) || pages < 0 || pages > book.total) {
        return;
      }

      book.current = pages;
      saveBooks();
      updateProgress(book, pageText, progressFill);
      progress.setAttribute("aria-valuenow", Math.round(pages / book.total * 100));
    });

    bookList.append(card);
  });
}

addBookForm.addEventListener("submit", event => {
  event.preventDefault();

  const title = document.getElementById("book-title").value.trim();
  const total = Number(document.getElementById("total-pages").value);

  if (!title || !Number.isSafeInteger(total) || total < 1) {
    return;
  }

  books.push({ title, total, current: 0 });
  saveBooks();
  renderBooks();
  addBookForm.reset();
});

renderBooks();
