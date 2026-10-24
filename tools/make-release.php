<?php

/**
 * Builds release/agarwal-site.zip: the whole site, ready to be unzipped INTO public_html on Hostinger.
 *
 *   php tools/make-release.php            (run from the project folder; run `npm run build` first)
 *
 * Inside the zip (this is exactly what public_html will contain):
 *   index.php, .htaccess, build/, css/, js/, fonts/, images/ ...   the website files (the public folder)
 *   laravel_app/                                                    the application with its vendor folder
 *   laravel_app/.htaccess                                           "deny all": nobody can open the app files by address
 *
 * What is NOT inside: .env (made on the server), .git, node_modules, tests, logs, backups, uploads (they come
 * from the admin Backup), dev-only packages. Every file git knows about is packed as it is on disk (committed or not).
 */

$root = dirname(__DIR__);
chdir($root);

function fail(string $m): never
{
    fwrite(STDERR, "\nSTOP: {$m}\n");
    exit(1);
}

function run(string $cmd, ?string $cwd = null): string
{
    $full = ($cwd ? 'cd ' . escapeshellarg($cwd) . ' && ' : '') . $cmd . ' 2>&1';
    exec($full, $out, $code);
    if ($code !== 0) {
        fail("command failed ({$code}): {$cmd}\n" . implode("\n", array_slice($out, -15)));
    }

    return implode("\n", $out);
}

function copyTree(string $from, string $to, array $skip = []): void
{
    @mkdir($to, 0775, true);
    foreach (new DirectoryIterator($from) as $item) {
        if ($item->isDot() || in_array($item->getFilename(), $skip, true)) {
            continue;
        }
        $target = $to . '/' . $item->getFilename();
        if ($item->isLink()) {
            continue;   // never copy links (public/storage)
        }
        $item->isDir() ? copyTree($item->getPathname(), $target) : copy($item->getPathname(), $target);
    }
}

function rrmdir(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() && ! $f->isLink() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
    }
    @rmdir($dir);
}

echo "1/7 checking the project...\n";
if (! is_file('public/build/manifest.json')) {
    fail('public/build is missing. Run `npm run build` first.');
}
if (is_file('public/hot')) {
    fail('public/hot exists (the Vite dev server is running). Stop `npm run dev`, delete public/hot and run `npm run build`.');
}
if (! class_exists(ZipArchive::class)) {
    fail('the PHP zip extension is not enabled.');
}

$stage = sys_get_temp_dir() . '/ab-release-' . date('His');
$app = $stage . '/laravel_app';
rrmdir($stage);
mkdir($app, 0775, true);

echo "2/7 copying the code (every file git knows, minus what .gitignore hides)...\n";
$files = array_filter(explode("\0", run('git ls-files --cached --others --exclude-standard -z')));
foreach ($files as $rel) {
    if (! is_file($rel) || is_link($rel)) {
        continue;   // deleted, or a link
    }
    @mkdir(dirname($app . '/' . $rel), 0775, true);
    copy($rel, $app . '/' . $rel);
}

echo "3/7 removing what a server does not need...\n";
foreach (['tests', 'docs', '.github', 'tools', 'node_modules'] as $dir) {
    rrmdir($app . '/' . $dir);
}
foreach (['phpunit.xml', 'package.json', 'package-lock.json', 'vite.config.js', 'CLAUDE.md', '.gitattributes', '.gitignore', '.editorconfig', '.env.example', 'README.md', 'composer.phar'] as $file) {
    @unlink($app . '/' . $file);
}
rrmdir($app . '/public');   // the web files come from the real public folder below (it also holds the built assets)

echo "4/7 installing the packages (no dev tools)...\n";
run('composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --prefer-dist', $app);
run('php artisan package:discover --ansi', $app);

echo "5/7 building the website files (public_html)...\n";
$web = $stage . '/web';
copyTree($root . '/public', $web, ['hot', 'storage', 'index.php', '.htaccess']);

file_put_contents($web . '/index.php', <<<'PHP'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// This file lives in public_html. The application sits in the folder "laravel_app" next to it.
$app = __DIR__.'/laravel_app';

if (file_exists($maintenance = $app.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $app.'/vendor/autoload.php';

$laravel = require_once $app.'/bootstrap/app.php';

$laravel->handleRequest(Request::capture());
PHP);

$htaccess = file_get_contents($root . '/public/.htaccess');
$block = <<<'HT'
    RewriteEngine On

    # The application folder and hidden files (.env ...) can never be opened by address.
    RewriteRule ^laravel_app(/|$) - [F,L]
    RewriteRule (^|/)\.(?!well-known/) - [F,L]

HT;
str_contains($htaccess, "    RewriteEngine On\n") || fail('public/.htaccess has changed: update tools/make-release.php');
file_put_contents($web . '/.htaccess', preg_replace('/ {4}RewriteEngine On\R/', $block, $htaccess, 1));

file_put_contents($app . '/.htaccess', "# Nothing inside the application folder may be opened by address.\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n");

echo "6/7 making the folders the server writes to...\n";
foreach (['storage/app/public', 'storage/app/private', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/framework/testing', 'storage/logs', 'bootstrap/cache'] as $d) {
    @mkdir($app . '/' . $d, 0775, true);
}
// a clean copy never carries local data
foreach (['storage/logs', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'bootstrap/cache'] as $d) {
    foreach (glob($app . '/' . $d . '/*') ?: [] as $f) {
        is_file($f) && ! in_array(basename($f), ['.gitignore'], true) ? unlink($f) : null;
    }
}
@unlink($app . '/bootstrap/cache/config.php');
@unlink($app . '/bootstrap/cache/routes-v7.php');
@file_put_contents($app . '/storage/logs/.gitignore', "*\n!.gitignore\n");

echo "7/7 packing the zip...\n";
@mkdir($root . '/release', 0775, true);
$out = $root . '/release/agarwal-site.zip';
@unlink($out);
$z = new ZipArchive;
$z->open($out, ZipArchive::CREATE) === true || fail('cannot create ' . $out);

$count = 0;
foreach ([[$web, ''], [$app, 'laravel_app/']] as [$base, $prefix]) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $f) {
        $name = $prefix . str_replace('\\', '/', substr($f->getPathname(), strlen($base) + 1));
        if ($f->isDir()) {
            $z->addEmptyDir($name);
        } else {
            $z->addFile($f->getPathname(), $name);
            $count++;
        }
    }
}
$z->close();

// a last look inside
$check = new ZipArchive;
$check->open($out);
$names = [];
for ($i = 0; $i < $check->numFiles; $i++) {
    $names[] = $check->getNameIndex($i);
}
$check->close();
foreach (['index.php', '.htaccess', 'build/manifest.json', 'laravel_app/.htaccess', 'laravel_app/vendor/autoload.php', 'laravel_app/artisan', 'laravel_app/.env.production.example', 'laravel_app/storage/app/public/.htaccess'] as $must) {
    in_array($must, $names, true) || fail("the zip is missing {$must}");
}
foreach ($names as $n) {
    if (preg_match('#(^|/)(\.env|\.git|node_modules|tests)(/|$)#', $n)) {
        fail("the zip must not contain {$n}");
    }
}

rrmdir($stage);
printf("\nDone: %s\n%d files, %.1f MB\n", $out, $count, filesize($out) / 1048576);
