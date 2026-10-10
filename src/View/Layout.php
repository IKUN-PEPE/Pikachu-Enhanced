<?php

declare(strict_types=1);

namespace Pikachu\View;

use Pikachu\Core\App;

/**
 * Modern Clean Layout Renderer.
 */
class Layout
{
    /**
     * Render a view wrapped in standard Pikachu headers and footers.
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $title, string $contentHtml, array $data = []): string
    {
        $app = App::getInstance();
        $appName = $app->getConfig()->getString('app_name', 'Pikachu-Enhanced');

        ob_start();
        ?>
        <div class="main-content">
            <div class="main-content-inner">
                <div class="breadcrumbs" id="breadcrumbs">
                    <ul class="breadcrumb">
                        <li>
                            <i class="ace-icon fa fa-home home-icon"></i>
                            <a href="/">首页</a>
                        </li>
                        <li class="active"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                </div>
                <div class="page-content">
                    <div class="page-header">
                        <h1>
                            <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                            <small><i class="ace-icon fa fa-angle-double-right"></i> <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></small>
                        </h1>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= $contentHtml ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
