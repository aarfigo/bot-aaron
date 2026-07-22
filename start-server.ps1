<#
Start-server.ps1

Usage: Double-click this file or run from PowerShell. It opens a new PowerShell window,
changes to the project folder and starts the dev server (prefer `php artisan serve` if
`artisan` exists). Then it opens the default browser to http://127.0.0.1:8000

This script is safe to run on Windows PowerShell v5.1.
#>

$project = Split-Path -Path $PSScriptRoot -Parent | Split-Path -Leaf
# If executed from inside laravel_full, $PSScriptRoot is the folder; otherwise we assume script is placed inside laravel_full
$root = $PSScriptRoot

Write-Host "Starting app from: $root"

# Build the command we'll run in the new window
if (Test-Path (Join-Path $root 'artisan')) {
    $cmd = "Set-Location -LiteralPath '$root'; php artisan serve --host=127.0.0.1 --port=8000"
} else {
    # fallback to built-in PHP server pointing to public
    $publicPath = Join-Path $root 'public'
    $cmd = "Set-Location -LiteralPath '$root'; php -S 127.0.0.1:8000 -t '$publicPath'"
}

Write-Host "Launching new PowerShell window to run: $cmd"

# Start a new PowerShell window and run the command (leave window open with -NoExit)
Start-Process -FilePath powershell -ArgumentList "-NoExit","-Command","$cmd"

Start-Sleep -Milliseconds 500

# Try to open the browser to the app URL. If the server isn't up yet the browser will retry when you refresh.
$url = 'http://127.0.0.1:8000'
Start-Process $url

Write-Host "Server window launched and browser opened to $url. If you don't see the site, check the PowerShell window for errors." -ForegroundColor Green
