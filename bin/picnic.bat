@echo off

if "%PHPRC%" == "" goto :phprcexit
if "%PICNICRC%" == "" goto :picnicrcexit
"%PHPRC%\php" "%PICNICRC%\bin\picnic" %*
goto :eof

:phprcexit
echo Environment variable PHPRC must be set
goto :eof

:picnicrcexit
echo Environment variable PICNICRC must be set
goto :eof