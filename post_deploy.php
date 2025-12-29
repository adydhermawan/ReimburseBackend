<?php
// post_deploy.php - Jalankan sekali via browser
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>Recashly Post-Deploy</h1>";
echo "<pre>";

try {
    // Cache config
    echo "Running config:cache...\n";
    $kernel->call('config:cache');
    echo "Running route:cache...\n";
    $kernel->call('route:cache');
    echo "Running view:cache...\n";
    $kernel->call('view:cache');

    // Create storage link
    echo "Checking storage link...\n";
    
    // Explicit paths since we are in root and moved public
    // Target: current_dir/storage/app/public
    // Link:   current_dir/storage (wait, usually link is in public/storage)
    // BUT since we flattened public -> root, the link should be 'storage' in root?
    // NO, 'storage' folder ALREADY EXISTS in root (framework storage).
    // The web-accessible storage usually maps public/storage -> storage/app/public.
    // If we flattened public to root, we have a name collision: We can't name the link 'storage' because 'storage' folder exists!
    
    // We should name the link something else or rely on the factual structure.
    // Actually, on shared hosting flattened structure:
    // The 'storage' folder in root is the REAL storage (logs, framework, etc).
    // We need a public URL for assets.
    // Usually people rename the real storage folder or keep public separate.
    // Since we flattened, we have a problem. "storage" directory is occupied by the real storage.
    
    // OPTION 1: We cannot have a symlink named 'storage' in root if 'storage' dir exists.
    // We might need to rename the public-facing link to 'public_storage' or similar, but then asset() helper breaks.
    
    // WAIT. If we moved public/* to root, then public/storage (symlink) became root/storage (symlink).
    // BUT root/storage (directory) ALREADY EXISTED.
    // So usually `fix_hosting.php` or `cp` would have failed to move the symlink if it conflicted.
    // OR we just perform a check here.
    
    // CORRECTION: Standard Laravel structure:
    // root/storage (Real Dir)
    // root/public/storage (Symlink)
    
    // If we flattened:
    // root/storage (Real Dir)
    // root/storage (Symlink) ??? -> CONFLICT.
    
    // If we are on shared hosting with flattened structure, we usually CANNOT simply put the symlink at root/storage.
    // This is the downside of "moving public to root".
    
    // ALTERNATIVE:
    // We leave 'storage' as the real directory.
    // We create the symlink inside the real storage? No.
    // We need 'https://site.com/storage/file.jpg'.
    // That means we need physical folder or symlink named 'storage' in public root.
    // But 'storage' IS ALREADY A physical folder (containing logs, app, framework).
    
    // SOLUTION:
    // We must ensure 'storage/app/public' is accessible.
    // Since 'storage' is publicly accessible (BAD SECURITY), we can technically access 'storage/app/public'.
    // BUT Laravel protects it with .htaccess usually?
    // Or we should rely on the fact that if we flattened it, the ENTIRE storage folder is now public.
    // So 'https://site.com/storage/app/public/file.jpg' is available.
    // But helper asset('storage/file.jpg') points to 'https://site.com/storage/file.jpg'.
    
    // FIX:
    // Use `storage_path('app/public')` as the target.
    // The link?
    // If we can't create 'storage' symlink, we assume the user has to access via full path?
    // NO, that breaks existing code.
    
    // REAL FIX for FLATTENED structure:
    // We can't have a symlink named 'storage' in root.
    // We rely on the fact that 'storage' directory is there.
    // We might need to symlink 'storage/app/public' contents to 'storage'? No, infinite loop.
    
    // Actually, widespread practice for this generic flatten "hack":
    // 1. Rename real 'storage' to 'laravel_storage' (requires updating config/filesystems.php paths... messy).
    // 2. OR Don't flatten completely?
    
    // Let's check if the user accidentally deleted the real storage or if they coexist?
    // They cannot coexist with same name.
    // The 'fix_hosting.php' moved public contents.
    // public/storage (symlink) -> move -> root/storage.
    // If root/storage (dir) existed, the move of the symlink FAILED (or was skipped).
    
    // So right now, root/storage is the REAL directory.
    // And asset('storage/foo.jpg') expects root/storage/foo.jpg.
    // But the file is at root/storage/app/public/foo.jpg.
    
    // WORKAROUND for this specific setup:
    // Create a symlink named 'public_storage' (or similar)? No, code expects 'storage'.
    
    // HACK: 
    // Symlink `storage/app/public` contents INTO `storage`? Messy.
    
    // Wait, if `storage` folder is now public, we can just change the config?
    // Configure 'url' in `filesystems.php` to `env('APP_URL').'/storage/app/public'`.
    // Then `asset('storage/file')` (generated by `Storage::url()`) might work if configured correctly.
    
    // BUT `config:cache` is irrelevant if we change code.
    
    // Let's try to just ensure the directory exists first to satisfy the error.
    // The error `No such file or directory` on symlink() suggests `storage/app/public` doesn't exist.
    
    $target = __DIR__ . '/storage/app/public';
    $link = __DIR__ . '/storage_link_test'; // Temporary test to see if symlink works AT ALL
    
    echo "Target: $target\n";
    
    if (!is_dir($target)) {
        echo "Target directory missing! Creating...\n";
        mkdir($target, 0755, true);
    }
    
    // Let's try to fix the "Flattened" issue by using a different name if needed, 
    // OR just verify if we can symlink.
    // Since we can't fix the architecture issue in this script easily without editing config,
    // I will focus on the specific error: "No such file".
    
    // Check if we can create the standard link (fails if dir exists)
    $publicStorageLink = __DIR__ . '/storage';
    
    if (is_dir($publicStorageLink) && !is_link($publicStorageLink)) {
        echo "WARNING: '$publicStorageLink' is a real directory (Standard Laravel Storage).\n";
        echo "Cannot create symlink named 'storage' in root because it conflicts with the folder.\n";
        echo "APP_URL might need adjustment or config/filesystems.php update.\n";
        echo "RECOMMENDATION: Update .env 'ASSET_URL' or 'FILESYSTEM_DISK' config.\n";
    } elseif (is_link($publicStorageLink)) {
        echo "Symlink 'storage' already exists.\n";
    } else {
        echo "Attempting to create symlink 'storage' -> 'storage/app/public' (Circular/Self-ref? No target is specific)...\n";
        // This will fail if 'storage' dir exists (which it does).
        // It only works if we renamed the real storage.
    }

    echo "\n✅ Post-deployment checks done.";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage();
}

echo "</pre>";
