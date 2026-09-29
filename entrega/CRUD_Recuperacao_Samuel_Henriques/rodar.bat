@echo off
rem Sobe o CRUD de alunos usando o PHP e o MySQL ja instalados na maquina (Windows).
rem Uso: de dois cliques neste arquivo ou rode "rodar.bat" no terminal.
setlocal
cd /d "%~dp0"

if not defined PORTA set "PORTA=8080"

rem Procura o PHP no PATH; se nao achar, tenta o do XAMPP
set "PHP=php"
where php >nul 2>nul
if errorlevel 1 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP=C:\xampp\php\php.exe"
    ) else (
        echo ERRO: PHP nao encontrado. Instale o PHP ou o XAMPP.
        pause
        exit /b 1
    )
)

rem Usa a extensao mysqli; se nao estiver habilitada no php.ini, tenta carrega-la
set "EXT="
"%PHP%" -r "exit(extension_loaded('mysqli') ? 0 : 1);"
if errorlevel 1 (
    "%PHP%" -d extension=mysqli -r "exit(extension_loaded('mysqli') ? 0 : 1);" 2>nul
    if errorlevel 1 (
        echo ERRO: extensao mysqli do PHP nao encontrada. Habilite "extension=mysqli" no php.ini.
        pause
        exit /b 1
    )
    set "EXT=-d extension=mysqli"
)

echo Dados de acesso ao MySQL (Enter aceita o valor entre colchetes)
set "DB_HOST=localhost"
set /p "DB_HOST=Host [localhost]: "
set "DB_USER=root"
set /p "DB_USER=Usuario [root]: "
set "DB_PASS="
set /p "DB_PASS=Senha (Enter se nao tiver): "

"%PHP%" %EXT% preparar_banco.php %*
if errorlevel 1 (
    pause
    exit /b 1
)

echo.
echo Sistema rodando em http://localhost:%PORTA%  (Ctrl+C para parar)
"%PHP%" %EXT% -S localhost:%PORTA%
