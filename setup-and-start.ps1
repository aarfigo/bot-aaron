<#
setup-and-start.ps1

Script todo-en-uno para preparar y arrancar la app Laravel en Windows PowerShell v5.1.

Pasos que realiza:
- Verifica PHP, Composer, Node/NPM (avisa si faltan).
- Copia .env.example a .env si falta y genera APP_KEY.
- Crea el fichero SQLite database\database.sqlite si no existe.
- Ejecuta composer install (si Composer está disponible).
- Ejecuta php artisan migrate --force y php artisan db:seed --force.
- Ejecuta npm install y npm run build (si npm está disponible).
- Abre una nueva ventana PowerShell y arranca el servidor con `php artisan serve`.
- Abre el navegador en http://127.0.0.1:8000

Uso: ejecutar desde la carpeta `laravel_full`:
    .\setup-and-start.ps1

Puedes pasar parámetros opcionales:
    -Port <número> (por defecto 8000)
    -Host <host> (por defecto 127.0.0.1)

#>

param(
    [int]$Port = 8000,
    [string]$ServeHost = '127.0.0.1'
)

function Write-Info($m){ Write-Host $m -ForegroundColor Cyan }
function Write-Err($m){ Write-Host $m -ForegroundColor Red }

$root = $PSScriptRoot
Write-Info "Proyecto: $root"

# Check PHP
if (-not (Get-Command php -ErrorAction SilentlyContinue)){
    Write-Err "PHP no está en PATH. Instala PHP y añade php.exe al PATH antes de continuar."; exit 1
}

# Check Composer
# Check Composer
$hasComposer = (Get-Command composer -ErrorAction SilentlyContinue) -ne $null
if (-not $hasComposer){ Write-Info "Composer no detectado en PATH: se saltará 'composer install'" }

# Check NPM
# Check NPM
$hasNpm = (Get-Command npm -ErrorAction SilentlyContinue) -ne $null
if (-not $hasNpm){ Write-Info "npm no detectado: se saltará compilación de assets" }

Push-Location $root

Write-Info "1) Crear .env si hace falta"
if (-not (Test-Path .env)){
    if (Test-Path .env.example){ Copy-Item .env.example .env; Write-Info ".env creado desde .env.example" }
}

Write-Info "2) Generar APP_KEY si falta"
php artisan key:generate

Write-Info "3) Crear SQLite si hace falta"
if (-not (Test-Path database\database.sqlite)){
    New-Item -ItemType File -Path database\database.sqlite -Force | Out-Null
    Write-Info "database/database.sqlite creado"
}

if ($hasComposer){
    Write-Info "4) composer install"
    composer install --no-interaction
}

Write-Info "5) Migraciones y seeders"
php artisan migrate --force
php artisan db:seed --force

if ($hasNpm){
    Write-Info "6) npm install && npm run build"
    npm install --no-audit --no-fund
    npm run build --silent
}

Write-Info "7) Lanzar servidor en nueva ventana PowerShell"
$cmd = "Set-Location -LiteralPath '$root'; php artisan serve --host=$ServeHost --port=$Port"
Start-Process -FilePath powershell -ArgumentList "-NoExit","-Command","$cmd"

Start-Sleep -Milliseconds 500
Start-Process "http://$Host`:$Port"

Write-Info "Script finalizado. Si el navegador no carga, revisa la ventana PowerShell nueva para ver errores." 

Pop-Location
