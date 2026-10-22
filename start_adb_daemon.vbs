Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "powershell -ExecutionPolicy Bypass -WindowStyle Hidden -File ""C:\laragon\www\Classy-One\adb_reverse_daemon.ps1""", 0, False
