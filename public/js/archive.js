/* PROJECT PACE — THE ARCHIVE / HISTORIA EDITORIAL
   Las historias oficiales se leen de MySQL cuando estén disponibles; no es necesario editar JS.
   Los textos provisionales se conservan hasta que tengamos
   las fotografías y los textos oficiales de cada drop.
   Las descripciones iniciales se basan en el catálogo, no en
   una historia de la marca confirmada. Los títulos son provisionales.
   image: ruta relativa a frontend/; si está vacía, usamos las fotos del catálogo.
*/
(() => {
    'use strict';

    // ================================================
    // EDITAR AQUÍ: HISTORIA Y FOTOGRAFÍA DE CADA DROP
    // ================================================
    const provisionalStories = {
        8: {
            title: 'Everyday Motion',
            description: 'Cuatro diseños de la línea Everyday Motion: Precision, Typography, Graphic y Essential Crop.',
            image: '',
            imageAlt: ''
        },
        7: {
            title: 'Relaxed & Performance',
            description: 'Un lanzamiento compuesto por la Relaxed Project Tee, la Relaxed Training Tank y la Performance Tee.',
            image: '',
            imageAlt: ''
        },
        6: {
            title: 'Project & Performance',
            description: 'Dos diseños originales: la Relaxed Project Tee en tono claro y la Performance Long Sleeve en negro.',
            image: '',
            imageAlt: ''
        },
        5: {
            title: 'Pace 01',
            description: 'La Pace 01 Tee y la Pace 01 Tank comparten una identidad gráfica de color rojo sobre prendas claras.',
            image: '',
            imageAlt: ''
        },
        4: {
            title: 'Oversized Training',
            description: 'Dos prendas de corte oversized con la gráfica Training Club: una polera y una solera.',
            image: '',
            imageAlt: ''
        },
        3: {
            title: 'Minimal Long Sleeve',
            description: 'Una prenda de manga larga negra con detalles gráficos de carácter minimalista.',
            image: '',
            imageAlt: ''
        },
        2: {
            title: 'Regular Pink Tank',
            description: 'Una solera de tono claro con detalles de marca en rosado.',
            image: '',
            imageAlt: ''
        },
        1: {
            title: 'Training Club',
            description: 'El primer drop del archivo: Regular Training Tee y Regular Training Tank, ambas en negro con la gráfica Training Club.',
            image: '',
            imageAlt: ''
        }
    };

    // ================================================
    // FUNCIONAMIENTO — NO ES NECESARIO MODIFICARLO
    // ================================================
    const list = document.getElementById('archive-stories-list');
    const index = document.getElementById('archive-index');


    const numbers = publicDrops.map(drop => drop.number).sort((a, b) => b - a);
    const archiveStories = Object.fromEntries(publicDrops.map(drop => {
        const fallback = provisionalStories[drop.number] || {};
        return [drop.number, {
            title: drop.title || fallback.title || drop.name,
            description: drop.description || fallback.description || 'Prendas originales de este lanzamiento.',
            image: drop.image || '',
            imageAlt: drop.imageAlt || ''
        }];
    }));
    const formatNumber = number => String(number).padStart(2, '0');
    const pieceLabel = count => `${count} ${count === 1 ? 'PIEZA ORIGINAL' : 'PIEZAS ORIGINALES'}`;

    function imageTag(path, alt) {
        return `<img src="/${escapeHtml(path)}" alt="${escapeHtml(alt)}" loading="lazy" decoding="async">`;
    }

    function renderVisual(story, items) {
        // Prioridad: imagen de campaña elegida por el administrador del contenido.
        if (story.image) {
            return `<figure class="archive-visual archive-campaign">
                ${imageTag(story.image, story.imageAlt || story.title)}
            </figure>`;
        }
        // Mientras tanto, las fotografías reales de las prendas sirven de portada.
        const selection = items.slice(0, 2);
        if (!selection.length) return `<figure class="archive-visual archive-preview archive-preview-single">
            ${imageTag('assets/images/02_project_pace_isotipo_circular.svg', story.title)}
        </figure>`;
        return `<figure class="archive-visual archive-preview ${selection.length === 1 ? 'archive-preview-single' : ''}">
            ${selection.map(p => imageTag(p.image, `Vista frontal de ${p.name}`)).join('')}
        </figure>`;
    }

    function renderStory(number) {
        const items = products.filter(p => p.dropNumber === number);
        const story = archiveStories[number] || {
            title: `Drop ${formatNumber(number)}`,
            description: 'Prendas originales de este lanzamiento.', image: '', imageAlt: ''
        };
        const label = `DROP ${formatNumber(number)}`;
        const id = `drop-${formatNumber(number)}`;
        return `<article class="archive-story" id="${id}" aria-labelledby="${id}-title">
            <div class="archive-story-main">
                ${renderVisual(story, items)}
                <div class="archive-story-copy">
                    <p class="archive-chapter">CHAPTER ${formatNumber(number)} / ${number === numbers[0] ? 'LATEST RELEASE' : 'PREVIOUS RELEASE'}</p>
                    <p class="archive-drop-number">${label}</p>
                    <h3 id="${id}-title">${escapeHtml(story.title)}<span>.</span></h3>
                    <p class="archive-story-description">${escapeHtml(story.description)}</p>
                    <p class="archive-story-count">${pieceLabel(items.length)}</p>
                </div>
            </div>
            <details class="archive-lookbook">
                <summary>EXPLORE THE DROP <span class="archive-summary-icon" aria-hidden="true"></span></summary>
                <div class="archive-lookbook-grid">
                    ${items.map(p => `<figure class="archive-lookbook-piece">
                        <div class="archive-lookbook-image">${imageTag(p.image, `Vista frontal de ${p.name}`)}</div>
                        <figcaption>${escapeHtml(p.name)}</figcaption>
                    </figure>`).join('')}
                </div>
            </details>
        </article>`;
    }

    document.getElementById('archive-count-drops').textContent = formatNumber(numbers.length);
    document.getElementById('archive-count-pieces').textContent = formatNumber(products.length);
    index.innerHTML = numbers.map(number => `<a href="#drop-${formatNumber(number)}">DROP ${formatNumber(number)}</a>`).join('');
    list.innerHTML = numbers.length ? numbers.map(renderStory).join('') : '<p>No hay lanzamientos publicados.</p>';

    // Compatibilidad con enlaces antiguos que utilizaban archive.html?drop=8.
    const requested = Number(new URLSearchParams(window.location.search).get('drop'));
    const target = window.location.hash && window.location.hash !== '#drop-details'
        ? document.getElementById(window.location.hash.slice(1))
        : numbers.includes(requested) ? document.getElementById(`drop-${formatNumber(requested)}`) : null;
    if (target) requestAnimationFrame(() => target.scrollIntoView({ block: 'start' }));
})();
