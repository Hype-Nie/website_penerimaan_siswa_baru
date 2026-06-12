<?php foreach (($flashMessages ?? []) as $message): ?>
    <div class="alert alert-<?= e($message['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($message['message']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endforeach; ?>

