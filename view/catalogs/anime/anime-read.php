<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/CatalogController.php';

$type = $_GET['type'];
$id = $_GET['id'];
$idChapter = $_GET['idChapter'];
$chapterNumber = $_GET['numberChapter'];

$result = (new Catalog())->returnChapter($id, $idChapter, $chapterNumber, $type);
$title = $result['title'];
$description = $result['description'];
$number = $result['number'];
$file = $result['File'];
$prevId = $result['prev_id'];
$nextId = $result['next_id'];
$prevChapter = $result['prev_chapter'];
$nextChapter = $result['next_chapter'];
$subtitleTracks = [];
$chapterDir = trim(dirname($file), '.\\/') . '/';
$subtitleDir = '/var/www/uploads/Anime/' . $chapterDir . 'subtitles/';
$subtitleUrl = ANIME_URL . $chapterDir . 'subtitles/';

if (is_dir($subtitleDir)) {
    foreach (glob($subtitleDir . '*.vtt') as $subtitlePath) {
        $filename = basename($subtitlePath);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $label = ucwords(str_replace(['-', '_'], ' ', $name));
        $language = strtolower(preg_split('/[-_]/', $name)[0] ?? 'sub');

        $subtitleTracks[] = [
            'src' => $subtitleUrl . rawurlencode($filename),
            'label' => $label,
            'srclang' => preg_match('/^[a-z]{2,3}$/', $language) ? $language : 'und'
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/main.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/styles/catalog.css?v=<?php echo getAssetVersion(); ?>" />
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/img/logo.webp" />
    <title>Monogatarya - Capítulo <?php echo $number; ?></title>
    <style>
        :root {
            --cue-size: 0.85rem;
        }

        @media (min-width: 640px) {
            :root { --cue-size: 1.1rem; }
        }

        @media (min-width: 1024px) {
            :root { --cue-size: 1.4rem; }
        }

        video::cue {
            background: transparent;
            color: white;
            font-family: 'Inter', Arial, sans-serif;
            font-size: var(--cue-size);
            font-style: normal;
            font-weight: 800;
            line-height: 1.2;
            text-shadow:
                -3px -3px 0 #000,
                -2px -3px 0 #000,
                0 -3px 0 #000,
                2px -3px 0 #000,
                3px -3px 0 #000,
                -3px -2px 0 #000,
                3px -2px 0 #000,
                -3px 0 0 #000,
                3px 0 0 #000,
                -3px 2px 0 #000,
                3px 2px 0 #000,
                -3px 3px 0 #000,
                -2px 3px 0 #000,
                0 3px 0 #000,
                2px 3px 0 #000,
                3px 3px 0 #000,
                0 5px 7px rgba(0, 0, 0, .85);
        }

        /* Fullscreen móvil: 1.5× más pequeño que 1.85rem */
        @media (max-width: 639px) {
            video:fullscreen::cue,
            video:-webkit-full-screen::cue {
                font-size: 1.23rem;
            }
        }

        /* Fullscreen escritorio: 1.5× más grande que 1.85rem */
        @media (min-width: 640px) {
            video:fullscreen::cue,
            video:-webkit-full-screen::cue {
                font-size: 2.78rem;
            }
        }
    </style>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/header.php'; ?>

    <main class="page-main">
        <div class="layout-container">
            <section class="card-panel">
                <section class="card-panel" aria-labelledby="description">
                    <h2>Capítulo <?php echo $number; ?>: <?php echo htmlspecialchars($title); ?></h2>

                    <div class="video-container">
                        <video controls preload="metadata">
                            <source src="<?php echo ANIME_URL . htmlspecialchars($file); ?>" type="video/mp4">
                            <?php foreach ($subtitleTracks as $track) { ?>
                                <track kind="subtitles" src="<?php echo htmlspecialchars($track['src']); ?>"
                                    srclang="<?php echo htmlspecialchars($track['srclang']); ?>"
                                    label="<?php echo htmlspecialchars($track['label']); ?>">
                            <?php } ?>
                            Tu navegador no soporta vídeo HTML5.
                        </video>
                    </div>
                    <details>
                        <summary>Descripción</summary>
                        <article class="content-card">
                            <p class="field-help"><?php echo htmlspecialchars($description); ?></p>
                        </article>
                    </details>
                </section>

                <?php require $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/navegation.php'; ?>

            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/menu.php'; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/footer.php'; ?>

    <script>
        (function () {
            var root  = document.documentElement;
            var video = document.querySelector('.video-container video');

            var SIZE = {
                small:            '0.85rem',
                medium:           '1.1rem',
                large:            '1.4rem',
                fullscreen_desk:  '2.78rem',   /* 1.85 × 1.5 */
                fullscreen_mob:   '1.23rem'    /* 1.85 / 1.5 */
            };

            function isMobile()     { return window.innerWidth < 640; }
            function isFullscreen() { return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement); }

            function getResponsiveSize() {
                if (window.innerWidth >= 1024) return SIZE.large;
                if (window.innerWidth >= 640)  return SIZE.medium;
                return SIZE.small;
            }

            function updateCueSize() {
                var fs   = isFullscreen();
                var size = fs ? (isMobile() ? SIZE.fullscreen_mob : SIZE.fullscreen_desk)
                              : getResponsiveSize();
                root.style.setProperty('--cue-size', size);
            }

            /* Mueve cada cue a la línea más baja posible en móvil */
            function applyCueLine(cue) {
                cue.snapToLines = true;
                cue.line = isMobile() ? -1 : 'auto';
            }

            function applyToTrack(track) {
                if (track.cues) {
                    for (var i = 0; i < track.cues.length; i++) applyCueLine(track.cues[i]);
                }
                track.addEventListener('cuechange', function () {
                    var active = track.activeCues;
                    if (!active) return;
                    for (var i = 0; i < active.length; i++) applyCueLine(active[i]);
                });
            }

            if (video) {
                video.addEventListener('loadeddata', function () {
                    for (var t = 0; t < video.textTracks.length; t++) {
                        var track = video.textTracks[t];
                        if (track.mode === 'disabled') track.mode = 'hidden';
                        applyToTrack(track);
                    }
                });
            }

            document.addEventListener('fullscreenchange',       updateCueSize);
            document.addEventListener('webkitfullscreenchange', updateCueSize);
            document.addEventListener('mozfullscreenchange',    updateCueSize);
            window.addEventListener('resize', updateCueSize);
        })();
    </script>
</body>

</html>
