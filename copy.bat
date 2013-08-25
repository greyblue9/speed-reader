@echo off

rem The current version of this batch file assumes SpeedDial2 is installed in Chrome by the current user.

rem for /f "usebackq" %%m in (`dir /b "c:\Users"`) do (

	if exist "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\Extensions" (
		for /f "usebackq" %%n in (`dir /b "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\Extensions"`) do (
			if exist "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\Extensions\%%n" (
				for /f "usebackq" %%v in (`dir /b "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\Extensions\%%n"`) do (
			
					find /c "Speed Dial 2" "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\Extensions\%%n\%%v\manifest.json" >nul 2>&1 && (
					
						for /f "usebackq" %%x in (`dir /b "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\databases\chrome-extension_%%n_0\"`) do (
						
							if exist "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\databases\chrome-extension_%%n_0\%%x" (
								echo Found Speed Dial 2 extension folder!
								echo Location: "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default
								echo            \Extensions\%%n\%%v"
								echo Copying DB file from:
								echo            \databases\chrome-extension_%%n_0\%%x
								copy "%USERPROFILE%\AppData\Local\Google\Chrome\User Data\Default\databases\chrome-extension_%%n_0\%%x" ".\SpeedDial2.sqlite"
							)
						
						)
					)
					
					
				)
			)
		)
	)
	
rem )

