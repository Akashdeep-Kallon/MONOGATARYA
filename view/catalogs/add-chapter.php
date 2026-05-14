<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
requireRole('promoter');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../assets/styles/main.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Publicar Capítulo</title>
</head>

<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel" aria-labelledby="crear-evento-title">
                <h2 id="crear-evento-title" class="section-title">Añadir un capitulo</h2>

                <form class="form-vertical" action="<?php echo CONTROLLER_URL; ?>/CatalogController.php" method="post"
                    enctype="multipart/form-data">
                    <input type="hidden" name="id_work" value="<?php echo intval($_GET['id'] ?? 0); ?>">

                    <div class="field-group">
                        <label for="tipo-obra">Tipo</label>
                        <input id="title" type="text" name="type"
                            value="<?php echo htmlspecialchars($_GET['type'] ?? ''); ?>" readonly>
                    </div>
                    <div class="field-group">
                        <label for="chapter-number">Número de capítulo</label>
                        <input id="chapter-number" type="number" name="chapter_number" min="1"
                            placeholder="Dejar vacío para usar el siguiente número"
                            value="<?php echo !empty($_GET['number']) ? intval($_GET['number']) : ''; ?>">
                    </div>

                    <div class="field-group">
                        <label for="title">Título del capítulo</label>
                        <input id="title" type="text" name="title" required minlength="1" maxlength="50">
                    </div>

                    <div class="field-group">
                        <label for="video">Añadir capítulo</label>
                        <?php if ($_GET['type'] === 'Anime') { ?>
                            <input id="video" type="file" name="video"
                                accept="video/mp4,video/webm,video/x-matroska,.mov,.mkv" required>
                            <small>Sube el vídeo del episodio (MP4, WEBM, MOV, MKV — máx. 1GB)</small>

                        <?php } else { ?>
                            <input id="video" type="file" name="video" accept=".zip,application/zip" required>
                            <small>Sube un ZIP con las páginas del capítulo en (JPG/PNG/WEBP— máx. 500MB)</small>
                        <?php } ?>
                    </div>

                    <div class="field-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" maxlength="100"></textarea>
                    </div>

                    <div class="inline-actions">
                        <button type="submit" class="btn btn-add" name="add_chapter">Publicar capítulo</button>
                        <button type="reset" class="btn btn-delete" name="cancelar">Cancelar</button>
                    </div>
                </form>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php'; ?>
            </section>
        </div>
    </main>

    <!-- MODAL DE PROGRESO DE SUBIDA -->
    <div id="upload-backdrop" role="dialog" aria-modal="true" aria-labelledby="upload-title" hidden>
        <div id="upload-modal">
            <p id="upload-title" class="upload-heading">⬆ Subiendo archivo…</p>

            <!-- Barra visual tipo terminal -->
            <div class="upload-bar-wrap" aria-hidden="true">
                <div class="upload-bar-filled" id="upload-bar-filled"></div>
                <div class="upload-bar-empty" id="upload-bar-empty"></div>
                <span class="upload-pct" id="upload-pct">0%</span>
            </div>

            <!-- Línea de detalle -->
            <p class="upload-detail" id="upload-detail">
                <span id="ud-pct">0%</span>
                <span class="ud-sep">·</span>
                <span id="ud-bytes">0 B / —</span>
                <span class="ud-sep">·</span>
                <span id="ud-speed">—</span>
                <span class="ud-sep">·</span>
                <span id="ud-eta">Calculando…</span>
            </p>

            <p class="upload-hint">Esto puede tardar unos minutos…</p>
        </div>
    </div>

    <style>
        /* ── Backdrop ── */
        #upload-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(3px);
            animation: upFadeIn .2s ease;
        }

        #upload-backdrop[hidden] {
            display: none;
        }

        /* ── Modal ── */
        #upload-modal {
            width: min(480px, 92vw);
            padding: 24px 28px 20px;
            border-radius: 10px;
            background: var(--surface, #1f1f1f);
            border: 1px solid var(--border, #343434);
            color: var(--text, #f5f5f5);
            font-family: 'Inter', Arial, sans-serif;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .55);
            animation: upSlideIn .22s ease;
        }

        /* ── Título ── */
        .upload-heading {
            margin: 0 0 16px;
            font-size: 15px;
            font-weight: 700;
            color: var(--text, #f5f5f5);
            letter-spacing: .02em;
        }

        /* ── Barra de progreso ── */
        .upload-bar-wrap {
            display: flex;
            align-items: center;
            gap: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 18px;
            line-height: 1;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .upload-bar-filled {
            color: var(--accent, #ff4141);
            white-space: pre;
            transition: width .3s ease;
            word-break: break-all;
        }

        .upload-bar-empty {
            color: var(--muted, #666);
            white-space: pre;
            flex: 1;
            word-break: break-all;
        }

        .upload-pct {
            margin-left: 10px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: 700;
            color: var(--accent, #ff4141);
            min-width: 42px;
            text-align: right;
            flex-shrink: 0;
        }

        /* ── Detalle ── */
        .upload-detail {
            margin: 0 0 10px;
            font-size: 12.5px;
            color: var(--muted, #bdbdbd);
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            flex-wrap: wrap;
            gap: 4px 2px;
            align-items: center;
        }

        .ud-sep {
            color: var(--border, #555);
            padding: 0 4px;
        }

        #ud-pct {
            color: var(--accent, #ff4141);
            font-weight: 700;
        }

        #ud-bytes {
            color: var(--text, #f5f5f5);
        }

        #ud-speed {
            color: #4fc3f7;
        }

        #ud-eta {
            color: #a5d6a7;
        }

        /* ── Hint ── */
        .upload-hint {
            margin: 6px 0 0;
            font-size: 11.5px;
            color: var(--muted, #888);
            font-style: italic;
        }

        /* ── Animaciones ── */
        @keyframes upFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes upSlideIn {
            from {
                transform: translateY(-14px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        (function () {
            /* ── Constantes de barra ── */
            const BAR_TOTAL = 28; // número de bloques totales

            /* ── Utilidades de formato ── */
            function fmtBytes(b) {
                if (b >= 1e9) return (b / 1e9).toFixed(1) + ' GB';
                if (b >= 1e6) return (b / 1e6).toFixed(1) + ' MB';
                if (b >= 1e3) return (b / 1e3).toFixed(1) + ' KB';
                return b + ' B';
            }
            function fmtSpeed(bps) {
                if (bps >= 1e6) return (bps / 1e6).toFixed(2) + ' MB/s';
                if (bps >= 1e3) return (bps / 1e3).toFixed(1) + ' KB/s';
                return bps.toFixed(0) + ' B/s';
            }
            function fmtEta(secs) {
                if (!isFinite(secs) || secs < 0) return 'Calculando…';
                if (secs < 60) return 'Tiempo restante: ' + Math.ceil(secs) + 's';
                const m = Math.floor(secs / 60), s = Math.ceil(secs % 60);
                return 'Tiempo restante: ' + m + 'min ' + (s < 10 ? '0' : '') + s + 's';
            }

            /* ── Actualiza la UI ── */
            function updateUI(loaded, total, bps) {
                const pct = total ? Math.min(100, Math.round((loaded / total) * 100)) : 0;
                const filled = Math.round((pct / 100) * BAR_TOTAL);
                const empty = BAR_TOTAL - filled;

                // Barra de bloques ████░░░░
                document.getElementById('upload-bar-filled').textContent = '█'.repeat(filled);
                document.getElementById('upload-bar-empty').textContent = '░'.repeat(empty);
                document.getElementById('upload-pct').textContent = pct + '%';

                // Detalle
                document.getElementById('ud-pct').textContent = pct + '%';
                const missing = total ? fmtBytes(total - loaded) : null;
                document.getElementById('ud-bytes').textContent =
                    fmtBytes(loaded) + ' / ' + (total ? fmtBytes(total) : '—') +
                    (missing && total ? '  (faltan ' + missing + ')' : '');
                document.getElementById('ud-speed').textContent = bps > 0 ? fmtSpeed(bps) : '—';

                const eta = (bps > 0 && total) ? (total - loaded) / bps : Infinity;
                document.getElementById('ud-eta').textContent = fmtEta(eta);
            }

            /* ── Interceptar el formulario ── */
            const form = document.querySelector('form.form-vertical');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                // Solo activar si hay un archivo seleccionado
                const fileInput = form.querySelector('input[type="file"]');
                if (!fileInput || !fileInput.files.length) return; // dejar que el browser valide

                e.preventDefault();

                /* Mostrar modal */
                const backdrop = document.getElementById('upload-backdrop');
                backdrop.hidden = false;
                updateUI(0, fileInput.files[0].size, 0);

                /* Variables de velocidad */
                let lastLoaded = 0;
                let lastTime = Date.now();
                let smoothSpeed = 0;
                const ALPHA = 0.3; // suavizado exponencial

                /* XHR */
                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', function (ev) {
                    if (!ev.lengthComputable) return;

                    const now = Date.now();
                    const dt = (now - lastTime) / 1000;
                    const dl = ev.loaded - lastLoaded;
                    const rawSpeed = dt > 0 ? dl / dt : 0;

                    smoothSpeed = smoothSpeed === 0
                        ? rawSpeed
                        : ALPHA * rawSpeed + (1 - ALPHA) * smoothSpeed;

                    lastLoaded = ev.loaded;
                    lastTime = now;

                    updateUI(ev.loaded, ev.total, smoothSpeed);
                });

                xhr.addEventListener('load', function () {
                    updateUI(fileInput.files[0].size, fileInput.files[0].size, 0);
                    document.querySelector('.upload-heading').textContent = '✓ Archivo subido. Procesando…';
                    document.querySelector('.upload-hint') && (document.querySelector('.upload-hint').textContent = 'Redirigiendo…');

                    setTimeout(function () {
                        // El controller siempre redirige con header() a una URL con ?msg=
                        // responseURL recoge la URL final tras la redirección del XHR
                        const dest = xhr.responseURL;
                        if (dest && dest !== window.location.href) {
                            window.location.href = dest;
                        } else {
                            // Fallback: volver al detalle de la obra
                            const params = new URLSearchParams(window.location.search);
                            const id = params.get('id');
                            const type = params.get('type');
                            window.location.href = '<?php echo VIEW_URL; ?>/catalogs/work-detail.php?type=' + encodeURIComponent(type) + '&id=' + id;
                        }
                    }, 600);
                });

                xhr.addEventListener('error', function () {
                    backdrop.hidden = true;
                    alert('Error de red al subir el archivo. Inténtalo de nuevo.');
                });

                xhr.addEventListener('abort', function () {
                    backdrop.hidden = true;
                });

                const formData = new FormData(form);
                if (e.submitter && e.submitter.name) {
                    formData.append(e.submitter.name, e.submitter.value || '1');
                }

                xhr.open('POST', form.action);
                xhr.send(formData);
            });
        })();
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>

</body>

</html>