<?php
/**
 * AutoVault - Public video gallery using only student-supplied local files.
 */

$mediaItems = [
    [
        'filename' => '12643338_2160_3840_50fps.mp4',
        'title' => 'Urban driving scene',
        'description' => 'A vertical-format view of vehicles travelling on a city road.',
        'label' => 'Play the urban driving scene',
    ],
    [
        'filename' => '13110546_3840_2160_24fps.mp4',
        'title' => 'Performance car showcase',
        'description' => 'A close view of a blue performance car at an outdoor automotive event.',
        'label' => 'Play the performance car showcase',
    ],
    [
        'filename' => '13318897_3840_2160_30fps.mp4',
        'title' => 'SUV adventure scene',
        'description' => 'A white SUV travelling through a rocky outdoor landscape.',
        'label' => 'Play the SUV adventure scene',
    ],
];

$pageTitle = 'Vehicle Media';
$metaDescription = 'Watch three student-supplied AutoVault vehicle videos.';
$metaKeywords = 'vehicle videos, automotive media, AutoVault gallery';
require __DIR__ . '/includes/header.php';
?>

    <section class="media-page">
        <header class="page-intro">
            <h1>Vehicle media</h1>
            <p>
                Explore three student-supplied automotive videos. Playback begins only
                when you use the controls.
            </p>
            <p><a href="help-media.php">Need help playing these videos?</a></p>
        </header>

        <div class="media-grid">
            <?php foreach ($mediaItems as $index => $item): ?>
                <?php
                    $relativePath = 'assets/media/' . $item['filename'];
                    $absolutePath = __DIR__ . '/assets/media/' . $item['filename'];
                    $titleId = 'media-video-' . ($index + 1) . '-title';
                ?>
                <article class="media-card">
                    <h2 id="<?php echo e($titleId); ?>"><?php echo e($item['title']); ?></h2>
                    <p><?php echo e($item['description']); ?></p>

                    <?php if (is_file($absolutePath)): ?>
                        <video controls preload="metadata"
                               aria-labelledby="<?php echo e($titleId); ?>"
                               aria-label="<?php echo e($item['label']); ?>">
                            <source src="<?php echo e($relativePath); ?>" type="video/mp4">
                            Your browser does not support HTML5 video. You can
                            <a href="<?php echo e($relativePath); ?>">open the MP4 file directly</a>.
                        </video>
                    <?php else: ?>
                        <p class="media-missing" role="status">
                            This video is temporarily unavailable.
                        </p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
