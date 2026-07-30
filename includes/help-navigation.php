<?php
/**
 * Shared topic navigation for the public Help/Wiki section.
 */

function render_help_navigation($currentTopic) {
    $topics = [
        'overview' => ['help.php', 'Help overview'],
        'catalogue' => ['help-catalogue.php', 'Catalogue and vehicles'],
        'account' => ['help-account.php', 'Accounts'],
        'favourites' => ['help-favourites.php', 'Favourites'],
        'testdrive' => ['help-testdrive.php', 'Test drives'],
        'media' => ['help-media.php', 'Media'],
        'admin' => ['help-admin.php', 'Administration'],
    ];
    ?>
    <nav class="help-topic-nav" aria-label="Help topics">
        <ul>
            <?php foreach ($topics as $key => $topic): ?>
                <li>
                    <a href="<?php echo e($topic[0]); ?>"
                       <?php echo $key === $currentTopic ? 'aria-current="page"' : ''; ?>>
                        <?php echo e($topic[1]); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php
}
