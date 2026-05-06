@echo off
setlocal enabledelayedexpansion
set "OUT=list.js"
set "count=0"
set "first=1"

echo const mediaList = [> "%OUT%"

for /r "." %%F in (*.jpg) do (
    set "FP=%%F"
    set "FP=!FP:%CD%\=!"
    set "FP=!FP:\=/!"
    if "!first!"=="1" (
        echo   {"t":"img","s":"!FP!"}>> "%OUT%"
        set "first=0"
    ) else (
        echo  ,{"t":"img","s":"!FP!"}>> "%OUT%"
    )
    set /a "count+=1"
)

echo ]; >> "%OUT%"
echo // total: %count% >> "%OUT%"
echo Done: %OUT%  total=%count%
