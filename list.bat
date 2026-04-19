@echo off
rem X:\xampp\php\php.exe "%~dp0list.php" > "%~dp0list.js"
rem echo Done! list.js generated at %~dp0
rem pause
@echo off
set "PHP_EXE=X:\xampp\php\php.exe"
set "PHP_SCRIPT=%~dp0list.php"

if "%~1" == "" (
    echo [INFO] No path provided. Scanning current folder...
    :: 在「批次檔所在位置」產生 list.js
    "%PHP_EXE%" "%PHP_SCRIPT%" > "%~dp0list.js"
) else (
    echo [INFO] Scanning: %~1
    :: 重要修改：將輸出導向到「目標資料夾 (%~1)」裡面
    "%PHP_EXE%" "%PHP_SCRIPT%" "%~1" > "%~1\list.js"
)

echo Done!
pause
