
<?php
require_once 'includes/auth.php';

$page_title = 'Cari servis';

require 'includes/header.php';
?>

<div class="mb-4">
    <h1 class="h2 fw-bold">Cari servis</h1>

    <p class="muted">
        Cari servis tanpa perlu refresh halaman.
    </p>

    <label class="form-label" for="searchInput">
        Kata kunci
    </label>

    <input
        id="searchInput"
        class="form-control form-control-lg"
        placeholder="Contoh: design, tutoring..."
        autocomplete="off">
</div>

<div id="searchStatus"
     class="small muted mb-3"
     aria-live="polite">
    Memuatkan servis...
</div>

<div id="results" class="row g-4"></div>

<script>
const input = document.getElementById('searchInput');
const results = document.getElementById('results');
const statusBox = document.getElementById('searchStatus');

let timer;

async function searchServices() {
    const q = input.value.trim();

    statusBox.textContent = 'Sedang mencari...';

    try {
        const response = await fetch(
            'search_api.php?q=' + encodeURIComponent(q),
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        if (!response.ok) {
            throw new Error('Carian gagal');
        }

        const items = await response.json();

        results.replaceChildren();

        if (!items.length) {
            statusBox.textContent = 'Tiada servis ditemui.';
            return;
        }

        statusBox.textContent =
            items.length + ' servis ditemui.';

        items.forEach(s => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';

            const card = document.createElement('article');
            card.className =
                'section-card overflow-hidden h-100';

            if (s.image) {
                const img = document.createElement('img');
                img.className = 'service-img';
                img.src = 'uploads/' +
                    encodeURIComponent(s.image);
                img.alt = s.title;
                card.appendChild(img);
            } else {
                const placeholder =
                    document.createElement('div');

                placeholder.className = 'service-placeholder';
                placeholder.textContent = 'CAMPUS SERVICE';

                card.appendChild(placeholder);
            }

            const body = document.createElement('div');
            body.className = 'p-3';

            const title = document.createElement('h2');
            title.className = 'h5 fw-bold';
            title.textContent = s.title;

            const owner = document.createElement('p');
            owner.className = 'small muted';
            owner.textContent = 'Oleh ' + s.owner_name;

            const desc = document.createElement('p');
            desc.textContent = s.description;

            const price = document.createElement('div');
            price.className = 'price';
            price.textContent =
                'RM ' + Number(s.price).toFixed(2);

            body.append(title, owner, desc, price);
            card.appendChild(body);
            col.appendChild(card);
            results.appendChild(col);
        });

    } catch (error) {
        statusBox.textContent =
            'Carian gagal. Sila semak Apache dan MySQL.';
    }
}

input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(searchServices, 250);
});

searchServices();
</script>

<?php require 'includes/footer.php'; ?>