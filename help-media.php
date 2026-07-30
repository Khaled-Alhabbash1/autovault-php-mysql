<?php
$pageTitle = 'Media Help';
$metaDescription = 'Help playing and maintaining the supplied AutoVault vehicle videos.';
$metaKeywords = 'video help, media playback, browser compatibility';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/help-navigation.php';
?>
    <article class="help-page">
        <h1>Media help</h1>
        <?php render_help_navigation('media'); ?>

        <section>
            <h2>Play a video</h2>
            <p>
                Open the <a href="media.php">Media</a> page and use each video's native
                Play, Pause, volume, timeline, and full-screen controls. Videos never autoplay
                or loop automatically.
            </p>
        </section>
        <section>
            <h2>Browser compatibility and playback problems</h2>
            <p>
                Current Chrome and Edge releases support the supplied MP4 files. If playback
                fails, reload the page, confirm the file finished copying, try another current
                browser, and check that the server permits MP4 delivery. Slow connections may
                need time to load metadata.
            </p>
        </section>
        <section>
            <h2>Replace media safely</h2>
            <p>
                An administrator or maintainer should copy a genuine MP4 into
                <code>assets/media/</code>, update the fixed entry in <code>media.php</code>,
                verify the file in two browsers, and update <code>docs/MEDIA-CREDITS.md</code>.
                Never rename non-video content to use an MP4 extension.
            </p>
        </section>
        <section>
            <h2>Captions</h2>
            <p>
                Caption files were not supplied. This remains an accessibility limitation;
                captions should be created from verified audio content before a public release.
            </p>
        </section>
    </article>
<?php require __DIR__ . '/includes/footer.php'; ?>
